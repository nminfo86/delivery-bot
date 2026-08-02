<?php
// 1. Load your existing eatSmartly database & initialization files
require_once __DIR__ . '/mycms/php/functions.php';
require_once __DIR__ . '/mycms/php/Config.php';
require_once __DIR__ . '/mycms/php/init.php';
require_once __DIR__ . '/mycms/php/Connection.php';
require_once __DIR__ . '/mycms/php/Ordere.php';
require_once __DIR__ . '/mycms/php/SubOrder.php';
require_once __DIR__ . '/mycms/php/JsonOrdere.php';
require_once __DIR__ . '/mycms/php/JsonSubOrder.php';
require_once __DIR__ . '/mycms/php/JsonObject.php';
require_once __DIR__ . '/mycms/php/JsonPrice.php';
require_once __DIR__ . '/mycms/php/JsonCategory.php';

// 2. Load the isolated Actor Handlers 
require_once __DIR__ . '/Handlers/CustomerHandler.php';
require_once __DIR__ . '/Handlers/RestaurantHandler.php';
require_once __DIR__ . '/Handlers/DriverHandler.php';

// Ensure PHP session is active for mycms class compatibility
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
    
// 3. Connect to Database reusing your exact eatSmartly credentials
try {
    $dsn = "mysql:host=" . Connection::$host . ";dbname=" . Connection::$db_name . ";charset=utf8mb4";
    $pdo = new PDO($dsn, Connection::$username, Connection::$password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Telegram Bot DB Connection failed: " . $e->getMessage());
    exit;
}

// 4. Read Incoming Request from Telegram
$content = file_get_contents("php://input");
$update = json_decode($content, true);

if (!$update) exit;

// 5. Extract User Identifiers safely
$message = $update['message'] ?? null;
$callback_query = $update['callback_query'] ?? null;

$telegram_id = $message['from']['id'] ?? $callback_query['from']['id'] ?? null;
$chat_id = $message['chat']['id'] ?? $callback_query['message']['chat']['id'] ?? null;

// Intercepter et ignorer les messages vocaux ou audio
if (isset($message['voice']) || isset($message['audio']) || isset($message['video_note'])) {
    sendTelegramMessage($chat_id, "⚠️ Désolé, je ne peux pas écouter les messages vocaux. Veuillez utiliser les boutons du menu ou du texte.");
    exit;
}

if (!$telegram_id || !$chat_id) exit;

// 6. Identity & Mode Detection Logic
$stmt = $pdo->prepare("SELECT id, role, current_mode, status, current_state, phone, latitude, longitude, commune_name FROM telegram_users WHERE telegram_id = :tid LIMIT 1");
$stmt->execute(['tid' => $telegram_id]);
$user = $stmt->fetch();

// 7. Auto-Registration for First-Time Users
if (!$user) {
    $insert = $pdo->prepare("INSERT INTO telegram_users (telegram_id, role, current_mode, status) VALUES (:tid, 'customer', 'customer', 'active')");
    $insert->execute(['tid' => $telegram_id]);
    
    $user = [
        'id' => $pdo->lastInsertId(),
        'role' => 'customer',
        'current_mode' => 'customer',
        'status' => 'active',
        'current_state' => null,
        'phone' => null,
        'latitude' => null,
        'longitude' => null,
        'commune_name' => null
    ];
}

// 8. Security Gate
if ($user['status'] !== 'active') {
    $errorMsg = ($user['status'] === 'banned') ? "🚫 Votre compte a été banni." : "⚠️ Votre compte est suspendu.";
    sendTelegramMessage($chat_id, $errorMsg);
    exit;
}

// 8.5 INTERCEPT MODE SWITCHING
if ($callback_query) {
    $data = $callback_query['data'];
    if (in_array($data, ['switch_customer', 'switch_kitchen', 'switch_driver'])) {
        $new_mode = str_replace('switch_', '', $data);
        
        if ($new_mode === 'kitchen' && $user['role'] !== 'restaurant') {
            sendTelegramMessage($chat_id, "⚠️ Accès refusé."); exit;
        }
        if ($new_mode === 'driver' && $user['role'] !== 'driver') {
            sendTelegramMessage($chat_id, "⚠️ Accès refusé."); exit;
        }

        $stmt = $pdo->prepare("UPDATE telegram_users SET current_mode = :mode, current_state = NULL WHERE telegram_id = :tid");
        $stmt->execute(['mode' => $new_mode, 'tid' => $telegram_id]);
        
        $modeNames = ['customer' => 'Client', 'kitchen' => 'Cuisine', 'driver' => 'Livreur'];
        sendTelegramMessage($chat_id, "🔄 Interface changée vers : Mode <b>" . $modeNames[$new_mode] . "</b>.\nTapez /start pour ouvrir le nouveau menu.");
        exit;
    }
}

// 8.6 INTERCEPT MODE SWITCHING VIA TEXT COMMANDS (Left Menu)
$text = $message['text'] ?? '';

if (in_array($text, ['/client', '/cuisine', '/livreur'])) {
    $new_mode = '';
    if ($text === '/client') $new_mode = 'customer';
    if ($text === '/cuisine') $new_mode = 'kitchen';
    if ($text === '/livreur') $new_mode = 'driver';

    // Security Gate: Prevent normal customers from accessing staff modes
    if ($new_mode === 'kitchen' && $user['role'] !== 'restaurant') {
        sendTelegramMessage($chat_id, "🚫 Accès refusé. Vous n'êtes pas restaurateur."); 
        exit;
    }
    if ($new_mode === 'driver' && $user['role'] !== 'driver') {
        sendTelegramMessage($chat_id, "🚫 Accès refusé. Vous n'êtes pas livreur."); 
        exit;
    }

    // Update the user's mode in the database
    $stmt = $pdo->prepare("UPDATE telegram_users SET current_mode = :mode, current_state = NULL WHERE telegram_id = :tid");
    $stmt->execute(['mode' => $new_mode, 'tid' => $telegram_id]);

    $modeNames = ['customer' => 'Client', 'kitchen' => 'Cuisine', 'driver' => 'Livreur'];
    
    // Clear the old keyboard and confirm the switch
    $removeKeyboard = json_encode(['remove_keyboard' => true]);
    sendTelegramMessage($chat_id, "✅ Interface changée vers : Mode <b>" . $modeNames[$new_mode] . "</b>.\n\nTapez /start pour afficher votre nouveau menu.", $removeKeyboard);
    exit;
}
// 9. The Core Router
$activeMode = $user['current_mode'];

switch ($activeMode) {
    case 'kitchen':
        $handler = new RestaurantHandler($pdo, $chat_id, $telegram_id);
        break;
    case 'driver':
        $handler = new DriverHandler($pdo, $chat_id, $telegram_id);
        break;
    case 'customer':
    default:
        $handler = new CustomerHandler($pdo, $chat_id, $telegram_id, $user['role'], $user);
        break;
}

$handler->handle($update);

function sendTelegramMessage($chat_id, $text, $reply_markup = null) {
    $botToken = "8935407487:AAFXdMAi_JjmtuqlyceCmK2ogfNxocNqjNY"; 
    $apiUrl = "https://api.telegram.org/bot" . $botToken . "/sendMessage";
    
    $data = ['chat_id' => $chat_id, 'text' => $text, 'parse_mode' => 'HTML'];
    if ($reply_markup) $data['reply_markup'] = $reply_markup;
    
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_exec($ch);
    curl_close($ch);
}
?>