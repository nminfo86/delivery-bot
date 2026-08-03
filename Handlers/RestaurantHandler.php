<?php

class RestaurantHandler
{
    private $pdo;
    private $chat_id;
    private $telegram_id;
    private $company_id;
    private $current_state; // <-- AJOUTER CECI

    public function __construct($pdo, $chat_id, $telegram_id)
    {
        $this->pdo = $pdo;
        $this->chat_id = $chat_id;
        $this->telegram_id = $telegram_id;
        $this->verifyRestaurantOwner();
    }

    /**
     * Secures the handler by ensuring this Telegram ID is actually linked to a company
     */
    private function verifyRestaurantOwner()
    {
        // <-- MODIFIER LA REQUÊTE POUR RÉCUPÉRER current_state
        $stmt = $this->pdo->prepare("SELECT company_id, current_state FROM telegram_users WHERE telegram_id = :tid LIMIT 1");
        $stmt->execute(['tid' => $this->telegram_id]);
        $user = $stmt->fetch();

        if (!$user || empty($user['company_id'])) {
            $this->sendMessage($this->chat_id, "⚠️ Erreur: Votre compte n'est lié à aucun restaurant. Veuillez contacter l'administrateur.");
            exit;
        }
        $this->company_id = $user['company_id'];
        $this->current_state = $user['current_state']; // <-- AJOUTER CECI
    }

    public function handle($update)
    {
        $message = $update['message'] ?? null;
        $callback = $update['callback_query'] ?? null;

        // 1. Handle Text Commands & States
        if ($message && isset($message['text'])) {
            $text = trim($message['text']);
            
            if ($text === '/start' || $text === '/dashboard') {
                $this->clearState(); // Toujours vider l'état au retour au menu
                $this->showDashboard();
                return;
            }

            // <-- AJOUT : Intercepter le numéro de téléphone saisi
            if ($this->current_state === 'AWAITING_DRIVER_ID') {
                $this->processAddDriver($text);
                return;
            }
            return;
        }

        // 2. Handle Interactive Button Clicks
        if ($callback) {
            $data = $callback['data'];
            
            // <-- AJOUT : Vider l'état si l'utilisateur clique sur n'importe quel bouton
            if ($this->current_state === 'AWAITING_DRIVER_ID') {
                $this->clearState();
            }

            if ($data === 'dash') {
                $this->showDashboard();
                
            // <-- AJOUT : Boutons de gestion des livreurs
            } elseif ($data === 'manage_drivers') {
                $this->showDriverManagement();

            } elseif ($data === 'add_driver') {
                $this->askForDriverId();

            } elseif (strpos($data, 'remove_driver_') === 0) {
                $driver_profile_id = (int) str_replace('remove_driver_', '', $data);
                $this->removeDriver($driver_profile_id);
            // <-- FIN DES AJOUTS
            
            } elseif ($data === 'view_pending') {
                $this->listOrders('PENDING_RESTAURANT', "Nouvelles commandes en attente");

            } elseif ($data === 'view_active') {
                // Shows orders currently being cooked or waiting for drivers
                $this->listOrders("IN('ACCEPTED', 'AWAITING_BID', 'BID_SELECTED')", "Commandes en cours");

            } elseif (strpos($data, 'ord_') === 0) {
                // View specific order details: "ord_532"
                $order_id = (int) str_replace('ord_', '', $data);
                $this->showOrderDetails($order_id);

            } elseif (strpos($data, 'my_drivers_') === 0) {
                // Afficher la liste des livreurs du resto pour cette commande
                $order_id = (int) str_replace('my_drivers_', '', $data);
                $this->showMyDrivers($order_id);

            } elseif (preg_match('/^assign_(\d+)_ord_(\d+)$/', $data, $matches)) {
                // Au lieu d'assigner directement, on demande le tarif de livraison
                $this->askDirectDriverFee((int)$matches[2], (int)$matches[1]);

            } elseif (preg_match('/^confirm_assign_(\d+)_fee_(\d+)_ord_(\d+)$/', $data, $matches)) {
                // Assigner directement la commande au livreur avec le tarif choisi
                // $matches[1] = driver_id, $matches[2] = fee, $matches[3] = order_id
                $this->assignDirectDriver((int)$matches[3], (int)$matches[1], (int)$matches[2]);

            } elseif (strpos($data, 'accept_bid_') === 0) {
                // Le restaurant accepte l'offre d'un livreur du pool public
                $bid_id = (int) str_replace('accept_bid_', '', $data);
                $this->acceptDriverBid($bid_id);

            } elseif (strpos($data, 'accept_') === 0) {
                // Accept order and push to driver pool: "accept_532"
                $order_id = (int) str_replace('accept_', '', $data);
                $this->updateOrderStatus($order_id, 'AWAITING_BID', "✅ Commande acceptée ! Elle a été envoyée aux livreurs de la ville.");

            } elseif (strpos($data, 'ready_') === 0) {
                // Mark food as ready for pickup: "ready_532"
                $order_id = (int) str_replace('ready_', '', $data);
                $this->updateOrderStatus($order_id, 'FOOD_READY', "🛎️ Commande marquée comme prête ! Le livreur a été notifié.");

            } elseif (strpos($data, 'cancel_') === 0) {
                // Kitchen rejects the order: "cancel_532"
                $order_id = (int) str_replace('cancel_', '', $data);
                $this->updateOrderStatus($order_id, 'CANCELLED', "❌ Commande annulée. Le client sera remboursé/notifié.");
            }
            return;
        }
    }

