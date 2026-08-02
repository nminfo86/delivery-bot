<?php

class CustomerHandler {
    private $pdo;
    private $chat_id;
    private $telegram_id;
    private $user_role;
    private $user_data;

    public function __construct($pdo, $chat_id, $telegram_id, $user_role, $user_data) {
        $this->pdo = $pdo;
        $this->chat_id = $chat_id;
        $this->telegram_id = $telegram_id;
        $this->user_role = $user_role;
        $this->user_data = $user_data;
    }

    public function handle($update) {
        $message = $update['message'] ?? null;
        $callback = $update['callback_query'] ?? null;

        if ($message) {
            $text = trim($message['text'] ?? '');
            
            if ($text === '/start') {
                $this->clearUserState();
                $this->sendWelcome();
                return;
            }
            // Ajout des commandes pour le menu bleu
            if ($text === '/devenir_livreur') {
                $this->sendMessage($this->chat_id, "🛵 <b>Devenir Livreur</b>\nEnvoyez-nous vos informations (Nom, Prénom, Numéro, Wilaya, Type de moto) pour valider votre compte !");
                return;
            }

            if ($text === '/devenir_restaurateur') {
                $this->sendMessage($this->chat_id, "👨‍🍳 <b>Devenir Restaurateur</b>\nContactez l'administration pour ajouter votre restaurant sur la plateforme.");
                return;
            }

            // 1. Intercept GPS Location
            if (isset($message['location'])) {
                $lat = $message['location']['latitude'];
                $lon = $message['location']['longitude'];
                
                $stmt = $this->pdo->prepare("UPDATE telegram_users SET latitude = :lat, longitude = :lon, commune_name = NULL WHERE telegram_id = :tid");
                $stmt->execute(['lat' => $lat, 'lon' => $lon, 'tid' => $this->telegram_id]);
                $this->user_data['latitude'] = $lat;
                $this->user_data['longitude'] = $lon;

                $this->findNearbyRestaurants($lat, $lon);
                return;
            }

            // 2. Intercept Contact (Phone Number)
            if (isset($message['contact'])) {
                $phone = $message['contact']['phone_number'];
                
                $stmt = $this->pdo->prepare("UPDATE telegram_users SET phone = :phone WHERE telegram_id = :tid");
                $stmt->execute(['phone' => $phone, 'tid' => $this->telegram_id]);
                $this->user_data['phone'] = $phone;

                if (!empty($this->user_data['current_state']) && strpos($this->user_data['current_state'], 'AWAITING_PHONE_R_') === 0) {
                    $rest_id = (int) str_replace('AWAITING_PHONE_R_', '', $this->user_data['current_state']);
                    $this->showOrderSummary($rest_id);
                }
                return;
            }
        }

        if ($callback) {
            $data = $callback['data'];

            // Nouvelles actions des boutons d'accueil
            if ($data === 'start_order') {
                $this->showCitySelection();
                return;
            } elseif ($data === 'register_restaurant') {
                $this->sendMessage($this->chat_id, "👨‍🍳 <b>Devenir Restaurateur</b>\nContactez l'administration pour ajouter votre menu sur la plateforme.");
                return;
            } elseif ($data === 'register_driver') {
                $this->sendMessage($this->chat_id, "🛵 <b>Devenir Livreur</b>\nEnvoyez vos informations à l'administration pour créer votre profil livreur.");
                return;
            }

            // Add this right at the top of your inline button routing
            if ($data === 'ignore_closed') {
                $this->sendMessage($this->chat_id, "⚠️ Ce restaurant est fermé aujourd'hui (Jour de repos).");
                return;
            }
            
            elseif ($data === 'manual_loc') {
                $this->showActiveWilayas();
            } elseif (strpos($data, 'w_') === 0) {
                $wilaya = str_replace('-', ' ', substr($data, 2));
                $this->showActiveCommunes($wilaya);
            } elseif (strpos($data, 'com_') === 0) {
                $commune = str_replace('-', ' ', substr($data, 4));
                $stmt = $this->pdo->prepare("UPDATE telegram_users SET commune_name = :com, latitude = NULL, longitude = NULL WHERE telegram_id = :tid");
                $stmt->execute(['com' => $commune, 'tid' => $this->telegram_id]);
                $this->user_data['commune_name'] = $commune;
                $this->findRestaurantsByCommune($commune);
            } elseif (strpos($data, 'r_') === 0) {
                $rest_id = (int) str_replace('r_', '', $data);
                $this->showCategories($rest_id);
            } elseif (strpos($data, 'c_') === 0) {
                preg_match('/c_(\d+)_r_(\d+)/', $data, $matches);
                $this->showItems($matches[1], $matches[2]);
            } elseif (strpos($data, 'i_') === 0) {
                preg_match('/i_(\d+)_r_(\d+)/', $data, $matches);
                $this->showItemDetails($matches[1], $matches[2]);
            } elseif (strpos($data, 'add_') === 0) {
                preg_match('/add_v_(\d+)_i_(\d+)_r_(\d+)/', $data, $matches);
                $this->addItemToCart((int)$matches[2], (int)$matches[1], (int)$matches[3]);
            } elseif (strpos($data, 'cart_r_') === 0) {
                $rest_id = (int) str_replace('cart_r_', '', $data);
                $this->showCart($rest_id);
            } elseif (preg_match('/^cart_(inc|dec|del)_(\d+)_r_(\d+)$/', $data, $matches)) {
                $this->updateCartItem($matches[1], (int)$matches[2], (int)$matches[3]);
            } elseif (strpos($data, 'checkout_r_') === 0) {
                $rest_id = (int) str_replace('checkout_r_', '', $data);
                $this->processCheckout($rest_id);
            } elseif (strpos($data, 'place_order_r_') === 0) {
                $rest_id = (int) str_replace('place_order_r_', '', $data);
                $this->finalizeOrder($rest_id);
            }
            return;
        }
    }

