<?php

class RestaurantHandler {
    private $pdo;
    private $chat_id;
    private $telegram_id;
    private $company_id;

    public function __construct($pdo, $chat_id, $telegram_id) {
        $this->pdo = $pdo;
        $this->chat_id = $chat_id;
        $this->telegram_id = $telegram_id;
        $this->verifyRestaurantOwner();
    }

    /**
     * Secures the handler by ensuring this Telegram ID is actually linked to a company
     */
    private function verifyRestaurantOwner() {
        $stmt = $this->pdo->prepare("SELECT company_id FROM telegram_users WHERE telegram_id = :tid LIMIT 1");
        $stmt->execute(['tid' => $this->telegram_id]);
        $user = $stmt->fetch();

        if (!$user || empty($user['company_id'])) {
            $this->sendMessage($this->chat_id, "⚠️ Erreur: Votre compte n'est lié à aucun restaurant. Veuillez contacter l'administrateur.");
            exit; // Stop execution if unauthorized
        }
        $this->company_id = $user['company_id'];
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
                
            } elseif ($data === 'view_pending') {
                $this->listOrders('PENDING_RESTAURANT', "Nouvelles commandes en attente");
                
            } elseif ($data === 'view_active') {
                // Shows orders currently being cooked or waiting for drivers
                $this->listOrders("IN('ACCEPTED', 'AWAITING_BID', 'BID_SELECTED')", "Commandes en cours");
                
            } elseif (strpos($data, 'ord_') === 0) {
                // View specific order details: "ord_532"
                $order_id = (int) str_replace('ord_', '', $data);
                $this->showOrderDetails($order_id);
                
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

    private function showDashboard() {
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
            [['text' => "🔄 Passer en mode Client", 'callback_data' => 'switch_customer']] // Handled by webhook usually, but good to have
        ];

        $this->sendMessage($this->chat_id, $text, json_encode(['inline_keyboard' => $buttons]));
    }

    private function listOrders($statusCondition, $title) {
        // If it's an IN() clause, inject directly securely, otherwise use standard equal
        $condition = (strpos($statusCondition, 'IN') !== false) ? "progression $statusCondition" : "progression = '$statusCondition'";
        
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

    private function showOrderDetails($order_id) {
        // 1. Fetch main order using legacy 'ordere' table
        $stmt = $this->pdo->prepare("SELECT code, progression, totalTtc, comment, DATE_FORMAT(creationDate, '%H:%i') as time FROM ordere WHERE id = :oid AND company_id = :cid LIMIT 1");
        $stmt->execute(['oid' => $order_id, 'cid' => $this->company_id]);
        $order = $stmt->fetch();

        if (!$order) {
            $this->sendMessage($this->chat_id, "❌ Commande introuvable.");
            return;
        }

        // 2. Fetch items using legacy 'suborder' joined with 'object' and 'attribute_value'
        $sql = "SELECT s.quantity, s.subTotal, o.title, av.attributeValue 
                FROM suborder s 
                LEFT JOIN object o ON s.object_id = o.id 
                LEFT JOIN attribute_value av ON s.attributeValue_id = av.id 
                WHERE s.ordere_id = :oid";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['oid' => $order_id]);
        $items = $stmt->fetchAll();

        // 3. Format the receipt text
        $text = "🧾 <b>Commande " . $order['code'] . "</b> (" . $order['time'] . ")\n";
        $text .= "Statut : <i>" . $order['progression'] . "</i>\n";
        $text .= "━━━━━━━━━━━━━━━━━\n";
        
        foreach ($items as $item) {
            $variant = $item['attributeValue'] ? " (" . $item['attributeValue'] . ")" : "";
            $text .= $item['quantity'] . "x " . $item['title'] . $variant . " - " . $item['subTotal'] . " DA\n";
        }
        
        $text .= "━━━━━━━━━━━━━━━━━\n";
        $text .= "💰 <b>Total : " . $order['totalTtc'] . " DA</b>\n";
        
        if (!empty($order['comment'])) {
            $text .= "\n📝 <i>Note client: " . $order['comment'] . "</i>";
        }

        // 4. Generate action buttons based on the progression status
        $buttons = [];
        
        if ($order['progression'] === 'PENDING_RESTAURANT') {
            $buttons[] = [['text' => "✅ Accepter & Chercher un livreur", 'callback_data' => "accept_" . $order_id]];
            $buttons[] = [['text' => "❌ Refuser la commande", 'callback_data' => "cancel_" . $order_id]];
            
        } elseif ($order['progression'] === 'BID_SELECTED') {
            // A driver has been confirmed, kitchen needs to mark food as ready when done
            $buttons[] = [['text' => "🛎️ La commande est prête !", 'callback_data' => "ready_" . $order_id]];
            
        } elseif ($order['progression'] === 'AWAITING_BID') {
            $buttons[] = [['text' => "⏳ En attente d'un livreur...", 'callback_data' => "dash"]];
        }

        $buttons[] = [['text' => "🔙 Retour au tableau de bord", 'callback_data' => 'dash']];

        $this->sendMessage($this->chat_id, $text, json_encode(['inline_keyboard' => $buttons]));
    }

    private function updateOrderStatus($order_id, $new_status, $successMessage) {
        $stmt = $this->pdo->prepare("UPDATE ordere SET progression = :status, updateDate = NOW() WHERE id = :oid AND company_id = :cid");
        $stmt->execute([
            'status' => $new_status,
            'oid' => $order_id,
            'cid' => $this->company_id
        ]);

        // If the status is AWAITING_BID, we record the accepted_at timestamp
        if ($new_status === 'AWAITING_BID') {
            $stmt = $this->pdo->prepare("UPDATE ordere SET accepted_at = NOW() WHERE id = :oid");
            $stmt->execute(['oid' => $order_id]);
        }
        
        // If the status is FOOD_READY, we record the ready_at timestamp
        if ($new_status === 'FOOD_READY') {
            $stmt = $this->pdo->prepare("UPDATE ordere SET ready_at = NOW() WHERE id = :oid");
            $stmt->execute(['oid' => $order_id]);
        }

        // Notify the kitchen success
        $this->sendMessage($this->chat_id, $successMessage);
        
        // Redirect back to dashboard to refresh view
        $this->showDashboard();
    }

    private function sendMessage($chat_id, $text, $reply_markup = null) {
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
?>