    private function showDashboard()
    {
        // Count orders by progression status for this specific restaurant
        $sql = "SELECT progression, COUNT(*) as count FROM ordere 
                WHERE company_id = :cid AND progression IN ('PENDING_RESTAURANT', 'ACCEPTED', 'AWAITING_BID', 'BID_SELECTED') 
                GROUP BY progression";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['cid' => $this->company_id]);

        $pendingCount = 0;
        $activeCount = 0;

        while ($row = $stmt->fetch()) {
            if ($row['progression'] === 'PENDING_RESTAURANT') {
                $pendingCount = $row['count'];
            } else {
                $activeCount += $row['count'];
            }
        }

        $text = "👨‍🍳 <b>Tableau de Bord du Restaurant</b>\n\n";
        $text .= "Nouvelles commandes : <b>$pendingCount</b>\n";
        $text .= "En cours de préparation : <b>$activeCount</b>\n\n";
        $text .= "Que souhaitez-vous vérifier ?";

        $buttons = [
            [['text' => "🔴 Nouvelles Commandes ($pendingCount)", 'callback_data' => 'view_pending']],
            [['text' => "🟠 En Préparation ($activeCount)", 'callback_data' => 'view_active']],
            [['text' => "🛵 Gérer mes livreurs", 'callback_data' => 'manage_drivers']] // <-- NOUVEAU BOUTON
            // [['text' => "🔄 Passer en mode Client", 'callback_data' => 'switch_customer']]
        ];

        $this->sendMessage($this->chat_id, $text, json_encode(['inline_keyboard' => $buttons]));
    }

    private function listOrders($statusCondition, $title)
    {
        // Fix: Check specifically if it starts with "IN(" to avoid triggering on words like "PENDING"
        $condition = (strpos(trim($statusCondition), 'IN(') === 0)
            ? "progression $statusCondition"
            : "progression = '$statusCondition'";

        $sql = "SELECT id, code, totalTtc, DATE_FORMAT(creationDate, '%H:%i') as time 
                FROM ordere 
                WHERE company_id = :cid AND $condition 
                ORDER BY creationDate DESC LIMIT 15";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['cid' => $this->company_id]);
        $orders = $stmt->fetchAll();

        if (count($orders) === 0) {
            $this->sendMessage($this->chat_id, "Aucune commande dans cette catégorie.", json_encode([
                'inline_keyboard' => [[['text' => "🔙 Retour", 'callback_data' => 'dash']]]
            ]));
            return;
        }

        $buttons = [];
        foreach ($orders as $order) {
            $buttons[] = [
                ['text' => "📝 " . $order['code'] . " - " . $order['totalTtc'] . " DA (" . $order['time'] . ")", 'callback_data' => "ord_" . $order['id']]
            ];
        }
        $buttons[] = [['text' => "🔙 Retour", 'callback_data' => 'dash']];

        $this->sendMessage($this->chat_id, "📋 <b>$title</b>\nSélectionnez une commande pour voir les détails :", json_encode(['inline_keyboard' => $buttons]));
    }

