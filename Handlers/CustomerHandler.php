<?php

class CustomerHandler {
    private $pdo;
    private $chat_id;
    private $telegram_id;
    private $user_role; // Added this property

    public function __construct($pdo, $chat_id, $telegram_id, $user_role) { // Added $user_role parameter
        $this->pdo = $pdo;
        $this->chat_id = $chat_id;
        $this->telegram_id = $telegram_id;
        $this->user_role = $user_role;
    }

    public function handle($update) {
        $message = $update['message'] ?? null;
        $callback = $update['callback_query'] ?? null;

        // 1. Handle Text Commands & GPS
        if ($message) {
            $text = $message['text'] ?? '';
            
            if ($text === '/start') {
                $this->sendWelcome();
                return;
            }

            // If they share GPS, use the existing Haversine logic
            if (isset($message['location'])) {
                $this->findNearbyRestaurants($message['location']['latitude'], $message['location']['longitude']);
                return;
            }
        }

        // 2. Handle Inline Button Clicks
        // Telegram limits callback_data to 64 bytes, so we use short prefixes (r=rest, c=cat, i=item, v=variant)
        if ($callback) {
           $data = $callback['data'];
            
            // --- NEW: MANUAL LOCATION ROUTING ---
            if ($data === 'manual_loc') {
                $this->showActiveWilayas();
                
            } elseif (strpos($data, 'w_') === 0) {
                // User clicked a Wilaya (e.g., "w_Sétif")
                $wilaya = str_replace('-', ' ', substr($data, 2));
                $this->showActiveCommunes($wilaya);
                
            } elseif (strpos($data, 'com_') === 0) {
                // User clicked a Commune (e.g., "com_El-Eulma")
                $commune = str_replace('-', ' ', substr($data, 4));
                $this->findRestaurantsByCommune($commune);
                
            // --- EXISTING: RESTAURANT & MENU ROUTING ---
            
            } elseif (strpos($data, 'r_') === 0) {
                // View Restaurant Categories: "r_6"
                $rest_id = (int) str_replace('r_', '', $data);
                $this->showCategories($rest_id);
                
            } elseif (strpos($data, 'c_') === 0) {
                // View Category Items: "c_50_r_6" (Cat 50, Rest 6)
                preg_match('/c_(\d+)_r_(\d+)/', $data, $matches);
                $this->showItems($matches[1], $matches[2]);
                
            } elseif (strpos($data, 'i_') === 0) {
                // View Item Details & Variants: "i_125_r_6" (Item 125, Rest 6)
                preg_match('/i_(\d+)_r_(\d+)/', $data, $matches);
                $this->showItemDetails($matches[1], $matches[2]);
                
            } elseif (strpos($data, 'add_') === 0) {
                // Add to Cart: "add_v_1_i_125_r_6" (Variant 1, Item 125, Rest 6)
                // OR "add_v_0_i_122_r_6" (No variant, Item 122, Rest 6)
                $this->sendMessage($this->chat_id, "✅ Ajouté au panier ! (Cart logic to be implemented)");
            }
            return;
        }
    }

    private function sendWelcome() {
        $text = "👋 Bienvenue sur l'application de livraison DZ !\n\nPour trouver les restaurants ouverts, veuillez partager votre position.";
        
        // 1. Send the GPS button (Bottom Keyboard)
        $text = "👋 Bienvenue sur l'application de livraison DZ !\n\nComment souhaitez-vous trouver les restaurants autour de vous ?";
        $gpsKeyboard = [
            'keyboard' => [
                [['text' => '📍 Partager ma position GPS', 'request_location' => true]]
            ],
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ];
        $this->sendMessage($this->chat_id, $text, json_encode($gpsKeyboard));

        // 2. The Dynamic Inline Menu based on their Role
        $inlineButtons = [];

        // Manual Location Button
        $inlineButtons[] = [['text' => "🗺️ Choisir ma ville manuellement", 'callback_data' => "manual_loc"]];

        // Role-based Buttons
        if ($this->user_role === 'customer') {
            $inlineButtons[] = [['text' => "🚴 Devenir Livreur", 'callback_data' => "register_driver"]];
        }
        
        // If they are a restaurant owner, let them switch back to their kitchen dashboard
        if ($this->user_role === 'restaurant') {
            $inlineButtons[] = [['text' => "🔄 Basculer en Mode Cuisine", 'callback_data' => "switch_kitchen"]];
        }
        
        // If they are an approved driver, let them switch to their logistics feed
        if ($this->user_role === 'driver') {
            $inlineButtons[] = [['text' => "🔄 Basculer en Mode Livreur", 'callback_data' => "switch_driver"]];
        }

        $this->sendMessage($this->chat_id, "Ou utilisez les options ci-dessous :", json_encode(['inline_keyboard' => $inlineButtons]));

        
    }

