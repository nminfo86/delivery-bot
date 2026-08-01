<?php

class DriverHandler {
    private $pdo;
    private $chat_id;
    private $telegram_id;
    private $driver;

    public function __construct($pdo, $chat_id, $telegram_id) {
        $this->pdo = $pdo;
        $this->chat_id = $chat_id;
        $this->telegram_id = $telegram_id;
        $this->verifyDriver();
    }

    /**
     * Secures the handler and loads the driver's profile (including their commune and online status)
     */
    private function verifyDriver() {
        $sql = "SELECT dp.id, dp.verification_status, dp.is_online, dp.commune_name, dp.rating 
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

    public function handle($update) {
        $message = $update['message'] ?? null;
        $callback = $update['callback_query'] ?? null;

        // 1. Handle Text Commands
        if ($message && isset($message['text'])) {
            if ($message['text'] === '/start' || $message['text'] === '/dashboard') {
                $this->showDashboard();
            }
            return;
        }

        // 2. Handle Interactive Button Clicks
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
                // Driver clicks an open order to see details and bid
                $order_id = (int) str_replace('view_ord_', '', $data);
                $this->showBiddingOptions($order_id);
                
            } elseif (preg_match('/^bid_(\d+)_ord_(\d+)$/', $data, $matches)) {
                // Driver submits a specific DZD bid amount: e.g., bid_250_ord_532
                $amount = (int) $matches[1];
                $order_id = (int) $matches[2];
                $this->submitBid($order_id, $amount);
                
            } elseif (strpos($data, 'pickup_') === 0) {
                // Driver picked up food from restaurant
                $order_id = (int) str_replace('pickup_', '', $data);
                $this->updateOrderStatus($order_id, 'OUT_FOR_DELIVERY', "🛵 Vous êtes en route ! Le client a été notifié.");
                
            } elseif (strpos($data, 'deliver_') === 0) {
                // Driver delivered food and collected cash
                $order_id = (int) str_replace('deliver_', '', $data);
                $this->updateOrderStatus($order_id, 'DELIVERED', "✅ Commande livrée avec succès ! Bon travail.");
            }
            return;
        }
    }

    private function showDashboard() {
        $statusText = $this->driver['is_online'] ? "🟢 <b>En Ligne</b> (Prêt à livrer)" : "🔴 <b>Hors Ligne</b>";
        $toggleBtnText = $this->driver['is_online'] ? "🛑 Passer Hors Ligne" : "🟢 Passer En Ligne";

        // Check how many open orders are in the driver's commune awaiting a bid
        $sql = "SELECT COUNT(*) FROM orders o 
                JOIN restaurants r ON o.restaurant_id = r.id 
                WHERE o.status = 'AWAITING_BID' AND r.commune_name = :commune";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['commune' => $this->driver['commune_name']]);
        $openOrders = $stmt->fetchColumn();

        // Check active deliveries for this driver
        $sqlActive = "SELECT COUNT(*) FROM orders WHERE driver_id = :did AND status IN ('BID_SELECTED', 'FOOD_READY', 'OUT_FOR_DELIVERY')";
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
            [['text' => "📦 Mes Livraisons en cours ($activeDeliveries)", 'callback_data' => 'view_active']],
            [['text' => "🔄 Passer en mode Client", 'callback_data' => 'switch_customer']]
        ];

        $this->sendMessage($this->chat_id, $text, json_encode(['inline_keyboard' => $buttons]));
    }

    private function toggleOnlineStatus() {
        $newStatus = $this->driver['is_online'] ? 0 : 1;
        $stmt = $this->pdo->prepare("UPDATE driver_profiles SET is_online = :st WHERE id = :id");
        $stmt->execute(['st' => $newStatus, 'id' => $this->driver['id']]);
        
        $this->driver['is_online'] = $newStatus;
        $this->showDashboard();
    }

    private function showOpenDeliveries() {
        if (!$this->driver['is_online']) {
            $this->sendMessage($this->chat_id, "⚠️ Vous devez être En Ligne pour voir les offres de livraison.");
            return;
        }

        // Fetch orders in driver's commune that they haven't bid on yet
        $sql = "SELECT o.id, o.order_code, o.food_subtotal, r.name AS rest_name, DATE_FORMAT(o.created_at, '%H:%i') as time
                FROM orders o
                JOIN restaurants r ON o.restaurant_id = r.id
                WHERE o.status = 'AWAITING_BID' 
                AND r.commune_name = :commune
                AND NOT EXISTS (
                    SELECT 1 FROM delivery_bids b WHERE b.order_id = o.id AND b.driver_id = :did
                )
                ORDER BY o.created_at DESC LIMIT 10";
                
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['commune' => $this->driver['commune_name'], 'did' => $this->driver['id']]);
        $orders = $stmt->fetchAll();

        if (count($orders) === 0) {
            $this->sendMessage($this->chat_id, "Aucune nouvelle livraison disponible dans votre zone pour le moment.", 
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

    private function showBiddingOptions($order_id) {
        $stmt = $this->pdo->prepare("SELECT o.order_code, o.food_subtotal, r.name, r.address FROM orders o JOIN restaurants r ON o.restaurant_id = r.id WHERE o.id = :oid LIMIT 1");
        $stmt->execute(['oid' => $order_id]);
        $order = $stmt->fetch();

        if (!$order) return;

        $text = "📦 <b>Commande " . $order['order_code'] . "</b>\n";
        $text .= "🏪 Restaurant : " . $order['name'] . "\n";
        $text .= "📍 Départ : " . $order['address'] . "\n";
        $text .= "🍔 Total nourriture : " . $order['food_subtotal'] . " DA\n\n";
        $text .= "Quel est votre tarif de livraison pour cette course ?";

        // Pre-calculated fast bidding buttons (In DZD)
        $buttons = [
            [
                ['text' => "150 DA", 'callback_data' => "bid_150_ord_" . $order_id],
                ['text' => "200 DA", 'callback_data' => "bid_200_ord_" . $order_id]
            ],
            [
                ['text' => "250 DA", 'callback_data' => "bid_250_ord_" . $order_id],
                ['text' => "300 DA", 'callback_data' => "bid_300_ord_" . $order_id]
            ],
            [
                ['text' => "350 DA", 'callback_data' => "bid_350_ord_" . $order_id],
                ['text' => "400 DA", 'callback_data' => "bid_400_ord_" . $order_id]
            ],
            [['text' => "🔙 Annuler", 'callback_data' => 'view_pool']]
        ];

        $this->sendMessage($this->chat_id, $text, json_encode(['inline_keyboard' => $buttons]));
    }

    private function submitBid($order_id, $amount) {
        // Double check the order is still awaiting bids
        $stmt = $this->pdo->prepare("SELECT status FROM orders WHERE id = :oid LIMIT 1");
        $stmt->execute(['oid' => $order_id]);
        $order = $stmt->fetch();

        if (!$order || $order['status'] !== 'AWAITING_BID') {
            $this->sendMessage($this->chat_id, "⚠️ Trop tard ! Cette commande n'accepte plus d'offres.");
            $this->showOpenDeliveries();
            return;
        }

        // Insert the bid into delivery_bids
        $stmt = $this->pdo->prepare("INSERT INTO delivery_bids (order_id, driver_id, bid_amount, status) VALUES (:oid, :did, :amount, 'pending')");
        $stmt->execute([
            'oid' => $order_id,
            'did' => $this->driver['id'],
            'amount' => $amount
        ]);

        $this->sendMessage($this->chat_id, "✅ Offre de <b>$amount DA</b> envoyée ! Vous serez notifié si le client accepte votre tarif.");
        $this->showDashboard();
    }

    private function showActiveDeliveries() {
        $sql = "SELECT o.id, o.order_code, o.status, r.name AS rest_name, o.total_amount 
                FROM orders o 
                JOIN restaurants r ON o.restaurant_id = r.id 
                WHERE o.driver_id = :did AND o.status IN ('BID_SELECTED', 'FOOD_READY', 'OUT_FOR_DELIVERY')
                ORDER BY o.updated_at ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['did' => $this->driver['id']]);
        $activeOrders = $stmt->fetchAll();

        if (count($activeOrders) === 0) {
            $this->sendMessage($this->chat_id, "Vous n'avez aucune livraison en cours.", json_encode(['inline_keyboard' => [[['text' => "🔙 Retour", 'callback_data' => 'dash']]]]));
            return;
        }

        foreach ($activeOrders as $o) {
            $text = "📦 <b>Commande " . $o['order_code'] . "</b>\n";
            $text .= "🏪 " . $o['rest_name'] . "\n";
            $text .= "💰 À encaisser : <b>" . $o['total_amount'] . " DA</b>\n";
            
            $buttons = [];
            if ($o['status'] === 'BID_SELECTED') {
                $text .= "⏳ <i>En attente que le restaurant prépare la commande...</i>";
                // No action button until food is ready
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

    private function updateOrderStatus($order_id, $new_status, $message) {
        $sql = "UPDATE orders SET status = :status, updated_at = NOW() ";
        // If delivered, timestamp it
        if ($new_status === 'DELIVERED') {
            $sql .= ", delivered_at = NOW() ";
        }
        $sql .= "WHERE id = :oid AND driver_id = :did";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'status' => $new_status,
            'oid' => $order_id,
            'did' => $this->driver['id']
        ]);

        $this->sendMessage($this->chat_id, $message);
        $this->showActiveDeliveries();
    }

    private function sendMessage($chat_id, $text, $reply_markup = null) {
        $botToken = "8935407487:AAFXdMAi_JjmtuqlyceCmK2ogfNxocNqjNY"; // Load securely from config
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
?>