    private function showOrderDetails($order_id)
    {
        // Ajout de 'delivery_fee' dans le SELECT
        $stmt = $this->pdo->prepare("SELECT code, progression, totalTtc, delivery_fee, comment, delivery_lat, delivery_lon, delivery_address_note, DATE_FORMAT(creationDate, '%H:%i') as time FROM ordere WHERE id = :oid AND company_id = :cid LIMIT 1");
        $stmt->execute(['oid' => $order_id, 'cid' => $this->company_id]);
        $order = $stmt->fetch();

        if (!$order) {
            $this->sendMessage($this->chat_id, "❌ Commande introuvable.");
            return;
        }

        // 2. Fetch items
        $sql = "SELECT s.quantity, s.subTotal, o.title, av.attributeValue 
                FROM suborder s 
                LEFT JOIN object o ON s.object_id = o.id 
                LEFT JOIN attribute_value av ON s.attributeValue_id = av.id 
                WHERE s.ordere_id = :oid";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['oid' => $order_id]);
        $items = $stmt->fetchAll();

        // Format the receipt text
        $text = "🧾 <b>Commande " . $order['code'] . "</b> (" . $order['time'] . ")\n";
        $text .= "Statut : <i>" . $order['progression'] . "</i>\n";
        $text .= "━━━━━━━━━━━━━━━━━\n";

        foreach ($items as $item) {
            $variant = $item['attributeValue'] ? " (" . $item['attributeValue'] . ")" : "";
            $text .= $item['quantity'] . "x " . $item['title'] . $variant . " - " . $item['subTotal'] . " DA\n";
        }

        $text .= "━━━━━━━━━━━━━━━━━\n";
        $text .= "🍔 Repas : " . $order['totalTtc'] . " DA\n";
        $text .= "🛵 Livraison : " . $order['delivery_fee'] . " DA\n";

        $totalTtcFinal = $order['totalTtc'] + $order['delivery_fee'];
        $text .= "💰 <b>Total : " . $totalTtcFinal . " DA</b>\n\n";

        // Display Delivery Details from the native schema fields
        $text .= "📍 <b>Détails de Livraison :</b>\n";
        if (!empty($order['delivery_lat']) && !empty($order['delivery_lon'])) {
            $mapsLink = "https://www.google.com/maps?q={$order['delivery_lat']},{$order['delivery_lon']}";
            $text .= "🗺️ <a href='{$mapsLink}'>Ouvrir dans Google Maps</a>\n";
        } elseif (!empty($order['delivery_address_note'])) {
            $text .= "🏘️ " . htmlspecialchars($order['delivery_address_note']) . "\n";
        }
        if (!empty($order['comment'])) {
            $text .= "📝 <i>" . $order['comment'] . "</i>";
        }

        // 4. Generate action buttons
        $buttons = [];
        if ($order['progression'] === 'PENDING_RESTAURANT') {
            $buttons[] = [['text' => "🌍 Accepter (Livreurs de la ville)", 'callback_data' => "accept_" . $order_id]];
            $buttons[] = [['text' => "🛵 Assigner à MON livreur", 'callback_data' => "my_drivers_" . $order_id]];
            $buttons[] = [['text' => "❌ Refuser la commande", 'callback_data' => "cancel_" . $order_id]];
        } elseif ($order['progression'] === 'BID_SELECTED') {
            $buttons[] = [['text' => "🛎️ La commande est prête !", 'callback_data' => "ready_" . $order_id]];
        } elseif ($order['progression'] === 'AWAITING_BID') {
            $buttons[] = [['text' => "⏳ En attente d'un livreur...", 'callback_data' => "dash"]];
        }

        $buttons[] = [['text' => "🔙 Retour", 'callback_data' => 'dash']];

        $this->sendMessage($this->chat_id, $text, json_encode(['inline_keyboard' => $buttons]));
    }