    private function getOrCreateDraftOrderId($rest_id) {
        $_SESSION['company_id'] = $rest_id;
        $stmt = $this->pdo->prepare("SELECT id FROM ordere WHERE cookieID = :cid AND valid = 0 AND company_id = :cid_comp ORDER BY id DESC LIMIT 1");
        $stmt->execute(['cid' => 'TG_' . $this->telegram_id, 'cid_comp' => $rest_id]);
        $draft = $stmt->fetch();

        if ($draft) {
            $_SESSION['ordere_id'] = $draft['id'];
            return $draft['id'];
        }

        $ordere_id = JsonOrdere::createOrder(false);
        $stmt = $this->pdo->prepare("UPDATE ordere SET cookieID = :cid, company_id = :rest_id WHERE id = :oid");
        $stmt->execute(['cid' => 'TG_' . $this->telegram_id, 'rest_id' => $rest_id, 'oid' => $ordere_id]);
        $_SESSION['ordere_id'] = $ordere_id;
        return $ordere_id;
    }

    private function addItemToCart($item_id, $val_id, $rest_id) {
        $ordere_id = $this->getOrCreateDraftOrderId($rest_id);
        $attributeValueId = ($val_id > 0) ? $val_id : null;

        $subOrder = new SubOrder();
        $subOrder->setOrdere_id($ordere_id);
        $subOrder->setObject_id($item_id);
        $subOrder->setAttributeValue_id($attributeValueId);
        $subOrder->setQuantity(1);

        $uPrice = ($attributeValueId !== null) ? JsonPrice::getPrice($item_id, $attributeValueId, false) : JsonObject::getObjectBasePrice($item_id);
        $uCost = ($attributeValueId !== null) ? JsonPrice::getCost($item_id, $attributeValueId, false) : JsonObject::getObjectBaseCost($item_id);

        $subOrder->setUPrice($uPrice);
        $subOrder->setUCost($uCost);
        $subOrder->setSubTotal($uPrice);
        $subOrder->setSubCost($uCost);
        $subOrder->setSubProgression(JsonCategory::isPrepare($item_id) ? Config::$orderStateNew : Config::$orderStateReady);

        JsonSubOrder::createSubOrder($subOrder, false);

        $buttons = [
            [['text' => "🛒 Voir mon Panier", 'callback_data' => "cart_r_" . $rest_id]],
            [['text' => "➕ Continuer le menu", 'callback_data' => "r_" . $rest_id]]
        ];
        $this->sendMessage($this->chat_id, "✅ Produit ajouté au panier !", json_encode(['inline_keyboard' => $buttons]));
    }

