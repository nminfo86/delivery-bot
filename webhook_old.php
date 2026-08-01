<?php
// 1. Configuration
$botToken = "8935407487:AAFXdMAi_JjmtuqlyceCmK2ogfNxocNqjNY";
$apiUrl = "https://api.telegram.org/bot" . $botToken . "/";

$dbHost = 'localhost';
$dbName = 'c3_bot_db';
$dbUser = 'c3_bot_user';
$dbPass = 'hZ97iEUutjDG@';

// Connect to Database
try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("DB Connection failed: " . $e->getMessage());
    exit;
}

// 2. Read Incoming Request from Telegram
$content = file_get_contents("php://input");
$update = json_decode($content, true);

if (!$update) {
    exit;
}

// 3. Extract Message Data
$message = $update['message'] ?? null;
$chat_id = $message['chat']['id'] ?? null;
$text = $message['text'] ?? '';
$location = $message['location'] ?? null;

// 4. Helper Function to Send Telegram Messages
function sendMessage($chat_id, $text, $reply_markup = null) {
    global $apiUrl;
    $data = [
        'chat_id' => $chat_id,
        'text' => $text,
        'parse_mode' => 'HTML'
    ];
    if ($reply_markup) {
        $data['reply_markup'] = $reply_markup;
    }
    
    $ch = curl_init($apiUrl . "sendMessage");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_exec($ch);
    curl_close($ch);
}

// 5. Bot Logic: The /start command
if ($text === '/start') {
    $welcome_msg = "👋 Bienvenue sur l'application de livraison !\n\nPour trouver les restaurants ouverts près de chez vous, veuillez partager votre position.";
    
    // Create the "Share Location" button
    $keyboard = [
        'keyboard' => [
            [['text' => '📍 Partager ma position', 'request_location' => true]]
        ],
        'resize_keyboard' => true,
        'one_time_keyboard' => true
    ];
    
    sendMessage($chat_id, $welcome_msg, json_encode($keyboard));
    exit;
}

// 6. Bot Logic: Receiving the GPS Location
if ($location) {
    $user_lat = $location['latitude'];
    $user_lon = $location['longitude'];
    
    sendMessage($chat_id, "⏳ Recherche des restaurants à proximité...", json_encode(['remove_keyboard' => true]));

    // Haversine Formula to find restaurants within 10 km
    $sql = "SELECT id, name, 
            ( 6371 * acos( cos( radians(:user_lat) ) * cos( radians( latitude ) ) * 
            cos( radians( longitude ) - radians(:user_lon) ) + sin( radians(:user_lat) ) * 
            sin( radians( latitude ) ) ) ) AS distance 
            FROM restaurants 
            WHERE is_active = 1 
            HAVING distance < 10 
            ORDER BY distance ASC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['user_lat' => $user_lat, 'user_lon' => $user_lon]);
    $restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($restaurants) > 0) {
        $msg = "✅ Voici les restaurants dans votre zone :\nCliquez pour voir le menu.";
        $inline_keyboard = [];
        
        foreach ($restaurants as $rest) {
            $dist = round($rest['distance'], 1); // Round distance to 1 decimal
            $inline_keyboard[] = [
                ['text' => "🏪 " . $rest['name'] . " (~" . $dist . " km)", 'callback_data' => "menu_" . $rest['id']]
            ];
        }
        
        $markup = ['inline_keyboard' => $inline_keyboard];
        sendMessage($chat_id, $msg, json_encode($markup));
    } else {
        sendMessage($chat_id, "Désolé, aucun restaurant n'est disponible dans votre zone pour le moment.");
    }
    exit;
}
?>