   private function updateOrderStatus($order_id, $new_status, $successMessage)
    {
        // 1. Récupérer le Telegram ID du client, du livreur, le nom ET la commune du restaurant
        $sqlInfo = "SELECT o.code, o.customer_telegram_id, tu.telegram_id as driver_telegram_id, c.companyName as rest_name, c.commune_name as rest_commune
                    FROM ordere o 
                    LEFT JOIN driver_profiles dp ON o.driver_profile_id = dp.id
                    LEFT JOIN telegram_users tu ON dp.telegram_user_id = tu.id
                    LEFT JOIN company c ON o.company_id = c.id
                    WHERE o.id = :oid";
        $stmtInfo = $this->pdo->prepare($sqlInfo);
        $stmtInfo->execute(['oid' => $order_id]);
        $orderData = $stmtInfo->fetch();

        // 2. Mise à jour de la commande
        $stmt = $this->pdo->prepare("UPDATE ordere SET progression = :status, updateDate = NOW() WHERE id = :oid AND company_id = :cid");
        $stmt->execute([
            'status' => $new_status,
            'oid' => $order_id,
            'cid' => $this->company_id
        ]);

        if ($new_status === 'AWAITING_BID') {
            $stmt = $this->pdo->prepare("UPDATE ordere SET accepted_at = NOW() WHERE id = :oid");
            $stmt->execute(['oid' => $order_id]);
        }
        if ($new_status === 'FOOD_READY') {
            $stmt = $this->pdo->prepare("UPDATE ordere SET ready_at = NOW() WHERE id = :oid");
            $stmt->execute(['oid' => $order_id]);
        }

        // 3. Envoyer le message de confirmation au restaurant
        $this->sendMessage($this->chat_id, $successMessage);

        // 4. Notifier le CLIENT et les LIVREURS
        if ($orderData) {
            $code = $orderData['code'];
            $restName = htmlspecialchars($orderData['rest_name']);

            // --- Notification au CLIENT ---
            if (!empty($orderData['customer_telegram_id'])) {
                $custId = $orderData['customer_telegram_id'];
                if ($new_status === 'AWAITING_BID') {
                    $this->sendMessage($custId, "✅ <b>Commande Acceptée !</b>\nVotre commande <b>$code</b> est en cours de préparation. Nous recherchons actuellement un livreur disponible dans votre ville 🛵.");
                } elseif ($new_status === 'FOOD_READY') {
                    $this->sendMessage($custId, "🛎️ <b>C'est prêt !</b>\nVotre commande <b>$code</b> est prête au restaurant. Le livreur va la récupérer d'une minute à l'autre !");
                } elseif ($new_status === 'CANCELLED') {
                    $this->sendMessage($custId, "❌ <b>Commande Annulée</b>\nLe restaurant a malheureusement dû annuler votre commande <b>$code</b>. Veuillez nous excuser pour la gêne occasionnée.");
                }
            }

            // --- BROADCAST AUX LIVREURS DE LA VILLE (Système d'enchères) ---
            if ($new_status === 'AWAITING_BID') {
                $restCommune = $orderData['rest_commune'];
                
                // Requête ciblée : En ligne + Même ville + (Public OU Livreur de ce resto)
                $sqlDrivers = "SELECT tu.telegram_id 
                               FROM driver_profiles dp 
                               JOIN telegram_users tu ON dp.telegram_user_id = tu.id 
                               WHERE dp.is_online = 1 
                               AND dp.verification_status = 'approved' 
                               AND dp.commune_name = :commune 
                               AND (tu.company_id IS NULL OR tu.company_id = :cid)";
                $stmtDrivers = $this->pdo->prepare($sqlDrivers);
                $stmtDrivers->execute([
                    'commune' => $restCommune,
                    'cid' => $this->company_id
                ]);
                
                $availableDrivers = $stmtDrivers->fetchAll();
                
                // Préparation du message d'alerte avec un bouton de raccourci
                $broadcastMsg = "📡 <b>Nouvelle Course Disponible !</b>\n";
                $broadcastMsg .= "La commande (<b>$code</b>) est en attente pour le restaurant <b>$restName</b> ($restCommune).\n\n";
                $broadcastMsg .= "Soyez le premier à proposer votre tarif !";
                
                $driverButtons = json_encode(['inline_keyboard' => [
                    [['text' => "📡 Voir les Offres", 'callback_data' => 'view_pool']]
                ]]);
                
                // Envoi de la notification à chaque livreur concerné
                foreach ($availableDrivers as $d) {
                    $this->sendMessage($d['telegram_id'], $broadcastMsg, $driverButtons);
                }
            }

            // --- Notification au LIVREUR ASSIGNÉ (Quand c'est prêt à récupérer) ---
            if ($new_status === 'FOOD_READY' && !empty($orderData['driver_telegram_id'])) {
                $driverId = $orderData['driver_telegram_id'];
                
                $msg = "🛎️ <b>Commande PRÊTE !</b>\n";
                $msg .= "La commande <b>$code</b> est prête au restaurant <b>$restName</b>.\n";
                $msg .= "Vous pouvez aller la récupérer ! 🛵";
                
                $buttons = [
                    [['text' => "🛵 J'ai récupéré la commande", 'callback_data' => "pickup_" . $order_id]]
                ];
                
                $this->sendMessage($driverId, $msg, json_encode(['inline_keyboard' => $buttons]));
            }
        }

        $this->showDashboard();
    }