    private function showCart($rest_id) {
        $ordere_id = $this->getOrCreateDraftOrderId($rest_id);
        $subOrders = JsonSubOrder::getSubOrdersOfOrder($ordere_id, false);

        if (empty($subOrders)) {
            $buttons = [[['text' => "📜 Reprendre le Menu", 'callback_data' => "r_" . $rest_id]]];
            $this->sendMessage($this->chat_id, "🛒 Votre panier est vide.", json_encode(['inline_keyboard' => $buttons]));
            return;
        }

        $text = "🛒 <b>Votre Panier :</b>\n━━━━━━━━━━━━━━━━━\n";
        $total = 0;
        $buttons = [];

        foreach ($subOrders as $sub) {
            $title = $sub['title'] . (!empty($sub['attributeValue']) ? " (" . $sub['attributeValue'] . ")" : "");
            $qty = $sub['quantity'];
            $price = $sub['subTotal'];
            $total += $price;

            $text .= "• <b>$title</b>\n  └ Qte: $qty | " . $price . " DA\n";

            $sub_id = $sub['id'];
            $buttons[] = [
                ['text' => "➖", 'callback_data' => "cart_dec_{$sub_id}_r_{$rest_id}"],
                ['text' => "{$qty}", 'callback_data' => "ignore"],
                ['text' => "➕", 'callback_data' => "cart_inc_{$sub_id}_r_{$rest_id}"],
                ['text' => "🗑️", 'callback_data' => "cart_del_{$sub_id}_r_{$rest_id}"]
            ];
        }

        $text .= "━━━━━━━━━━━━━━━━━\n💰 <b>TOTAL : " . number_format($total, 2, '.', ' ') . " DA</b>";
        $buttons[] = [['text' => "🚀 Valider & Commander", 'callback_data' => "checkout_r_" . $rest_id]];
        $buttons[] = [['text' => "🛍️ Ajouter d'autres plats", 'callback_data' => "r_" . $rest_id]];

        $this->sendMessage($this->chat_id, $text, json_encode(['inline_keyboard' => $buttons]));
    }

    private function updateCartItem($action, $sub_id, $rest_id) {
        $sub = JsonSubOrder::getSubOrderById($sub_id, false);
        if (!$sub) return $this->showCart($rest_id);

        $currentQty = (int)$sub[0]['quantity'];
        if ($action === 'inc') JsonSubOrder::updateSubOrderQte($sub_id, $currentQty + 1);
        elseif ($action === 'dec' && $currentQty > 1) JsonSubOrder::updateSubOrderQte($sub_id, $currentQty - 1);
        elseif ($action === 'dec' || $action === 'del') JsonSubOrder::deleteSubOrder($sub_id, false);

        $this->showCart($rest_id);
    }

    private function processCheckout($rest_id) {
        if (empty($this->user_data['phone'])) {
            $stmt = $this->pdo->prepare("UPDATE telegram_users SET current_state = :st WHERE telegram_id = :tid");
            $stmt->execute(['st' => 'AWAITING_PHONE_R_' . $rest_id, 'tid' => $this->telegram_id]);

            $phoneKeyboard = ['keyboard' => [[['text' => '📱 Partager mon numéro', 'request_contact' => true]]], 'resize_keyboard' => true, 'one_time_keyboard' => true];
            $this->sendMessage($this->chat_id, "📱 <b>Numéro de téléphone requis</b>\n\nPour que le livreur puisse vous contacter, veuillez partager votre numéro.", json_encode($phoneKeyboard));
        } else {
            $this->showOrderSummary($rest_id);
        }
    }

