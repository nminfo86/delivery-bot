<?php
// Block direct HTTP access — only allow inclusion from other PHP files
if (count(get_included_files()) <= 1) {
    http_response_code(403);
    exit('Access denied');
}

$appVersionData = [
    'app_name'           => 'eatSmartly',
    'current_version'    => '1.3.0',
    'last_stable_update' => '2026-07-06',
    'server'               => 'WAMP x64 3.3.0 PHP 8.1.13 MySQL 8.0.31',
    'history'            => [
        [
            'version'              => '1.3.0',
            'release_date'         => '2026-07-06',
            'server'               => 'WAMP x64 3.3.0 PHP 8.1.13 MySQL 8.0.31',
            'type'                 => 'Minor',
            'title'                => 'Reprint All, Chef Labels & Hosting Support',
            'changes'              => [
                'Improved subcode sequence numbering to only count prepared items and removed the dash separator from supplement labels.',
                'Updated social media links in header and footer; refreshed licence contact page content.',
                'Renamed E-ticket and remaining "valid" labels to "Chef" consistently across checkout panel, history, and TV view.',
                'Added "Reprint All" button in checkout panel and checkout history for bulk order reprinting.',
                'Fixed JSON report class name reference in checkout panel.',
                'Improved licence validation to support hosting environments with additional configuration in Config.php and functions.php.',
            ],
            'required_migrations'  => [
                '001_initial_schema.sql',
                '002_performance_indexes.sql',
            ],
        ],
        [
            'version'              => '1.2.2',
            'release_date'         => '2026-07-02',
            'server'               => 'WAMP x64 3.3.0 PHP 8.1.13 MySQL 8.0.31',
            'type'                 => 'Patch',
            'title'                => 'DB Performance & SQL Filter Fix',
            'changes'              => [
                'Added performance indexes migration (002_performance_indexes.sql) to improve database query speed.',
                'Fixed SQL date filtering in JsonOrdere.php and JsonSubOrder.php by using %% for correct LIKE clause escaping.',
                'Created migrations/emptyDatabase.sql combining all migrations for streamlined new installer packages.',
            ],
            'required_migrations'  => [
                '001_initial_schema.sql',
                '002_performance_indexes.sql',
            ],
        ],
        [
            'version'              => '1.2.1',
            'release_date'         => '2026-06-26',
            'server'               => 'WAMP x64 3.3.0 PHP 8.1.13 MySQL 8.0.31',
            'type'                 => 'Patch',
            'title'                => 'TSPL Label & Schema Improvements',
            'changes'              => [
                'Improved TSPL 40×20 chef label: strip R-P reprint prefix from place, shorten each word of place and article name to first 3 characters for compact display.',
                'Added line feed spacing in ESC chef label for better readability.',
                'Added printer-all record to initial schema SQL and adjusted Config.php accordingly.',
            ],
            'required_migrations'  => [
                '001_initial_schema.sql',
            ],
        ],
        [
            'version'              => '1.2.0',
            'release_date'         => '2026-06-20',
            'server'               => 'WAMP x64 3.3.0 PHP 8.1.13 MySQL 8.0.31',
            'type'                 => 'Minor',
            'title'                => 'Printer-All Feature & UI Adjustments',
            'changes'              => [
                'Added printer-all feature and adjusted TSPL chef label printing.',
                'Changed UI buttons text from valid to chef.',
            ],
            'required_migrations'  => [
                '001_initial_schema.sql',
            ],
        ],
        [
            'version'              => '1.1.3',
            'release_date'         => '2026-05-30',
            'server'               => 'WAMP x64 3.3.0 PHP 8.1.13 MySQL 8.0.31',
            'type'                 => 'Patch',
            'title'                => 'Licence Debug & Access Control Fixes',
            'changes'              => [
                'Improved getHashedWindowsMAC() reliability for more robust MAC address detection during licence validation.',
                'Updated generateLicence.php to make licence issue debugging easier for clients.',
                'Adjusted user access error retry times and wait time in Config.php.',
            ],
            'required_migrations'  => [
                '001_initial_schema.sql',
            ],
        ],
        [
            'version'              => '1.1.2',
            'release_date'         => '2026-05-18',
            'server'               => 'WAMP x64 3.3.0 PHP 8.1.13 MySQL 8.0.31',
            'type'                 => 'Patch',
            'title'                => 'Version Badge & PHP Warning Fix',
            'changes'              => [
                'Fixed PHP 8.x variable declaration warning in JsonObject.php.',
                'Added version badge (V1.1.2) with SweetAlert info popup to index.php, rest.php, and admin panel footers.',
                'Introduced app_version.php (PHP-protected) replacing app_version.json — version data no longer directly accessible via browser.',
            ],
            'required_migrations'  => [
                '001_initial_schema.sql',
            ],
        ],
        [
            'version'              => '1.1.1',
            'release_date'         => '2026-05-13',
            'server'               => 'WAMP x64 3.3.0 PHP 8.1.13 MySQL 8.0.31',
            'type'                 => 'Patch',
            'title'                => 'Printer Delay & Chef Label Sub-Code Fix',
            'changes'              => [
                'Added configurable printerDelay (default 0.7s) in Config.php to fix printing timing issues on network printers.',
                'Added sub-order code (subCode) generation in JsonSubOrder.php and printed it on chef kitchen labels.',
                'Improved sub-code placement and formatting on chef labels.',
            ],
            'required_migrations'  => [
                '001_initial_schema.sql',
            ],
        ],
        [
            'version'              => '1.1.0',
            'release_date'         => '2026-04-20',
            'type'                 => 'Major',
            'server'               => 'WAMP x64 3.3.0 PHP 8.1.13 MySQL 8.0.31',
            'title'                => 'Initial Launch',
            'changes'              => [
                'First stable release of eatSmartly POS.',
                'Automated installer and migration system integrated.',
            ],
            'required_migrations'  => [
                '001_initial_schema.sql',
            ],
        ],
        [
            'version'              => '1.0.0',
            'release_date'         => '2026-05-03',
            'server'               => 'WAMP x64 3.3.0 PHP 8.1.13 MySQL 8.0.31',
            'type'                 => 'Minor',
            'title'                => 'Tax Management Update',
            'changes'              => [
                'Added VAT configuration for restaurant receipts.',
                'Fixed French translation bug in the settings menu.',
                'Improved print speed for thermal printers.',
            ],
            'required_migrations'  => [],
        ],
    ],
];