    private function showMyDrivers($order_id)
    {
        // Chercher tous les livreurs liés à ce restaurant
        $sql = "SELECT dp.id as driver_id, dp.full_name, dp.is_online 
                FROM driver_profiles dp 
                JOIN telegram_users tu ON dp.telegram_user_id = tu.id 
                WHERE tu.company_id = :cid AND dp.verification_status = 'approved'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['cid' => $this->company_id]);
        $drivers = $stmt->fetchAll();

        if (count($drivers) === 0) {
            $this->sendMessage(
                $this->chat_id,
                "⚠️ Vous n'avez aucun livreur exclusif enregistré pour votre restaurant.\nContactez l'admin pour lier un livreur à votre compte.",
                json_encode(['inline_keyboard' => [[['text' => "🔙 Retour à la commande", 'callback_data' => "ord_" . $order_id]]]])
            );
            return;
        }

        $buttons = [];
        foreach ($drivers as $d) {
            $status = $d['is_online'] ? "🟢 (En Ligne)" : "🔴 (Hors Ligne)";
            $buttons[] = [['text' => $status . " " . $d['full_name'], 'callback_data' => "assign_" . $d['driver_id'] . "_ord_" . $order_id]];
        }
        $buttons[] = [['text' => "🔙 Annuler", 'callback_data' => "ord_" . $order_id]];