    private function showOrderSummary($rest_id) {
        $this->clearUserState();
        $ordere_id = $this->getOrCreateDraftOrderId($rest_id);
        $subOrders = JsonSubOrder::getSubOrdersOfOrder($ordere_id, false);

        $total = 0;
        $summary = "📝 <b>Récapitulatif de la commande :</b>\n━━━━━━━━━━━━━━━━━\n";
        foreach ($subOrders as $sub) {
            $title = $sub['title'] . (!empty($sub['attributeValue']) ? " (" . $sub['attributeValue'] . ")" : "");
            $summary .= "• {$sub['quantity']}x {$title} - {$sub['subTotal']} DA\n";
            $total += $sub['subTotal'];
        }

        $locText = !empty($this->user_data['latitude']) ? "Position GPS enregistrée ✅" : "Commune de " . htmlspecialchars($this->user_data['commune_name']);

        $summary .= "━━━━━━━━━━━━━━━━━\n💰 <b>Total : " . number_format($total, 2, '.', ' ') . " DA</b>\n📱 <b>Tél :</b> {$this->user_data['phone']}\n📍 <b>Lieu :</b> {$locText}\n\nConfirmez-vous l'envoi en cuisine ?";
        $buttons = [[['text' => "✅ Confirmer la commande", 'callback_data' => "place_order_r_" . $rest_id]], [['text' => "🛒 Modifier le Panier", 'callback_data' => "cart_r_" . $rest_id]]];

        $this->sendMessage($this->chat_id, "Parfait !", json_encode(['remove_keyboard' => true]));
        $this->sendMessage($this->chat_id, $summary, json_encode(['inline_keyboard' => $buttons]));
    }

    private function finalizeOrder($rest_id) {
        $ordere_id = $this->getOrCreateDraftOrderId($rest_id);
        $order = JsonOrdere::getOrderById($ordere_id, false);
        if (!$order) return;

        $subOrders = JsonSubOrder::getSubOrdersOfOrder($ordere_id, false);
        $totalPrice = 0;
        foreach ($subOrders as $s) { $totalPrice += $s['subTotal']; }

        $orderCode = "C" . (JsonOrdere::getCountOrdersOfDay() + 1);

        $order->setCode($orderCode);
        $order->setCompany_id($rest_id);
        $order->setPlace(Config::$orderPlaceCarryWith);
        $order->setValid(1);
        $order->setPayed(0);
        $order->setCookieID("TG_" . $this->telegram_id);
        $order->setProgression('PENDING_RESTAURANT');
        $order->setOrderePrice($totalPrice);
        $order->setTotalTtc($totalPrice);
        
        $phone = $this->user_data['phone'];
        $order->setComment("Client Telegram | Tel: {$phone}");

        // 1. Update order using your CMS class
        JsonOrdere::updateOrder($order, false);
        JsonSubOrder::updateAllSubOrdersOfOrderByProgression($ordere_id, 'PENDING_RESTAURANT');

        // 2. Direct PDO update to set the native delivery columns which updateOrder ignores
        $updateSql = "UPDATE ordere SET delivery_lat = :lat, delivery_lon = :lon, delivery_address_note = :note, customer_telegram_id = :tid WHERE id = :oid";
        $stmt = $this->pdo->prepare($updateSql);
        $stmt->execute([
            'lat' => $this->user_data['latitude'],
            'lon' => $this->user_data['longitude'],
            'note' => $this->user_data['commune_name'],
            'tid' => $this->telegram_id,
            'oid' => $ordere_id
        ]);

        $this->sendMessage($this->chat_id, "🎉 <b>Commande envoyée !</b>\nCode: <b>{$orderCode}</b>\nLe restaurant la prépare et un livreur vous contactera.");
        $this->notifyRestaurantOwner($rest_id, $orderCode, $totalPrice, $phone);
    }