    // --- NEW MANUAL LOCATION METHODS ---

    private function showActiveWilayas() {
        // Only fetch Wilayas where you have open restaurants
        $sql = "SELECT DISTINCT wilaya_name FROM company WHERE is_open = 1 AND wilaya_name IS NOT NULL ORDER BY wilaya_name ASC";
        $stmt = $this->pdo->query($sql);
        $wilayas = $stmt->fetchAll();

        if (count($wilayas) === 0) {
            $this->sendMessage($this->chat_id, "Désolé, aucun restaurant n'est actuellement ouvert sur la plateforme.");
            return;
        }

        $buttons = [];
        foreach ($wilayas as $w) {
            // Replace spaces with dashes for safe callback data transmission
            $safeName = str_replace(' ', '-', $w['wilaya_name']);
            $buttons[] = [['text' => "📍 " . $w['wilaya_name'], 'callback_data' => "w_" . $safeName]];
        }

        $this->sendMessage($this->chat_id, "Veuillez sélectionner votre Wilaya :", json_encode(['inline_keyboard' => $buttons]));
    }

    private function showActiveCommunes($wilaya) {
        $sql = "SELECT DISTINCT commune_name FROM company WHERE is_open = 1 AND wilaya_name = :w ORDER BY commune_name ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['w' => $wilaya]);
        $communes = $stmt->fetchAll();

        $buttons = [];
        foreach ($communes as $c) {
            $safeName = str_replace(' ', '-', $c['commune_name']);
            $buttons[] = [['text' => "🏘️ " . $c['commune_name'], 'callback_data' => "com_" . $safeName]];
        }
        $buttons[] = [['text' => "🔙 Retour aux Wilayas", 'callback_data' => "manual_loc"]];

        $this->sendMessage($this->chat_id, "Veuillez sélectionner votre Commune :", json_encode(['inline_keyboard' => $buttons]));
    }

    private function findRestaurantsByCommune($commune) {
        $sql = "SELECT id, companyName FROM company WHERE is_open = 1 AND commune_name = :c";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['c' => $commune]);
        $restaurants = $stmt->fetchAll();

