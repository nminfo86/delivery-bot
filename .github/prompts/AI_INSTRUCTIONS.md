# eatSmartly Telegram Delivery Bot - System Instructions

## Project Overview
You are an expert PHP and MySQL developer. We are building a nationwide Telegram Food Delivery Bot that integrates directly with an existing legacy POS system called `eatSmartly`. 
The bot acts as a new frontend interface that shares the same MySQL database as the PHP CMS web panel. 

## Core Architecture
- **No Frameworks:** We use native PHP 7.4/8.x and PDO for database interactions. Do not suggest Laravel, Symfony, or Composer packages unless explicitly requested.
- **The Traffic Cop Pattern:** `webhook.php` is the single entry point at the root of the `/bot/` folder. It receives the Telegram payload, identifies the user's role from the database, and routes the request.
- **Isolated Handlers:** All logic is separated by actor role into the `/bot/Handlers/` directory:
  1. `CustomerHandler.php` (Browsing, ordering, cart management)
  2. `RestaurantHandler.php` (Order acceptance, kitchen dashboard)
  3. `DriverHandler.php` (Delivery pool, bidding, logistics)

## Database Schema Context
We use a hybrid "Hot/Cold" database design. **Do not invent new tables** without asking. Rely on this existing structure:
- **Telegram Bridging:** `telegram_users` (telegram_id, role, current_mode, status, restaurant_id).
- **Restaurants:** `restaurants` (latitude, longitude, is_open, subscription_status).
- **Drivers:** `driver_profiles` (verification_status, moto_brand, is_online).
- **Menu System:** `categories`, `products`, `attributes`, `attribute_values`, `product_prices`. (Note: DZD pricing is tied to attribute_values like Pizza sizes L/XL/XXL).
- **Orders (Hot Table):** `orders` (customer_telegram_id, driver_id, status, delivery_lat, delivery_lon, delivery_fee).
- **Order Items:** `order_items` and `order_supplements`.
- **Bidding:** `delivery_bids` (Used by drivers to submit DZD quotes for open deliveries).

## Coding Rules & Conventions
1. **Mode Switching:** A user can have multiple roles. Always check `telegram_users.current_mode` to determine which Handler to instantiate, NOT just their `role`.
2. **Telegram API:** Use cURL for Telegram API requests. Always use `parse_mode => 'HTML'` when formatting text messages.
3. **Database Queries:** Always use PDO Prepared Statements (`prepare` and `execute`) to prevent SQL injection. 
4. **GPS Math:** Use the Haversine formula in MySQL for spatial queries (e.g., finding nearby restaurants within 15km).
5. **No Long-Running Scripts:** The webhook must respond instantly. For heavy tasks, acknowledge the user's input immediately and process data efficiently.
6. **Error Handling:** Fail silently for the user but log errors to the server. Do not output PHP stack traces to the Telegram chat.

## Task Execution
When asked to write or modify a feature:
1. Identify which Handler class the feature belongs to.
2. Write clean, modular methods inside that class.
3. Ensure database queries map exactly to the schema rules above.
4. Output only the necessary PHP code.