        $this->sendMessage($this->chat_id, "🛵 <b>Sélectionnez un de vos livreurs :</b>", json_encode(['inline_keyboard' => $buttons]));
    }

    private function askDirectDriverFee($order_id, $driver_id)
    {
        $text = "💰 <b>Frais de livraison</b>\nQuel est le tarif de livraison pour cette commande avec votre livreur exclusif ?";

        $buttons = [
            [
                ['text' => "Gratuit (0 DA)", 'callback_data' => "confirm_assign_{$driver_id}_fee_0_ord_{$order_id}"]
            ],
            [
                ['text' => "100 DA", 'callback_data' => "confirm_assign_{$driver_id}_fee_100_ord_{$order_id}"],
                ['text' => "150 DA", 'callback_data' => "confirm_assign_{$driver_id}_fee_150_ord_{$order_id}"]
            ],
            [
                ['text' => "200 DA", 'callback_data' => "confirm_assign_{$driver_id}_fee_200_ord_{$order_id}"],
                ['text' => "250 DA", 'callback_data' => "confirm_assign_{$driver_id}_fee_250_ord_{$order_id}"]
            ],
            [
                ['text' => "🔙 Annuler", 'callback_data' => "my_drivers_" . $order_id]
            ]
        ];

        $this->sendMessage($this->chat_id, $text, json_encode(['inline_keyboard' => $buttons]));
    }

    private function acceptDriverBid($bid_id) {
        // 1. Récupérer les détails de l'offre et de la commande
        $sql = "SELECT b.order_id, b.driver_id, b.bid_amount, o.progression, o.code, o.customer_telegram_id, tu.telegram_id as driver_telegram_id, dp.full_name as driver_name
                FROM delivery_bids b
                JOIN ordere o ON b.order_id = o.id
                JOIN driver_profiles dp ON b.driver_id = dp.id
                JOIN telegram_users tu ON dp.telegram_user_id = tu.id
                WHERE b.id = :bid AND o.company_id = :cid LIMIT 1";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['bid' => $bid_id, 'cid' => $this->company_id]);
        $bid = $stmt->fetch();

        if (!$bid) {
            $this->sendMessage($this->chat_id, "❌ Offre introuvable.");
            return;
        }

        // Sécurité : Vérifier si une autre offre n'a pas déjà été acceptée
        if ($bid['progression'] !== 'AWAITING_BID') {
            $this->sendMessage($this->chat_id, "⚠️ Trop tard, une offre a déjà été acceptée pour cette commande ou elle n'est plus en attente d'enchères.");
            return;
        }

        $order_id = $bid['order_id'];
        $driver_id = $bid['driver_id'];
        $fee = $bid['bid_amount'];

        // 2. Mettre à jour le statut des enchères (Refuser les autres, accepter celle-ci)
        $this->pdo->prepare("UPDATE delivery_bids SET status = 'rejected' WHERE order_id = :oid")->execute(['oid' => $order_id]);
        $this->pdo->prepare("UPDATE delivery_bids SET status = 'accepted' WHERE id = :bid")->execute(['bid' => $bid_id]);

        // 3. Mettre à jour la commande principale
        $sqlOrder = "UPDATE ordere SET 
                progression = 'BID_SELECTED', 
                dispatch_type = 'public_pool', 
                driver_profile_id = :did, 
                delivery_fee = :fee,
                updateDate = NOW(), 
                accepted_at = NOW() 
                WHERE id = :oid";
        $this->pdo->prepare($sqlOrder)->execute(['did' => $driver_id, 'fee' => $fee, 'oid' => $order_id]);

        // 4. Notifier le Restaurant
        $this->sendMessage($this->chat_id, "✅ Vous avez accepté l'offre de <b>{$bid['driver_name']}</b> ($fee DA).\nPréparez la commande et marquez-la comme 'Prête' !");
        $this->showDashboard();

        // 5. Notifier le Livreur gagnant
        $msgDriver = "🎉 <b>Offre Acceptée !</b>\n";
        $msgDriver .= "Le restaurant a validé votre tarif de $fee DA pour la commande <b>{$bid['code']}</b>.\n";
        $msgDriver .= "Allez dans '📦 Mes Livraisons' pour suivre la course.";
        $this->sendMessage($bid['driver_telegram_id'], $msgDriver);

        // 6. Notifier le Client
        if (!empty($bid['customer_telegram_id'])) {
            $msgCust = "✅ <b>Livreur trouvé !</b>\n";
            $msgCust .= "Le livreur <b>{$bid['driver_name']}</b> a été assigné à votre commande <b>{$bid['code']}</b>.\n";
            $msgCust .= "🛵 Frais de livraison : <b>$fee DA</b>\n";
            $msgCust .= "👨‍🍳 Votre repas est actuellement en cours de préparation !";
            $this->sendMessage($bid['customer_telegram_id'], $msgCust);
        }
    }

    private function assignDirectDriver($order_id, $driver_id, $fee)
    {
        // 1. Mettre à jour la commande avec les frais (delivery_fee)
        $sql = "UPDATE ordere SET 
                progression = 'BID_SELECTED', 
                dispatch_type = 'direct_driver', 
                driver_profile_id = :did, 
                delivery_fee = :fee,
                updateDate = NOW(), 
                accepted_at = NOW() 
                WHERE id = :oid AND company_id = :cid";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'did' => $driver_id,
            'fee' => $fee,
            'oid' => $order_id,
            'cid' => $this->company_id
        ]);

        $this->sendMessage($this->chat_id, "✅ Commande assignée avec succès à votre livreur (Frais : {$fee} DA).\nPréparez la commande et marquez-la comme 'Prête' !");
        $this->showDashboard();

        // 2. Récupérer les infos pour notifier le Livreur ET le Client
        $stmt = $this->pdo->prepare("SELECT tu.telegram_id as driver_tid, o.code, o.customer_telegram_id 
                                     FROM driver_profiles dp 
                                     JOIN telegram_users tu ON dp.telegram_user_id = tu.id 
                                     JOIN ordere o ON o.id = :oid 
                                     WHERE dp.id = :did LIMIT 1");
        $stmt->execute(['oid' => $order_id, 'did' => $driver_id]);
        $data = $stmt->fetch();

        if ($data) {
            $this->sendMessage($data['driver_tid'], "🚨 <b>Nouvelle Livraison Assignée !</b>\nVotre restaurant vous a assigné la commande <b>" . $data['code'] . "</b>.\n\nAllez dans '📦 Mes Livraisons en cours' pour voir les détails.");

            if (!empty($data['customer_telegram_id'])) {
                $msg = "✅ <b>Bonne nouvelle !</b>\nVotre commande <b>" . $data['code'] . "</b> a été acceptée.\n";
                $msg .= "Un livreur du restaurant a été assigné.\n\n";
                $msg .= "🛵 Frais de livraison : <b>{$fee} DA</b>\n";
                $msg .= "👨‍🍳 Votre repas est en cours de préparation !";
                $this->sendMessage($data['customer_telegram_id'], $msg);
            }
        }
    }

    // --- GESTION DES LIVREURS DU RESTAURANT ---

    private function clearState() {
        $stmt = $this->pdo->prepare("UPDATE telegram_users SET current_state = NULL WHERE telegram_id = :tid");
        $stmt->execute(['tid' => $this->telegram_id]);
        $this->current_state = null;
    }

    private function showDriverManagement() {
        $sql = "SELECT dp.id as driver_id, dp.full_name, dp.phone 
                FROM driver_profiles dp 
                JOIN telegram_users tu ON dp.telegram_user_id = tu.id 
                WHERE tu.company_id = :cid";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['cid' => $this->company_id]);
        $drivers = $stmt->fetchAll();

        $text = "🛵 <b>Gestion de vos livreurs exclusifs</b>\n\n";
        
        $buttons = [];
        if (count($drivers) === 0) {
            $text .= "<i>Vous n'avez aucun livreur exclusif pour le moment.</i>";
        } else {
            $text .= "Voici la liste de vos livreurs. Cliquez sur ❌ pour les retirer de votre restaurant (ils retourneront dans la flotte publique).\n";
            foreach ($drivers as $d) {
                $buttons[] = [['text' => "❌ Retirer : " . $d['full_name'], 'callback_data' => "remove_driver_" . $d['driver_id']]];
            }
        }

        $buttons[] = [['text' => "➕ Ajouter un livreur", 'callback_data' => 'add_driver']];
        $buttons[] = [['text' => "🔙 Retour au Dashboard", 'callback_data' => 'dash']];

        $this->sendMessage($this->chat_id, $text, json_encode(['inline_keyboard' => $buttons]));
    }

    private function askForDriverId() { // Tu peux renommer cette fonction askForDriverId() si tu veux
        $stmt = $this->pdo->prepare("UPDATE telegram_users SET current_state = 'AWAITING_DRIVER_ID' WHERE telegram_id = :tid");
        $stmt->execute(['tid' => $this->telegram_id]);
        
        $text = "➕ <b>Ajouter un livreur</b>\n\n";
        $text .= "Demandez à votre livreur de vous fournir son <b>Code Livreur</b> (disponible sur son MENU /mon_code).\n\n";
        $text .= "✏️ <b>Veuillez taper et envoyer le Code Livreur (ex: 8612659606) :</b>";

        $buttons = [[['text' => "🔙 Annuler", 'callback_data' => 'manage_drivers']]];
        $this->sendMessage($this->chat_id, $text, json_encode(['inline_keyboard' => $buttons]));
    }

    private function processAddDriver($driver_code) {
        // Nettoyer l'entrée (au cas où il y a des espaces)
        $driver_telegram_id = trim($driver_code);

        // Chercher le livreur directement par son ID Telegram unique !
        $sql = "SELECT tu.id as tu_id, dp.full_name, tu.telegram_id, dp.verification_status 
                FROM driver_profiles dp 
                JOIN telegram_users tu ON dp.telegram_user_id = tu.id 
                WHERE tu.telegram_id = :dtid AND tu.role = 'driver' LIMIT 1";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['dtid' => $driver_telegram_id]);
        $driver = $stmt->fetch();

        $this->clearState();

        if (!$driver) {
            $this->sendMessage($this->chat_id, "❌ Impossible de trouver un livreur avec le code <b>$driver_telegram_id</b>.\nVérifiez qu'il vous a donné le bon code.", 
                json_encode(['inline_keyboard' => [[['text' => "🔙 Retour", 'callback_data' => 'manage_drivers']]]]));
            return;
        }

        if ($driver['verification_status'] !== 'approved') {
            $this->sendMessage($this->chat_id, "⚠️ Le profil de <b>" . $driver['full_name'] . "</b> n'a pas encore été approuvé par l'administration.",
                json_encode(['inline_keyboard' => [[['text' => "🔙 Retour", 'callback_data' => 'manage_drivers']]]]));
            return;
        }

        // Lier le livreur au restaurant
        $stmt = $this->pdo->prepare("UPDATE telegram_users SET company_id = :cid WHERE id = :tuid");
        $stmt->execute(['cid' => $this->company_id, 'tuid' => $driver['tu_id']]);

        // Notifications
        $this->sendMessage($this->chat_id, "✅ <b>" . $driver['full_name'] . "</b> a été rattaché à votre restaurant avec succès !");
        $this->sendMessage($driver['telegram_id'], "🎉 <b>Félicitations !</b>\nVous avez été recruté comme livreur exclusif pour un restaurant. Vous recevrez désormais directement leurs commandes !");

        $this->showDriverManagement();
    }

    private function removeDriver($driver_profile_id) {
        $stmt = $this->pdo->prepare("SELECT tu.id as tu_id, dp.full_name, tu.telegram_id FROM driver_profiles dp JOIN telegram_users tu ON dp.telegram_user_id = tu.id WHERE dp.id = :did LIMIT 1");
        $stmt->execute(['did' => $driver_profile_id]);
        $driver = $stmt->fetch();

        if ($driver) {
            // Remettre le company_id à NULL
            $stmt = $this->pdo->prepare("UPDATE telegram_users SET company_id = NULL WHERE id = :tuid");
            $stmt->execute(['tuid' => $driver['tu_id']]);

            $this->sendMessage($this->chat_id, "✅ <b>" . $driver['full_name'] . "</b> a été retiré de votre équipe de livreurs.");
            $this->sendMessage($driver['telegram_id'], "ℹ️ Vous avez été retiré des livreurs exclusifs de votre dernier restaurant. Vous recevrez à nouveau les commandes de la flotte publique de votre ville.");
        }

        $this->showDriverManagement();
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