    private function notifyRestaurantOwner($rest_id, $orderCode, $totalPrice, $phone) {
        $stmt = $this->pdo->prepare("SELECT telegram_id FROM telegram_users WHERE company_id = :cid AND role = 'restaurant' LIMIT 1");
        $stmt->execute(['cid' => $rest_id]);
        $owner = $stmt->fetch();

        if ($owner) {
            $msg = "🔔 <b>NOUVELLE COMMANDE REÇUE !</b>\n\nCode : <b>{$orderCode}</b>\nMontant : <b>{$totalPrice} DA</b>\nTel : {$phone}";
            $buttons = [[['text' => "👨‍🍳 Ouvrir le Tableau de Bord", 'callback_data' => "dash"]]];
            $this->sendMessage($owner['telegram_id'], $msg, json_encode(['inline_keyboard' => $buttons]));
        }
    }

    private function clearUserState() {
        $stmt = $this->pdo->prepare("UPDATE telegram_users SET current_state = NULL WHERE telegram_id = :tid");
        $stmt->execute(['tid' => $this->telegram_id]);
    }

   private function sendWelcome() {
        // 1. Si le user est déjà Restaurateur (mais utilise le bot en mode client)
        if ($this->user_role === 'restaurant') {
            $this->sendMessage($this->chat_id, "👨‍🍳 <b>Mode Client Actif</b>\n(Utilisez /cuisine pour revenir à votre menu restaurant).");
            $this->showCitySelection();
            return;
        }
        
        // 2. Si le user est déjà Livreur (mais utilise le bot en mode client)
        if ($this->user_role === 'driver') {
            $this->sendMessage($this->chat_id, "🛵 <b>Mode Client Actif</b>\n(Utilisez /livreur pour revenir à votre tableau de bord).");
            $this->showCitySelection();
            return;
        }

        // 3. Si Client existant (il a déjà une commune ou une latitude enregistrée en base)
        if (!empty($this->user_data['commune_name']) || !empty($this->user_data['latitude'])) {
            $this->showCitySelection();
            return;
        }

        // 4. Toute première connexion (Nouveau Client)
        $text = "👋 Bienvenue sur <b>DZ FOOD DELIVERY</b> !\nQue souhaitez-vous faire ?";
        $buttons = [
            [['text' => "🍕 Commandez un repas ..", 'callback_data' => "start_order"]],
            [['text' => "👨‍🍳 Ajouter votre Restaurant", 'callback_data' => "register_restaurant"]],
            [['text' => "🛵 Devenir Livreur", 'callback_data' => "register_driver"]]
        ];
        
        $this->sendMessage($this->chat_id, $text, json_encode(['inline_keyboard' => $buttons]));
    }

    private function showCitySelection() {
        $text = "Pour trouver des restaurants, choisir votre ville ou partager votre position 📍:";
        $gpsKeyboard = ['keyboard' => [[['text' => '📍 Partager ma position GPS', 'request_location' => true]]], 'resize_keyboard' => true, 'one_time_keyboard' => true];
        $this->sendMessage($this->chat_id, $text, json_encode($gpsKeyboard));

        $inlineButtons = [[['text' => "🗺️ Choisir ma ville manuellement", 'callback_data' => "manual_loc"]]];
        $this->sendMessage($this->chat_id, "👇 Choisir une option :", json_encode(['inline_keyboard' => $inlineButtons]));
    }

    private function showActiveWilayas() {
        $stmt = $this->pdo->query("SELECT DISTINCT wilaya_name FROM company WHERE is_open = 1 AND wilaya_name IS NOT NULL ORDER BY wilaya_name ASC");
        $wilayas = $stmt->fetchAll();
        if (count($wilayas) === 0) return $this->sendMessage($this->chat_id, "Désolé, aucun restaurant n'est ouvert.");

        $buttons = [];
        foreach ($wilayas as $w) $buttons[] = [['text' => "📍 " . $w['wilaya_name'], 'callback_data' => "w_" . str_replace(' ', '-', $w['wilaya_name'])]];
        $this->sendMessage($this->chat_id, "Veuillez sélectionner votre Wilaya :", json_encode(['inline_keyboard' => $buttons]));
    }

