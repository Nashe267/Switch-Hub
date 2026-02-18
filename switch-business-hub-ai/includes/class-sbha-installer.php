<?php
/**
 * Plugin Installer
 *
 * Handles plugin activation, deactivation, and database setup
 *
 * @package SwitchBusinessHub
 */

if (!defined('ABSPATH')) {
    exit;
}

class SBHA_Installer {

    public static function activate() {
        self::create_tables();
        self::create_default_options();
        self::create_default_services();
        set_transient('sbha_activation_redirect', true, 30);
        flush_rewrite_rules();
    }

    public static function deactivate() {
        flush_rewrite_rules();
    }

    private static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        // CUSTOMERS - Custom table (NOT WordPress users)
        $table_customers = $wpdb->prefix . 'sbha_customers';
        $sql_customers = "CREATE TABLE $table_customers (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            first_name varchar(100) NOT NULL,
            last_name varchar(100) NOT NULL,
            business_name varchar(255) DEFAULT '',
            email varchar(255) NOT NULL,
            cell_number varchar(50) NOT NULL,
            whatsapp_number varchar(50) DEFAULT '',
            password varchar(255) NOT NULL,
            reset_token varchar(100) DEFAULT '',
            reset_token_expiry datetime DEFAULT NULL,
            profile_image varchar(500) DEFAULT '',
            total_orders int(11) DEFAULT 0,
            total_spent decimal(12,2) DEFAULT 0.00,
            last_login datetime DEFAULT NULL,
            status enum('active','inactive','blocked') DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY email (email),
            KEY status (status)
        ) $charset_collate;";
        dbDelta($sql_customers);

        // SERVICES with image support
        $table_services = $wpdb->prefix . 'sbha_services';
        $sql_services = "CREATE TABLE $table_services (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            slug varchar(255) NOT NULL,
            category varchar(100) NOT NULL,
            description text,
            short_description varchar(500),
            base_price decimal(10,2) DEFAULT 0.00,
            price_type enum('fixed','starting_from','custom','hourly') DEFAULT 'fixed',
            features longtext,
            image_id bigint(20) UNSIGNED DEFAULT NULL,
            image_url varchar(500) DEFAULT '',
            gallery longtext,
            is_popular tinyint(1) DEFAULT 0,
            is_featured tinyint(1) DEFAULT 0,
            display_order int(11) DEFAULT 0,
            status enum('active','inactive','draft') DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY slug (slug),
            KEY category (category),
            KEY status (status)
        ) $charset_collate;";
        dbDelta($sql_services);

        // ORDERS/JOBS
        $table_orders = $wpdb->prefix . 'sbha_orders';
        $sql_orders = "CREATE TABLE $table_orders (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            order_number varchar(50) NOT NULL,
            customer_id bigint(20) UNSIGNED NOT NULL,
            service_id bigint(20) UNSIGNED,
            custom_service varchar(255) DEFAULT '',
            title varchar(255) NOT NULL,
            description text,
            quantity int(11) DEFAULT 1,
            urgency enum('standard','express','rush') DEFAULT 'standard',
            unit_price decimal(10,2) DEFAULT 0.00,
            total decimal(10,2) DEFAULT 0.00,
            client_budget decimal(10,2) DEFAULT NULL,
            budget_notes text,
            quote_status enum('pending','approved','declined') DEFAULT 'pending',
            quote_response_note text,
            quote_responded_at datetime DEFAULT NULL,
            files longtext,
            status enum('pending','quoted','confirmed','in_progress','completed','delivered','cancelled') DEFAULT 'pending',
            admin_response text,
            admin_response_date datetime DEFAULT NULL,
            customer_viewed_response tinyint(1) DEFAULT 0,
            quote_pdf_url varchar(500) DEFAULT '',
            invoice_pdf_url varchar(500) DEFAULT '',
            estimated_completion datetime DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY order_number (order_number),
            KEY customer_id (customer_id),
            KEY service_id (service_id),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";
        dbDelta($sql_orders);

        // QUOTES (AI-generated and manual)
        $table_quotes = $wpdb->prefix . 'sbha_quotes';
        $sql_quotes = "CREATE TABLE $table_quotes (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            quote_number varchar(50) NOT NULL,
            order_id bigint(20) UNSIGNED DEFAULT NULL,
            customer_id bigint(20) UNSIGNED DEFAULT NULL,
            email varchar(255) NOT NULL,
            phone varchar(50) DEFAULT '',
            company varchar(255) DEFAULT '',
            items longtext NOT NULL,
            item_count int(11) DEFAULT 0,
            needs_design tinyint(1) DEFAULT 0,
            design_details text,
            event_type varchar(100) DEFAULT '',
            event_date varchar(100) DEFAULT '',
            delivery_needed tinyint(1) DEFAULT 0,
            delivery_location text,
            special_notes text,
            chat_transcript longtext,
            subtotal decimal(10,2) DEFAULT 0.00,
            tax decimal(10,2) DEFAULT 0.00,
            total decimal(10,2) DEFAULT 0.00,
            valid_until datetime,
            notes text,
            admin_notes text,
            pdf_url varchar(500) DEFAULT '',
            status enum('pending','reviewed','quoted','accepted','rejected','expired') DEFAULT 'pending',
            sent_at datetime DEFAULT NULL,
            viewed_at datetime DEFAULT NULL,
            responded_at datetime DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY quote_number (quote_number),
            KEY order_id (order_id),
            KEY customer_id (customer_id),
            KEY email (email),
            KEY status (status)
        ) $charset_collate;";
        dbDelta($sql_quotes);

        // INVOICES
        $table_invoices = $wpdb->prefix . 'sbha_invoices';
        $sql_invoices = "CREATE TABLE $table_invoices (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            invoice_number varchar(50) NOT NULL,
            order_id bigint(20) UNSIGNED NOT NULL,
            customer_id bigint(20) UNSIGNED NOT NULL,
            items longtext NOT NULL,
            subtotal decimal(10,2) DEFAULT 0.00,
            tax decimal(10,2) DEFAULT 0.00,
            total decimal(10,2) DEFAULT 0.00,
            amount_paid decimal(10,2) DEFAULT 0.00,
            balance_due decimal(10,2) DEFAULT 0.00,
            due_date datetime,
            notes text,
            pdf_url varchar(500) DEFAULT '',
            status enum('draft','sent','viewed','partial','paid','overdue') DEFAULT 'draft',
            sent_at datetime DEFAULT NULL,
            paid_at datetime DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY invoice_number (invoice_number),
            KEY order_id (order_id),
            KEY customer_id (customer_id),
            KEY status (status)
        ) $charset_collate;";
        dbDelta($sql_invoices);

        // CONTACT MESSAGES
        $table_messages = $wpdb->prefix . 'sbha_messages';
        $sql_messages = "CREATE TABLE $table_messages (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            customer_id bigint(20) UNSIGNED DEFAULT NULL,
            name varchar(100) NOT NULL,
            email varchar(255) NOT NULL,
            phone varchar(50) DEFAULT '',
            subject varchar(255) DEFAULT '',
            message text NOT NULL,
            is_read tinyint(1) DEFAULT 0,
            replied tinyint(1) DEFAULT 0,
            reply_message text,
            replied_at datetime DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY is_read (is_read),
            KEY created_at (created_at)
        ) $charset_collate;";
        dbDelta($sql_messages);

        // NOTIFICATIONS
        $table_notifications = $wpdb->prefix . 'sbha_notifications';
        $sql_notifications = "CREATE TABLE $table_notifications (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            customer_id bigint(20) UNSIGNED NOT NULL,
            type varchar(50) NOT NULL,
            title varchar(255) NOT NULL,
            message text NOT NULL,
            link varchar(500) DEFAULT '',
            is_read tinyint(1) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY customer_id (customer_id),
            KEY is_read (is_read),
            KEY created_at (created_at)
        ) $charset_collate;";
        dbDelta($sql_notifications);

        // CUSTOMER SESSIONS (for login without WP)
        $table_sessions = $wpdb->prefix . 'sbha_sessions';
        $sql_sessions = "CREATE TABLE $table_sessions (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            customer_id bigint(20) UNSIGNED NOT NULL,
            session_token varchar(100) NOT NULL,
            ip_address varchar(45),
            user_agent text,
            expires_at datetime NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY session_token (session_token),
            KEY customer_id (customer_id),
            KEY expires_at (expires_at)
        ) $charset_collate;";
        dbDelta($sql_sessions);

        update_option('sbha_db_version', SBHA_DB_VERSION);
    }

    private static function create_default_options() {
        $defaults = array(
            'sbha_business_name' => 'Switch Graphics (Pty) Ltd',
            'sbha_business_email' => get_option('admin_email'),
            'sbha_business_phone' => '068 147 4232',
            'sbha_whatsapp' => '068 147 4232',
            'sbha_business_address' => '16 Harding Street, Newcastle, 2940',
            'sbha_business_reg_number' => 'Reg: 2023/000000/07',
            'sbha_business_csd_number' => 'CSD: MAAA0000000',
            'sbha_currency' => 'ZAR',
            'sbha_currency_symbol' => 'R',
            'sbha_tax_rate' => 15,
            'sbha_quote_validity_days' => 14,
            'sbha_order_prefix' => 'SBH',
            'sbha_quote_prefix' => 'QT-SBH',
            'sbha_invoice_prefix' => 'INV-SBH',
            'sbha_primary_color' => '#FF6600',
            'sbha_secondary_color' => '#1a1a2e',
            'sbha_gemini_api_key' => '',
        );

        foreach ($defaults as $key => $value) {
            if (get_option($key) === false) {
                add_option($key, $value);
            }
        }
    }

    private static function create_default_services() {
        global $wpdb;
        $table = $wpdb->prefix . 'sbha_services';

        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table");
        if ($count > 0) return;

        // Services with real SA market pricing
        $services = array(
            // PRINTING - Business Stationery
            array('Business Cards', 'business-cards', 'printing', '350gsm Matt/Gloss - From 100 cards', 177.00, 1, 1),
            array('Flyers', 'flyers', 'printing', 'A5 130gsm Gloss - From 100 flyers', 141.00, 1, 0),
            array('Brochures', 'brochures', 'printing', 'Folded leaflets - Various sizes', 322.00, 0, 0),
            array('Posters', 'posters', 'printing', 'A3 to A0 sizes - Gloss/Matt', 6.00, 0, 0),
            array('Stickers & Labels', 'stickers', 'printing', 'Paper, Vinyl or Clear - Die-cut available', 25.00, 0, 0),
            array('Document Printing', 'document-printing', 'printing', 'Colour/B&W - A4/A3', 1.00, 0, 0),
            
            // SIGNAGE - Banners & Boards
            array('PVC Banners', 'pvc-banners', 'signage', 'Outdoor vinyl banners with eyelets - R173/m²', 173.00, 1, 1),
            array('Pull-Up Banners', 'pull-up-banners', 'signage', '850x2000mm Standard with stand', 414.00, 1, 0),
            array('X-Banner Stands', 'x-banners', 'signage', '600x1600mm Budget option', 350.00, 0, 0),
            array('A-Frame Signs', 'a-frame-signs', 'signage', 'Pavement signs - A1 size', 650.00, 0, 0),
            array('Corex Boards', 'corex-boards', 'signage', 'Estate agent & directional signs', 31.00, 0, 0),
            array('Forex/PVC Board Signs', 'forex-signs', 'signage', 'Rigid board signage - R350/m²', 350.00, 0, 0),
            
            // WEDDING & EVENTS
            array('Welcome Boards', 'welcome-boards', 'wedding', 'Corex, Perspex, Wooden or Mirror', 180.00, 1, 1),
            array('Seating Charts', 'seating-charts', 'wedding', 'Table plan boards - Various materials', 280.00, 0, 0),
            array('Table Numbers', 'table-numbers', 'wedding', 'Card, Acrylic or Wooden - Per set', 15.00, 0, 0),
            array('Wedding Invitations', 'invitations', 'wedding', 'Flat, Folded or Acrylic', 8.00, 0, 0),
            
            // APPAREL
            array('T-Shirt Printing', 't-shirt-printing', 'apparel', 'Basic T-Shirt + DTF/Vinyl print', 80.00, 1, 0),
            array('Golf Shirts', 'golf-shirts', 'apparel', 'With embroidery or print', 130.00, 0, 0),
            array('Hoodies', 'hoodies', 'apparel', 'With front/back print', 220.00, 0, 0),
            array('Caps', 'caps', 'apparel', 'Embroidered or printed', 75.00, 0, 0),
            
            // VEHICLE BRANDING
            array('Vehicle Branding', 'vehicle-branding', 'vehicle', 'Sedan door decals - Logo & contact', 1400.00, 0, 1),
            array('Half Vehicle Wrap', 'half-wrap', 'vehicle', 'Sides and back coverage', 2500.00, 0, 0),
            array('Full Vehicle Wrap', 'full-wrap', 'vehicle', 'Complete vehicle branding', 5500.00, 0, 0),
            
            // LARGE FORMAT
            array('Canvas Prints', 'canvas-prints', 'largeformat', 'Stretched canvas - Gallery wrap', 180.00, 0, 0),
            array('Photo Boards', 'photo-boards', 'largeformat', 'Mounted photo prints', 150.00, 0, 0),
            array('Wall Decals', 'wall-decals', 'largeformat', 'Custom vinyl wall graphics', 200.00, 0, 0),
            
            // DESIGN SERVICES
            array('Logo Design', 'logo-design', 'design', '4 concepts, source files included', 950.00, 0, 1),
            array('Website Design', 'website-design', 'design', 'Responsive business website', 3500.00, 0, 0),
            array('Brand Identity Package', 'brand-identity', 'design', 'Logo + Stationery + Brand guide', 2500.00, 0, 0),
            
            // CORPORATE GIFTING
            array('Branded Mugs', 'branded-mugs', 'corporate', 'Ceramic mugs with logo', 65.00, 0, 0),
            array('Branded Pens', 'branded-pens', 'corporate', 'Printed promotional pens', 8.00, 0, 0),
            array('Calendars', 'calendars', 'corporate', 'Wall calendars - Custom branded', 25.00, 0, 0),
        );

        foreach ($services as $i => $s) {
            $wpdb->insert($table, array(
                'name' => $s[0],
                'slug' => $s[1],
                'category' => $s[2],
                'short_description' => $s[3],
                'base_price' => $s[4],
                'is_popular' => $s[5],
                'is_featured' => $s[6],
                'price_type' => 'starting_from',
                'display_order' => $i + 1,
                'status' => 'active'
            ));
        }
    }
}
