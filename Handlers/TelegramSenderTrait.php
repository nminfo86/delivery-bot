<?php

/**
 * @property PDO $pdo
 * @property int|string $chat_id
 * @property int|string $telegram_id
 */
trait TelegramSenderTrait {
    
    // --- 1. FONCTION POUR ENVOYER UN MESSAGE (AVEC NETTOYAGE AUTO) ---
    protected function sendMessage($chat_id, $text, $reply_markup = null) {
        $botToken = "8935407487:AAFXdMAi_JjmtuqlyceCmK2ogfNxocNqjNY"; 
        
        // Envoyer le nouveau message
        $apiUrl = "https://api.telegram.org/bot" . $botToken . "/sendMessage";
        $data = ['chat_id' => $chat_id, 'text' => $text, 'parse_mode' => 'HTML'];
        if ($reply_markup) {
            $data['reply_markup'] = $reply_markup;
        }
        
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $response = curl_exec($ch);
        curl_close($ch);

        // Gestion de l'historique glissant
        if (isset($this->chat_id) && $chat_id == $this->chat_id) {
            $result = json_decode($response, true);
            
            if (isset($result['result']['message_id'])) {
                $new_msg_id = $result['result']['message_id'];
                
                $stmt = $this->pdo->prepare("SELECT bot_messages_history FROM telegram_users WHERE telegram_id = :tid");
                $stmt->execute(['tid' => $this->telegram_id]);
                $history_json = $stmt->fetchColumn();
                
                $history = $history_json ? json_decode($history_json, true) : [];
                if (!is_array($history)) $history = [];
                
                $history[] = $new_msg_id;
                
                // On garde 3 messages par défaut (ou la valeur de Config.php si elle existe)
                $maxKeep = class_exists('Config') && property_exists('Config', 'maxBotMessagesToKeep') ? Config::$maxBotMessagesToKeep : 3;
                
                while (count($history) > $maxKeep) {
                    $old_msg_id = array_shift($history); 
                    
                    $delUrl = "https://api.telegram.org/bot" . $botToken . "/deleteMessage";
                    $delData = ['chat_id' => $chat_id, 'message_id' => $old_msg_id];
                    
                    $chDel = curl_init($delUrl);
                    curl_setopt($chDel, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($chDel, CURLOPT_POSTFIELDS, json_encode($delData));
                    curl_setopt($chDel, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                    curl_exec($chDel);
                    curl_close($chDel);
                }
                
                $stmtUpdate = $this->pdo->prepare("UPDATE telegram_users SET bot_messages_history = :hist WHERE telegram_id = :tid");
                $stmtUpdate->execute(['hist' => json_encode($history), 'tid' => $this->telegram_id]);
            }
        }
    }

    // --- 2. FONCTION POUR SUPPRIMER LE MESSAGE DE L'UTILISATEUR ---
    protected function deleteUserMessage($message_id) {
        $botToken = "8935407487:AAFXdMAi_JjmtuqlyceCmK2ogfNxocNqjNY"; 
        $apiUrl = "https://api.telegram.org/bot" . $botToken . "/deleteMessage";
        
        $data = [
            'chat_id' => $this->chat_id, // Toujours disponible grâce au Trait
            'message_id' => $message_id
        ];
        
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_exec($ch);
        curl_close($ch);
    }
}
?>