    private function showActiveCommunes($wilaya) {
        $stmt = $this->pdo->prepare("SELECT DISTINCT commune_name FROM company WHERE is_open = 1 AND wilaya_name = :w ORDER BY commune_name ASC");
        $stmt->execute(['w' => $wilaya]);
        $communes = $stmt->fetchAll();

        $buttons = [];
        foreach ($communes as $c) $buttons[] = [['text' => "🏘️ " . $c['commune_name'], 'callback_data' => "com_" . str_replace(' ', '-', $c['commune_name'])]];
        $buttons[] = [['text' => "🔙 Retour aux Wilayas", 'callback_data' => "manual_loc"]];
        $this->sendMessage($this->chat_id, "Veuillez sélectionner votre Commune :", json_encode(['inline_keyboard' => $buttons]));
    }

   private function findRestaurantsByCommune($commune) {
        // Filter out expired subscriptions
        $sql = "SELECT id, companyName, day_off 
                FROM company 
                WHERE is_open = 1 
                AND subscription_status IN ('active', 'warning') 
                AND commune_name = :c";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['c' => $commune]);
        $restaurants = $stmt->fetchAll();

        if (count($restaurants) > 0) {
            $buttons = [];
            $currentDay = date('l'); // Gets current day in English (e.g., 'Friday')
            
            foreach ($restaurants as $rest) {
                if ($rest['day_off'] === $currentDay) {
                    // Show the restaurant but assign a dummy callback so it doesn't open the menu
                    $buttons[] = [['text' => "⛔ " . $rest['companyName'] . " (Fermé)", 'callback_data' => "ignore_closed"]];
                } else {
                    // Open and active
                    $buttons[] = [['text' => "🏪 " . $rest['companyName'], 'callback_data' => "r_" . $rest['id']]];
                }
            }
            $this->sendMessage($this->chat_id, "✅ Restaurants à <b>$commune</b> :", json_encode(['inline_keyboard' => $buttons]));
        } else {
            $this->sendMessage($this->chat_id, "Désolé, aucun restaurant n'est disponible dans cette commune pour le moment.");
        }
    }

    private function findNearbyRestaurants($lat, $lon) {
        $this->sendMessage($this->chat_id, "⏳ Recherche des restaurants...", json_encode(['remove_keyboard' => true]));
        
        // Filter out expired subscriptions and fetch day_off
        $sql = "SELECT id, companyName, day_off, 
                ( 6371 * acos( cos( radians(:lat) ) * cos( radians( latitude ) ) * 
                cos( radians( longitude ) - radians(:lon) ) + sin( radians(:lat) ) * 
                sin( radians( latitude ) ) ) ) AS distance 
                FROM company 
                WHERE latitude IS NOT NULL 
                AND longitude IS NOT NULL 
                AND is_open = 1 
                AND subscription_status IN ('active', 'warning')
                HAVING distance < 15 
                ORDER BY distance ASC";
                
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['lat' => $lat, 'lon' => $lon]);
        $restaurants = $stmt->fetchAll();
        