        if (count($restaurants) > 0) {
            $buttons = [];
            foreach ($restaurants as $rest) {
                $buttons[] = [['text' => "🏪 " . $rest['companyName'], 'callback_data' => "r_" . $rest['id']]];
            }
            // We use the same 'r_' prefix so it automatically hooks into your existing Menu logic!
            
            $this->sendMessage($this->chat_id, "✅ Restaurants ouverts à <b>$commune</b> :", json_encode(['inline_keyboard' => $buttons]));
        } else {
            $this->sendMessage($this->chat_id, "Désolé, aucun restaurant n'est disponible dans cette commune pour le moment.");
        }
    }

    private function findNearbyRestaurants($lat, $lon) {
        $this->sendMessage($this->chat_id, "⏳ Recherche des restaurants à proximité...", json_encode(['remove_keyboard' => true]));

        $sql = "SELECT id, companyName, 
                ( 6371 * acos( cos( radians(:lat) ) * cos( radians( latitude ) ) * 
                cos( radians( longitude ) - radians(:lon) ) + sin( radians(:lat) ) * 
                sin( radians( latitude ) ) ) ) AS distance 
                FROM company 
                WHERE latitude IS NOT NULL AND longitude IS NOT NULL AND is_open = 1
                HAVING distance < 15 
                ORDER BY distance ASC";
                
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['lat' => $lat, 'lon' => $lon]);
        $restaurants = $stmt->fetchAll();
        
        if (count($restaurants) > 0) {
            $buttons = [];
            foreach ($restaurants as $rest) {
                $dist = round($rest['distance'], 1);
                $buttons[] = [
                    ['text' => "🏪 " . $rest['companyName'] . " (~" . $dist . " km)", 'callback_data' => "r_" . $rest['id']]
                ];
            }
            $this->sendMessage($this->chat_id, "✅ Voici les restaurants dans votre zone :", json_encode(['inline_keyboard' => $buttons]));
        } else {
            $this->sendMessage($this->chat_id, "Désolé, aucun restaurant n'est disponible dans votre zone pour le moment.");
        }
    }

    private function showCategories($rest_id) {
        // Fetch available categories for this company, ordered by the CMS 'display' column
        $sql = "SELECT id, category FROM category 
                WHERE company_id = :rest_id AND available = 1 AND display > 0 
                ORDER BY display ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['rest_id' => $rest_id]);
        $categories = $stmt->fetchAll();

        if (count($categories) > 0) {
            $buttons = [];
            // Create a 2-column grid for categories to save screen space
            $row = [];
            foreach ($categories as $i => $cat) {
                $row[] = ['text' => "📂 " . $cat['category'], 'callback_data' => "c_" . $cat['id'] . "_r_" . $rest_id];
                if (count($row) == 2 || $i == count($categories) - 1) {
                    $buttons[] = $row;
                    $row = [];
                }
            }
            
            // Add a "Back to Restaurants" button
            $buttons[] = [['text' => "🔙 Retour", 'callback_data' => "back_to_rest"]];
            
            $this->sendMessage($this->chat_id, "😋 Que souhaitez-vous commander ?", json_encode(['inline_keyboard' => $buttons]));
        } else {
            $this->sendMessage($this->chat_id, "⚠️ Ce restaurant n'a pas encore de menu actif.");
        }
    }

    private function showItems($cat_id, $rest_id) {
        // Fetch items that are available and belong to this category
        $sql = "SELECT id, title, basePrice FROM object 
                WHERE category_id = :cat_id AND company_id = :rest_id AND objAvailable = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['cat_id' => $cat_id, 'rest_id' => $rest_id]);
        $items = $stmt->fetchAll();

        if (count($items) > 0) {
            $buttons = [];
            foreach ($items as $item) {
                // Determine if it says "From XXX DA" (has variants) or just "XXX DA"
                $priceLabel = ($item['basePrice'] > 0) ? " (" . $item['basePrice'] . " DA)" : " (Voir Tailles)";
                $buttons[] = [
                    ['text' => "🍕 " . $item['title'] . $priceLabel, 'callback_data' => "i_" . $item['id'] . "_r_" . $rest_id]
                ];
            }
            $buttons[] = [['text' => "🔙 Retour aux catégories", 'callback_data' => "r_" . $rest_id]];

            $this->sendMessage($this->chat_id, "📋 Choisissez un plat :", json_encode(['inline_keyboard' => $buttons]));
        } else {
            $this->sendMessage($this->chat_id, "⚠️ Cette catégorie est vide.");
        }
    }

    private function showItemDetails($item_id, $rest_id) {
        // 1. Get the base item details
        $stmt = $this->pdo->prepare("SELECT title, description, basePrice FROM object WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $item_id]);
        $item = $stmt->fetch();

        if (!$item) return;

        $text = "🍽️ <b>" . $item['title'] . "</b>\n";
        if (!empty($item['description'])) {
            $text .= "📝 <i>" . $item['description'] . "</i>\n";
        }
        $text .= "\nSélectionnez une option pour ajouter au panier :";

        // 2. Check the `price` table for variants (L, XL, XXL) joined with `attribute_value`
        $sql = "SELECT p.price, av.attributeValue, av.id as val_id 
                FROM price p 
                JOIN attribute_value av ON p.attributeValue_id = av.id 
                WHERE p.object_id = :obj_id 
                ORDER BY p.price ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['obj_id' => $item_id]);
        $variants = $stmt->fetchAll();

        $buttons = [];

        if (count($variants) > 0) {
            // It has variants! Create a button for each size/attribute
            foreach ($variants as $var) {
                $buttons[] = [
                    ['text' => "🛒 " . $var['attributeValue'] . " - " . $var['price'] . " DA", 
                     'callback_data' => "add_v_" . $var['val_id'] . "_i_" . $item_id . "_r_" . $rest_id]
                ];
            }
        } else {
            // No variants, just a standard item with a basePrice
            $buttons[] = [
                ['text' => "🛒 Ajouter - " . $item['basePrice'] . " DA", 
                 'callback_data' => "add_v_0_i_" . $item_id . "_r_" . $rest_id]
            ];
        }

        $this->sendMessage($this->chat_id, $text, json_encode(['inline_keyboard' => $buttons]));
    }

    private function sendMessage($chat_id, $text, $reply_markup = null) {
        $botToken = "8935407487:AAFXdMAi_JjmtuqlyceCmK2ogfNxocNqjNY"; // Ensure this is loaded securely via config
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