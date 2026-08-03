<?php

class DriverHandler
{
    private $pdo;
    private $chat_id;
    private $telegram_id;
    private $driver;

    public function __construct($pdo, $chat_id, $telegram_id)
    {
        $this->pdo = $pdo;
        $this->chat_id = $chat_id;
        $this->telegram_id = $telegram_id;
        $this->verifyDriver();
    }

    /**
     * Secures the handler and loads the driver's profile
     */
    private function verifyDriver()
    {
        // AJOUT : dp.full_name, dp.phone dans le SELECT
        $sql = "SELECT dp.id, dp.verification_status, dp.is_online, dp.commune_name, dp.rating, dp.full_name, dp.phone 
                FROM driver_profiles dp
                JOIN telegram_users tu ON dp.telegram_user_id = tu.id
                WHERE tu.telegram_id = :tid LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['tid' => $this->telegram_id]);
        $this->driver = $stmt->fetch();

        if (!$this->driver) {
            $this->sendMessage($this->chat_id, "⚠️ Vous n'êtes pas encore inscrit comme livreur. Veuillez utiliser /register_driver.");
            exit;
        }

        if ($this->driver['verification_status'] === 'pending') {
            $this->sendMessage($this->chat_id, "⏳ Votre profil livreur est en cours de vérification par l'administration.");
            exit;
        } elseif ($this->driver['verification_status'] === 'rejected') {
            $this->sendMessage($this->chat_id, "❌ Votre profil livreur a été refusé.");
            exit;
        }
    }

    public function handle($update)
    {
        $message = $update['message'] ?? null;
        $callback = $update['callback_query'] ?? null;

        if ($message && isset($message['text'])) {
            if ($message['text'] === '/start' || $message['text'] === '/dashboard') {
                $this->showDashboard();
            }
            return;
        }

        if ($callback) {
            $data = $callback['data'];

            if ($data === 'dash') {
                $this->showDashboard();
            } elseif ($data === 'toggle_status') {
                $this->toggleOnlineStatus();
            } elseif ($data === 'view_pool') {
                $this->showOpenDeliveries();
            } elseif ($data === 'view_active') {
                $this->showActiveDeliveries();
            } elseif (strpos($data, 'view_ord_') === 0) {
                $order_id = (int) str_replace('view_ord_', '', $data);
                $this->showBiddingOptions($order_id);
            } elseif (preg_match('/^bid_(\d+)_ord_(\d+)$/', $data, $matches)) {
                $amount = (int) $matches[1];
                $order_id = (int) $matches[2];
                $this->submitBid($order_id, $amount);
            } elseif (strpos($data, 'pickup_') === 0) {
                $order_id = (int) str_replace('pickup_', '', $data);
                $this->updateOrderStatus($order_id, 'OUT_FOR_DELIVERY', "🛵 Vous êtes en route ! Le client a été notifié.");
            } elseif (strpos($data, 'deliver_') === 0) {
                $order_id = (int) str_replace('deliver_', '', $data);
                $this->updateOrderStatus($order_id, 'DELIVERED', "✅ Commande livrée avec succès ! Bon travail.");
            }
            return;
        }
    }

    private function showDashboard()
    {
        $statusText = $this->driver['is_online'] ? "🟢 <b>En Ligne</b> (Prêt à livrer)" : "🔴 <b>Hors Ligne</b>";
        $toggleBtnText = $this->driver['is_online'] ? "🛑 Passer Hors Ligne" : "🟢 Passer En Ligne";

        // Mapped to `ordere` and `company`
        $sql = "SELECT COUNT(*) FROM ordere o 
                JOIN company c ON o.company_id = c.id 
                WHERE o.progression = 'AWAITING_BID' AND c.commune_name = :commune";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['commune' => $this->driver['commune_name']]);
        $openOrders = $stmt->fetchColumn();

        // Mapped to `ordere` and `driver_profile_id`
        $sqlActive = "SELECT COUNT(*) FROM ordere WHERE driver_profile_id = :did AND progression IN ('BID_SELECTED', 'FOOD_READY', 'OUT_FOR_DELIVERY')";
        $stmtActive = $this->pdo->prepare($sqlActive);
        $stmtActive->execute(['did' => $this->driver['id']]);
        $activeDeliveries = $stmtActive->fetchColumn();

        $text = "🛵 <b>Tableau de Bord Livreur</b>\n";
        $text .= "Évaluation : ⭐ " . $this->driver['rating'] . "/5.00\n";
        $text .= "Statut : $statusText\n\n";
        $text .= "Zone active : <b>" . $this->driver['commune_name'] . "</b>\n";

        $buttons = [
            [['text' => $toggleBtnText, 'callback_data' => 'toggle_status']],
            [['text' => "📡 Offres Disponibles ($openOrders)", 'callback_data' => 'view_pool']],
            [['text' => "📦 Mes Livraisons en cours ($activeDeliveries)", 'callback_data' => 'view_active']]
            // [['text' => "🔄 Passer en mode Client", 'callback_data' => 'switch_customer']]
        ];

        $this->sendMessage($this->chat_id, $text, json_encode(['inline_keyboard' => $buttons]));
    }

    private function toggleOnlineStatus()
    {
        $newStatus = $this->driver['is_online'] ? 0 : 1;
        $stmt = $this->pdo->prepare("UPDATE driver_profiles SET is_online = :st WHERE id = :id");
        $stmt->execute(['st' => $newStatus, 'id' => $this->driver['id']]);

        $this->driver['is_online'] = $newStatus;
        $this->showDashboard();
    }

    private function showOpenDeliveries()
    {
        if (!$this->driver['is_online']) {
            $this->sendMessage($this->chat_id, "⚠️ Vous devez être En Ligne pour voir les offres de livraison.");
            return;
        }

        // Mapped to `ordere`, `code`, `totalTtc`, `company`
        $sql = "SELECT o.id, o.code AS order_code, o.totalTtc AS food_subtotal, c.companyName AS rest_name, DATE_FORMAT(o.creationDate, '%H:%i') as time
                FROM ordere o
                JOIN company c ON o.company_id = c.id
                WHERE o.progression = 'AWAITING_BID' 
                AND c.commune_name = :commune
                AND NOT EXISTS (
                    SELECT 1 FROM delivery_bids b WHERE b.order_id = o.id AND b.driver_id = :did
                )
                ORDER BY o.creationDate DESC LIMIT 10";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['commune' => $this->driver['commune_name'], 'did' => $this->driver['id']]);
        $orders = $stmt->fetchAll();

        if (count($orders) === 0) {
            $this->sendMessage(
                $this->chat_id,
                "Aucune nouvelle livraison disponible dans votre zone pour le moment.",
                json_encode(['inline_keyboard' => [[['text' => "🔙 Retour", 'callback_data' => 'dash']]]])
            );
            return;
        }

        $buttons = [];
        foreach ($orders as $o) {
            $buttons[] = [
                ['text' => "📍 " . $o['rest_name'] . " | " . $o['food_subtotal'] . " DA (" . $o['time'] . ")", 'callback_data' => "view_ord_" . $o['id']]
            ];
        }
        $buttons[] = [['text' => "🔙 Retour", 'callback_data' => 'dash']];

        $this->sendMessage($this->chat_id, "📡 <b>Livraisons Ouvertes (" . $this->driver['commune_name'] . ")</b>\nSélectionnez une commande pour proposer un tarif :", json_encode(['inline_keyboard' => $buttons]));
    }

    private function showBiddingOptions($order_id)
    {
        // On retire 'o.comment' de la requête, car on ne veut plus l'afficher à ce stade
        $stmt = $this->pdo->prepare("SELECT o.code AS order_code, o.totalTtc AS food_subtotal, o.delivery_lat, o.delivery_lon, o.delivery_address_note, c.companyName AS name, c.address 
                                     FROM ordere o JOIN company c ON o.company_id = c.id 
                                     WHERE o.id = :oid LIMIT 1");
        $stmt->execute(['oid' => $order_id]);
        $order = $stmt->fetch();

        if (!$order) return;

        $text = "📦 <b>Commande " . $order['order_code'] . "</b>\n";
        $text .= "🏪 Restaurant : " . $order['name'] . "\n";
        $text .= "📍 Départ : " . $order['address'] . "\n";

        // Affichage de la position / adresse uniquement
        $text .= "🚩 <b>Destination :</b> ";
        if (!empty($order['delivery_lat']) && !empty($order['delivery_lon'])) {
            $mapsLink = "https://www.google.com/maps?q={$order['delivery_lat']},{$order['delivery_lon']}";
            $text .= "<a href='{$mapsLink}'>Position GPS Client</a>\n";
        } elseif (!empty($order['delivery_address_note'])) {
            $text .= htmlspecialchars($order['delivery_address_note']) . "\n";
        } else {
            $text .= "Non spécifiée\n";
        }

        $text .= "🍔 Total nourriture : " . $order['food_subtotal'] . " DA\n\n";
        $text .= "Quel est votre tarif de livraison pour cette course ?";

        $buttons = [
            [
                ['text' => "150 DA", 'callback_data' => "bid_150_ord_" . $order_id],
                ['text' => "200 DA", 'callback_data' => "bid_200_ord_" . $order_id]
            ],
            [
                ['text' => "250 DA", 'callback_data' => "bid_250_ord_" . $order_id],
                ['text' => "300 DA", 'callback_data' => "bid_300_ord_" . $order_id]
            ],
            [['text' => "🔙 Annuler", 'callback_data' => 'view_pool']]
        ];

        $this->sendMessage($this->chat_id, $text, json_encode(['inline_keyboard' => $buttons]));
    }

    private function submitBid($order_id, $amount)
    {
        $stmt = $this->pdo->prepare("SELECT progression AS status FROM ordere WHERE id = :oid LIMIT 1");
        $stmt->execute(['oid' => $order_id]);
        $order = $stmt->fetch();

        if (!$order || $order['status'] !== 'AWAITING_BID') {
            $this->sendMessage($this->chat_id, "⚠️ Trop tard ! Cette commande n'accepte plus d'offres.");
            $this->showOpenDeliveries();
            return;
        }

        $stmt = $this->pdo->prepare("INSERT INTO delivery_bids (order_id, driver_id, bid_amount, status) VALUES (:oid, :did, :amount, 'pending')");
        $stmt->execute([
            'oid' => $order_id,
            'did' => $this->driver['id'],
            'amount' => $amount
        ]);
        $bid_id = $this->pdo->lastInsertId();

        $this->sendMessage($this->chat_id, "✅ Offre de <b>$amount DA</b> envoyée ! Vous serez notifié si le restaurant accepte votre tarif.");
        $this->showDashboard();

        // --- NOUVEAU : Notifier le restaurant de cette offre ---
        $stmtRest = $this->pdo->prepare("SELECT tu.telegram_id, o.code 
                                         FROM ordere o 
                                         JOIN telegram_users tu ON o.company_id = tu.company_id 
                                         WHERE o.id = :oid AND tu.role = 'restaurant' LIMIT 1");
        $stmtRest->execute(['oid' => $order_id]);
        $rest = $stmtRest->fetch();

        if ($rest) {
            $msg = "🛵 <b>Nouvelle Offre de Livraison !</b>\n";
            $msg .= "Commande : <b>" . $rest['code'] . "</b>\n";
            $msg .= "Livreur : <b>" . $this->driver['full_name'] . "</b> (⭐ " . $this->driver['rating'] . ")\n";
            $msg .= "📱 Tél : <b>" . $this->driver['phone'] . "</b>\n"; // <-- AJOUT DU NUMÉRO ICI
            $msg .= "Tarif proposé : <b>$amount DA</b>\n";

            $buttons = [
                [['text' => "✅ Accepter cette offre ($amount DA)", 'callback_data' => "accept_bid_" . $bid_id]]
            ];
            $this->sendMessage($rest['telegram_id'], $msg, json_encode(['inline_keyboard' => $buttons]));
        }
    }

    private function showActiveDeliveries()
    {
        // Ajout de o.delivery_fee dans le SELECT
        $sql = "SELECT o.id, o.code AS order_code, o.progression AS status, c.companyName AS rest_name, o.totalTtc AS total_amount, o.delivery_fee,
                       o.delivery_lat, o.delivery_lon, o.delivery_address_note, o.comment
                FROM ordere o 
                JOIN company c ON o.company_id = c.id 
                WHERE o.driver_profile_id = :did AND o.progression IN ('BID_SELECTED', 'FOOD_READY', 'OUT_FOR_DELIVERY')
                ORDER BY o.updateDate ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['did' => $this->driver['id']]);
        $activeOrders = $stmt->fetchAll();

        if (count($activeOrders) === 0) {
            $this->sendMessage($this->chat_id, "Vous n'avez aucune livraison en cours.", json_encode(['inline_keyboard' => [[['text' => "🔙 Retour", 'callback_data' => 'dash']]]]));
            return;
        }

        foreach ($activeOrders as $o) {
            // Calcul du total global
            $total_to_pay = $o['total_amount'] + $o['delivery_fee'];

            $text = "📦 <b>Commande " . $o['order_code'] . "</b>\n";
            $text .= "🏪 Restaurant : " . $o['rest_name'] . "\n";
            $text .= "🍔 Repas : " . $o['total_amount'] . " DA\n";
            $text .= "🛵 Livraison : " . $o['delivery_fee'] . " DA\n";
            $text .= "💰 <b>À encaisser : " . $total_to_pay . " DA</b>\n";
            $text .= "━━━━━━━━━━━━━━━━━\n";

            if (!empty($o['comment'])) {
                $text .= "📱 <b>Contact :</b> " . htmlspecialchars($o['comment']) . "\n";
            }

            $text .= "📍 <b>Destination :</b> ";
            if (!empty($o['delivery_lat']) && !empty($o['delivery_lon'])) {
                $mapsLink = "https://www.google.com/maps?q={$o['delivery_lat']},{$o['delivery_lon']}";
                $text .= "<a href='{$mapsLink}'>Position GPS du Client</a>\n";
            } elseif (!empty($o['delivery_address_note'])) {
                $text .= htmlspecialchars($o['delivery_address_note']) . "\n";
            } else {
                $text .= "Non spécifiée\n";
            }
            $text .= "━━━━━━━━━━━━━━━━━\n";

            $buttons = [];
            if ($o['status'] === 'BID_SELECTED') {
                $text .= "⏳ <i>En attente que le restaurant prépare la commande...</i>";
            } elseif ($o['status'] === 'FOOD_READY') {
                $text .= "🛎️ <b>La commande est prête au restaurant !</b>";
                $buttons[] = [['text' => "🛵 J'ai récupéré la commande", 'callback_data' => "pickup_" . $o['id']]];
            } elseif ($o['status'] === 'OUT_FOR_DELIVERY') {
                $text .= "🛵 <i>En route vers le client...</i>";
                $buttons[] = [['text' => "✅ Commande Livrée", 'callback_data' => "deliver_" . $o['id']]];
            }

            $keyboard = !empty($buttons) ? json_encode(['inline_keyboard' => $buttons]) : null;
            $this->sendMessage($this->chat_id, $text, $keyboard);
        }

        $this->sendMessage($this->chat_id, "Gérez vos livraisons ci-dessus ☝️", json_encode(['inline_keyboard' => [[['text' => "🔙 Retour", 'callback_data' => 'dash']]]]));
    }

    private function updateOrderStatus($order_id, $new_status, $message)
    {
        // 1. Récupérer le Telegram ID du client
        $stmtCust = $this->pdo->prepare("SELECT code, customer_telegram_id FROM ordere WHERE id = :oid");
        $stmtCust->execute(['oid' => $order_id]);
        $orderData = $stmtCust->fetch();

        // 2. Mettre à jour la commande
        $sql = "UPDATE ordere SET progression = :status, updateDate = NOW() ";
        if ($new_status === 'DELIVERED') {
            $sql .= ", delivered_at = NOW() ";
        }
        $sql .= "WHERE id = :oid AND driver_profile_id = :did";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'status' => $new_status,
            'oid' => $order_id,
            'did' => $this->driver['id']
        ]);

        // 3. Confirmer au Livreur
        $this->sendMessage($this->chat_id, $message);

        // 4. Notifier le Client de l'avancée de la livraison
        if ($orderData && !empty($orderData['customer_telegram_id'])) {
            $custId = $orderData['customer_telegram_id'];
            $code = $orderData['code'];

            if ($new_status === 'OUT_FOR_DELIVERY') {
                $this->sendMessage($custId, "🛵 <b>En route !</b>\nLe livreur vient de récupérer votre commande <b>$code</b>. Il est en route vers votre position !");
            } elseif ($new_status === 'DELIVERED') {
                $this->sendMessage($custId, "🎉 <b>Commande Livrée !</b>\nVotre commande <b>$code</b> a été livrée avec succès. Bon appétit et merci d'avoir choisi DZ FOOD DELIVERY !");
            }
        }

        $this->showActiveDeliveries();
    }

    private function sendMessage($chat_id, $text, $reply_markup = null)
    {
        $botToken = "8935407487:AAFXdMAi_JjmtuqlyceCmK2ogfNxocNqjNY";
        $apiUrl = "https://api.telegram.org/bot" . $botToken . "/sendMessage";

        $data = [
            'chat_id' => $chat_id,
            'text' => $text,
            'parse_mode' => 'HTML'
        ];
        if ($reply_markup) {
            $data['reply_markup'] = $reply_markup;
        }

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_exec($ch);
        curl_close($ch);
    }
}