        if (count($restaurants) > 0) {
            $buttons = [];
            $currentDay = date('l'); // Gets current day in English
            
            foreach ($restaurants as $rest) {
                $dist = round($rest['distance'], 1);
                
                if ($rest['day_off'] === $currentDay) {
                    $buttons[] = [['text' => "⛔ " . $rest['companyName'] . " (~" . $dist . " km) - Fermé", 'callback_data' => "ignore_closed"]];
                } else {
                    $buttons[] = [['text' => "🏪 " . $rest['companyName'] . " (~" . $dist . " km)", 'callback_data' => "r_" . $rest['id']]];
                }
            }
            $this->sendMessage($this->chat_id, "✅ Voici les restaurants dans votre zone :", json_encode(['inline_keyboard' => $buttons]));
        } else {
            $this->sendMessage($this->chat_id, "Désolé, aucun restaurant disponible dans un rayon de 15 km.");
        }
    }

    private function showCategories($rest_id) {
        // Exclude the 1/4 and 1/2 fractional categories
        $sql = "SELECT id, category FROM category 
                WHERE company_id = :rest_id 
                AND available = 1 
                AND display > 0 
                AND category NOT LIKE '1/4%' 
                AND category NOT LIKE '1/2%'
                ORDER BY display ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['rest_id' => $rest_id]);
        $categories = $stmt->fetchAll();

        if (count($categories) > 0) {
            $buttons = []; $row = [];
            foreach ($categories as $i => $cat) {
                $row[] = ['text' => "📂 " . $cat['category'], 'callback_data' => "c_" . $cat['id'] . "_r_" . $rest_id];
                if (count($row) == 2 || $i == count($categories) - 1) { $buttons[] = $row; $row = []; }
            }
            $buttons[] = [['text' => "🛒 Voir Panier", 'callback_data' => "cart_r_" . $rest_id]];
            $buttons[] = [['text' => "🔙 Retour aux villes", 'callback_data' => "manual_loc"]];
            $this->sendMessage($this->chat_id, "😋 Que souhaitez-vous commander ?", json_encode(['inline_keyboard' => $buttons]));
        } else {
            $this->sendMessage($this->chat_id, "⚠️ Ce restaurant n'a pas encore de menu actif.");
        }
    }

    private function showItems($cat_id, $rest_id) {
        $stmt = $this->pdo->prepare("SELECT id, title, basePrice FROM object WHERE category_id = :cat_id AND company_id = :rest_id AND objAvailable = 1");
        $stmt->execute(['cat_id' => $cat_id, 'rest_id' => $rest_id]);
        $items = $stmt->fetchAll();

        if (count($items) > 0) {
            $buttons = [];
            foreach ($items as $item) {
                $priceLabel = ($item['basePrice'] > 0) ? " (" . $item['basePrice'] . " DA)" : " (Voir Tailles)";
                $buttons[] = [['text' => "🍕 " . $item['title'] . $priceLabel, 'callback_data' => "i_" . $item['id'] . "_r_" . $rest_id]];
            }
            $buttons[] = [['text' => "🛒 Voir Panier", 'callback_data' => "cart_r_" . $rest_id]];
            $buttons[] = [['text' => "🔙 Retour aux catégories", 'callback_data' => "r_" . $rest_id]];
            $this->sendMessage($this->chat_id, "📋 Choisissez un plat :", json_encode(['inline_keyboard' => $buttons]));
        } else {
            $this->sendMessage($this->chat_id, "⚠️ Cette catégorie est vide.");
        }
    }

    private function showItemDetails($item_id, $rest_id) {
        $stmt = $this->pdo->prepare("SELECT title, description, basePrice FROM object WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $item_id]);
        $item = $stmt->fetch();
        if (!$item) return;

        $text = "🍽️ <b>" . $item['title'] . "</b>\n";
        if (!empty($item['description'])) $text .= "📝 <i>" . $item['description'] . "</i>\n";
        $text .= "\nSélectionnez une option pour ajouter au panier :";

        $stmt = $this->pdo->prepare("SELECT p.price, av.attributeValue, av.id as val_id FROM price p JOIN attribute_value av ON p.attributeValue_id = av.id WHERE p.object_id = :obj_id ORDER BY p.price ASC");
        $stmt->execute(['obj_id' => $item_id]);
        $variants = $stmt->fetchAll();

        $buttons = [];
        if (count($variants) > 0) {
            foreach ($variants as $var) $buttons[] = [['text' => "🛒 " . $var['attributeValue'] . " - " . $var['price'] . " DA", 'callback_data' => "add_v_" . $var['val_id'] . "_i_" . $item_id . "_r_" . $rest_id]];
        } else {
            $buttons[] = [['text' => "🛒 Ajouter - " . $item['basePrice'] . " DA", 'callback_data' => "add_v_0_i_" . $item_id . "_r_" . $rest_id]];
        }
        $buttons[] = [['text' => "🔙 Retour", 'callback_data' => "r_" . $rest_id]];
        $this->sendMessage($this->chat_id, $text, json_encode(['inline_keyboard' => $buttons]));
    }

    private function sendMessage($chat_id, $text, $reply_markup = null) {
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
}
?>