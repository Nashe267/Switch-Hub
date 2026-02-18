<?php
/**
 * AJAX Handler v3.0
 *
 * All frontend AJAX operations - Fixed for SBH prefix, invoice tracking, auth
 *
 * @package SwitchBusinessHub
 * @version 3.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class SBHA_Ajax {

    public function __construct() {
        add_action('wp_ajax_nopriv_sbha_register', array($this, 'register'));
        add_action('wp_ajax_sbha_register', array($this, 'register'));
        add_action('wp_ajax_nopriv_sbha_login', array($this, 'login'));
        add_action('wp_ajax_sbha_login', array($this, 'login'));
        add_action('wp_ajax_sbha_logout', array($this, 'logout'));
        add_action('wp_ajax_nopriv_sbha_logout', array($this, 'logout'));
        add_action('wp_ajax_nopriv_sbha_reset_password', array($this, 'reset_password'));
        add_action('wp_ajax_sbha_reset_password', array($this, 'reset_password'));

        add_action('wp_ajax_sbha_submit_quote', array($this, 'submit_ai_quote'));
        add_action('wp_ajax_nopriv_sbha_submit_quote', array($this, 'submit_ai_quote'));

        add_action('wp_ajax_sbha_track_order', array($this, 'track_order'));
        add_action('wp_ajax_nopriv_sbha_track_order', array($this, 'track_order'));

        add_action('wp_ajax_sbha_get_my_orders', array($this, 'get_my_orders'));
        add_action('wp_ajax_nopriv_sbha_get_my_orders', array($this, 'get_my_orders'));

        add_action('wp_ajax_sbha_get_documents', array($this, 'get_documents'));
        add_action('wp_ajax_nopriv_sbha_get_documents', array($this, 'get_documents'));

        add_action('wp_ajax_sbha_contact', array($this, 'contact'));
        add_action('wp_ajax_nopriv_sbha_contact', array($this, 'contact'));

        add_action('wp_ajax_sbha_get_notifications', array($this, 'get_notifications'));
        add_action('wp_ajax_sbha_mark_read', array($this, 'mark_notification_read'));

        add_action('wp_ajax_sbha_check_session', array($this, 'check_session'));
        add_action('wp_ajax_nopriv_sbha_check_session', array($this, 'check_session'));

        add_action('wp_ajax_sbha_get_services', array($this, 'get_services'));
        add_action('wp_ajax_nopriv_sbha_get_services', array($this, 'get_services'));

        add_action('wp_ajax_sbha_approve_quote', array($this, 'approve_quote'));
        add_action('wp_ajax_sbha_decline_quote', array($this, 'decline_quote'));

        add_action('wp_ajax_sbha_ai_chat', array($this, 'ai_chat'));
        add_action('wp_ajax_nopriv_sbha_ai_chat', array($this, 'ai_chat'));
        
        add_action('wp_ajax_sbha_create_invoice', array($this, 'create_invoice'));
        add_action('wp_ajax_sbha_upload_payment_proof', array($this, 'upload_payment_proof'));
    }

    /**
     * Register customer - Email REQUIRED, phone required
     */
    public function register() {
        global $wpdb;

        $name = sanitize_text_field($_POST['name'] ?? '');
        $first_name = sanitize_text_field($_POST['first_name'] ?? '');
        $last_name = sanitize_text_field($_POST['last_name'] ?? '');
        
        if ($name && !$first_name) {
            $parts = explode(' ', $name, 2);
            $first_name = $parts[0];
            $last_name = $parts[1] ?? '';
        }
        
        $phone = sanitize_text_field($_POST['phone'] ?? $_POST['cell_number'] ?? '');
        $email = sanitize_email($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($first_name) || empty($phone) || empty($password)) {
            wp_send_json_error('Please enter name, WhatsApp number and password.');
        }

        if (empty($email)) {
            wp_send_json_error('Email address is required.');
        }

        if (strlen($password) < 4) {
            wp_send_json_error('Password must be at least 4 characters.');
        }

        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phone) < 10) {
            wp_send_json_error('Please enter a valid phone number.');
        }

        $table = $wpdb->prefix . 'sbha_customers';
        
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table WHERE cell_number = %s OR whatsapp_number = %s OR email = %s", 
            $phone, $phone, $email
        ));
        if ($exists) {
            wp_send_json_error('This phone number or email is already registered. Please login.');
        }

        $result = $wpdb->insert($table, array(
            'first_name' => $first_name,
            'last_name' => $last_name,
            'business_name' => '',
            'email' => $email,
            'cell_number' => $phone,
            'whatsapp_number' => $phone,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'status' => 'active'
        ));

        if (!$result) {
            wp_send_json_error('Registration failed. Please try again.');
        }

        $customer_id = $wpdb->insert_id;
        $token = $this->create_session($customer_id);

        setcookie('sbha_token', $token, time() + (30 * 24 * 60 * 60), COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);

        wp_send_json_success(array(
            'message' => 'Account created!',
            'customer' => array(
                'id' => $customer_id,
                'name' => $first_name . ' ' . $last_name,
                'phone' => $phone
            ),
            'token' => $token
        ));
    }

    public function login() {
        global $wpdb;

        $phone = sanitize_text_field($_POST['phone'] ?? '');
        $email = sanitize_email($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ((empty($phone) && empty($email)) || empty($password)) {
            wp_send_json_error('Enter phone/email and password.');
        }

        $table = $wpdb->prefix . 'sbha_customers';
        
        if ($phone) {
            $phone = preg_replace('/[^0-9]/', '', $phone);
            $customer = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table WHERE (cell_number = %s OR whatsapp_number = %s) AND status = 'active'",
                $phone, $phone
            ), ARRAY_A);
        } else {
            $customer = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table WHERE email = %s AND status = 'active'",
                $email
            ), ARRAY_A);
        }

        if (!$customer || !password_verify($password, $customer['password'])) {
            wp_send_json_error('Invalid phone/email or password.');
        }

        $wpdb->update($table, array('last_login' => current_time('mysql')), array('id' => $customer['id']));

        $token = $this->create_session($customer['id']);
        setcookie('sbha_token', $token, time() + (30 * 24 * 60 * 60), COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);

        wp_send_json_success(array(
            'message' => 'Welcome back!',
            'customer' => array(
                'id' => $customer['id'],
                'name' => $customer['first_name'] . ' ' . $customer['last_name'],
                'email' => $customer['email']
            ),
            'token' => $token
        ));
    }

    public function logout() {
        global $wpdb;
        $token = $_COOKIE['sbha_token'] ?? '';
        if ($token) {
            $wpdb->delete($wpdb->prefix . 'sbha_sessions', array('session_token' => $token));
        }
        setcookie('sbha_token', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);
        wp_send_json_success(array('message' => 'Logged out.'));
    }

    public function reset_password() {
        global $wpdb;

        $phone = sanitize_text_field($_POST['phone'] ?? '');
        $new_password = $_POST['password'] ?? $_POST['new_password'] ?? '';

        if (empty($phone)) {
            wp_send_json_error('Enter your WhatsApp number.');
        }

        if (empty($new_password) || strlen($new_password) < 4) {
            wp_send_json_error('Password must be at least 4 characters.');
        }

        $table = $wpdb->prefix . 'sbha_customers';
        $phone_clean = preg_replace('/[^0-9]/', '', $phone);
        $customer = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM $table WHERE cell_number LIKE %s OR whatsapp_number LIKE %s",
            '%' . $phone_clean . '%',
            '%' . $phone_clean . '%'
        ));

        if (!$customer) {
            wp_send_json_error('Account not found with this phone number.');
        }

        $wpdb->update($table, array('password' => password_hash($new_password, PASSWORD_DEFAULT)), array('id' => $customer->id));

        wp_send_json_success(array('message' => 'Password updated! You can now login.'));
    }

    /**
     * Create invoice from shop cart - SBH prefix
     */
    public function create_invoice() {
        global $wpdb;
        
        $customer_id = $this->get_customer_id();
        if (!$customer_id) {
            wp_send_json_error('Please login to place an order.');
        }
        
        $items = json_decode(stripslashes($_POST['items'] ?? '[]'), true);
        
        if (empty($items)) {
            wp_send_json_error('Cart is empty.');
        }
        
        // Ensure columns exist
        $table_name = $wpdb->prefix . 'sbha_quotes';
        $cols = $wpdb->get_col("SHOW COLUMNS FROM {$table_name}", 0);
        if (!in_array('quote_type', $cols)) {
            $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN quote_type VARCHAR(20) DEFAULT 'quote'");
        }
        if (!in_array('payment_proof', $cols)) {
            $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN payment_proof TEXT");
        }
        if (!in_array('files', $cols)) {
            $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN files LONGTEXT");
        }
        
        $max_num = $wpdb->get_var("SELECT MAX(CAST(REPLACE(REPLACE(quote_number, 'SBH', ''), 'INV-', '') AS UNSIGNED)) FROM {$table_name} WHERE quote_number LIKE '%SBH%'");
        $next = ($max_num ? intval($max_num) : 0) + 1;
        $invoice_number = 'SBH' . str_pad($next, 4, '0', STR_PAD_LEFT);
        
        $calculated_total = 0;
        foreach ($items as &$item) {
            $item_total = ($item['price'] ?? 0) * ($item['qty'] ?? $item['quantity'] ?? 1);
            $calculated_total += $item_total;
        }
        
        $wpdb->insert(
            $table_name,
            array(
                'quote_number' => $invoice_number,
                'customer_id' => $customer_id,
                'email' => '',
                'items' => json_encode($items),
                'total' => $calculated_total,
                'status' => 'pending',
                'quote_type' => 'invoice',
                'created_at' => current_time('mysql')
            )
        );
        
        $order_id = $wpdb->insert_id;
        
        $customer = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}sbha_customers WHERE id = %d",
            $customer_id
        ));
        
        $admin_email = get_option('sbha_business_email', get_option('admin_email'));
        $body = "New Shop Order: {$invoice_number}\n\n";
        $body .= "Customer: {$customer->first_name} {$customer->last_name}\n";
        $body .= "Phone: {$customer->cell_number}\n";
        $body .= "Email: {$customer->email}\n\n";
        $body .= "Items:\n";
        foreach ($items as $item) {
            $body .= "- {$item['name']} / {$item['variation']} × {$item['qty']} = R" . (($item['price'] ?? 0) * ($item['qty'] ?? 1)) . "\n";
        }
        $body .= "\nTotal: R" . number_format($calculated_total, 2) . "\n";
        wp_mail($admin_email, "New Order: {$invoice_number}", $body);
        
        wp_send_json_success(array(
            'invoice_number' => $invoice_number,
            'order_id' => $order_id,
            'total' => $calculated_total,
            'message' => 'Invoice created!'
        ));
    }

    public function upload_payment_proof() {
        global $wpdb;
        
        $customer_id = $this->get_customer_id();
        if (!$customer_id) {
            wp_send_json_error('Please login first.');
        }
        
        $order_id = intval($_POST['order_id'] ?? 0);
        $invoice_number = sanitize_text_field($_POST['invoice_number'] ?? '');
        
        if (!$order_id && $invoice_number) {
            $order = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}sbha_quotes WHERE quote_number = %s AND customer_id = %d",
                $invoice_number, $customer_id
            ));
            if ($order) $order_id = $order->id;
        }
        
        if (!$order_id) {
            wp_send_json_error('Invalid order.');
        }
        
        $order = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}sbha_quotes WHERE id = %d AND customer_id = %d",
            $order_id, $customer_id
        ));
        
        if (!$order) {
            wp_send_json_error('Order not found.');
        }
        
        if (empty($_FILES['payment_proof'])) {
            wp_send_json_error('No file uploaded.');
        }
        
        $file = $_FILES['payment_proof'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error('Upload error. Please try again.');
        }
        
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        
        $upload = wp_handle_upload($file, array('test_form' => false));
        
        if (isset($upload['error'])) {
            wp_send_json_error('Upload failed: ' . $upload['error']);
        }
        
        $column_exists = $wpdb->get_var("SHOW COLUMNS FROM {$wpdb->prefix}sbha_quotes LIKE 'payment_proof'");
        if (!$column_exists) {
            $wpdb->query("ALTER TABLE {$wpdb->prefix}sbha_quotes ADD COLUMN payment_proof TEXT");
        }
        
        $wpdb->update(
            $wpdb->prefix . 'sbha_quotes',
            array('payment_proof' => $upload['url']),
            array('id' => $order_id)
        );
        
        wp_send_json_success(array(
            'message' => 'Payment proof uploaded!',
            'proof_url' => $upload['url']
        ));
    }

    /**
     * Track order - by INVOICE NUMBER only (SBH prefix)
     */
    public function track_order() {
        global $wpdb;

        $invoice = sanitize_text_field($_POST['invoice'] ?? '');
        if (empty($invoice)) {
            wp_send_json_error('Please enter your invoice number (e.g. SBH0001).');
        }

        $invoice = strtoupper(str_replace(array(' ', '-'), '', $invoice));
        
        if (preg_match('/^(\d+)$/', $invoice, $matches)) {
            $invoice = 'SBH' . str_pad($matches[1], 4, '0', STR_PAD_LEFT);
        } elseif (preg_match('/^SBH(\d+)$/i', $invoice, $matches)) {
            $invoice = 'SBH' . str_pad($matches[1], 4, '0', STR_PAD_LEFT);
        } elseif (preg_match('/^INV-?SBH-?(\d+)$/i', $invoice, $matches)) {
            $invoice = 'SBH' . str_pad($matches[1], 4, '0', STR_PAD_LEFT);
        } elseif (preg_match('/^QT-?SBH-?(\d+)$/i', $invoice, $matches)) {
            $invoice = 'QT-SBH' . str_pad($matches[1], 4, '0', STR_PAD_LEFT);
        }

        $order = $wpdb->get_row($wpdb->prepare("
            SELECT * FROM {$wpdb->prefix}sbha_quotes
            WHERE quote_number = %s OR quote_number LIKE %s
            LIMIT 1
        ", $invoice, '%' . $invoice . '%'), ARRAY_A);

        if (!$order) {
            $order = $wpdb->get_row($wpdb->prepare("
                SELECT o.*, c.first_name, c.last_name, s.name as service_name
                FROM {$wpdb->prefix}sbha_orders o
                LEFT JOIN {$wpdb->prefix}sbha_customers c ON o.customer_id = c.id
                LEFT JOIN {$wpdb->prefix}sbha_services s ON o.service_id = s.id
                WHERE o.order_number = %s OR o.order_number LIKE %s
                LIMIT 1
            ", $invoice, '%' . $invoice . '%'), ARRAY_A);
            
            if ($order) {
                wp_send_json_success(array(
                    'quote_number' => $order['order_number'],
                    'invoice_number' => $order['order_number'],
                    'status' => $order['status'],
                    'date' => date('d M Y', strtotime($order['created_at'])),
                    'description' => $order['service_name'] ?: $order['custom_service'] ?: $order['title'],
                    'total' => 'R' . number_format($order['total'], 2)
                ));
            }
        }

        if (!$order) {
            wp_send_json_error('Order not found. Please check your invoice number (e.g. SBH0001).');
        }

        $items = json_decode($order['items'] ?? '[]', true);
        $desc = '';
        if ($items) {
            foreach (array_slice($items, 0, 3) as $it) {
                $desc .= ($it['variant_name'] ?? $it['variation'] ?? $it['name'] ?? $it['product_name'] ?? '') . ', ';
            }
            $desc = rtrim($desc, ', ');
        }

        wp_send_json_success(array(
            'quote_number' => $order['quote_number'],
            'invoice_number' => $order['quote_number'],
            'status' => $order['status'],
            'payment_proof' => $order['payment_proof'] ?? '',
            'date' => date('d M Y', strtotime($order['created_at'])),
            'description' => $desc,
            'total' => 'R' . number_format($order['total'], 2)
        ));
    }

    public function get_my_orders() {
        global $wpdb;
        $customer_id = $this->get_customer_id();
        if (!$customer_id) {
            wp_send_json_success(array('orders' => array()));
        }
        $orders = $wpdb->get_results($wpdb->prepare("
            SELECT * FROM {$wpdb->prefix}sbha_quotes
            WHERE customer_id = %d ORDER BY created_at DESC LIMIT 50
        ", $customer_id), ARRAY_A);
        wp_send_json_success(array('orders' => $orders));
    }

    public function get_documents() {
        global $wpdb;
        $customer_id = $this->get_customer_id();
        if (!$customer_id) {
            wp_send_json_success(array('documents' => array()));
        }
        $docs = $wpdb->get_results($wpdb->prepare("
            SELECT * FROM {$wpdb->prefix}sbha_quotes
            WHERE customer_id = %d ORDER BY created_at DESC LIMIT 50
        ", $customer_id), ARRAY_A);
        wp_send_json_success(array('documents' => $docs));
    }

    public function contact() {
        global $wpdb;
        $name = sanitize_text_field($_POST['name'] ?? '');
        $email = sanitize_email($_POST['email'] ?? '');
        $phone = sanitize_text_field($_POST['phone'] ?? '');
        $message = sanitize_textarea_field($_POST['message'] ?? '');
        if (empty($name) || empty($message)) {
            wp_send_json_error('Fill in all required fields.');
        }
        $wpdb->insert($wpdb->prefix . 'sbha_messages', array(
            'customer_id' => $this->get_customer_id(),
            'name' => $name, 'email' => $email, 'phone' => $phone, 'message' => $message
        ));
        wp_mail(get_option('sbha_business_email', get_option('admin_email')),
            "Message from {$name}", "Name: {$name}\nEmail: {$email}\nPhone: {$phone}\n\n{$message}",
            array("Reply-To: {$email}"));
        wp_send_json_success(array('message' => 'Message sent!'));
    }

    public function get_notifications() {
        global $wpdb;
        $customer_id = $this->get_customer_id();
        if (!$customer_id) wp_send_json_success(array('notifications' => array(), 'unread' => 0));
        $notifs = $wpdb->get_results($wpdb->prepare("
            SELECT * FROM {$wpdb->prefix}sbha_notifications
            WHERE customer_id = %d ORDER BY created_at DESC LIMIT 20
        ", $customer_id), ARRAY_A);
        $unread = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) FROM {$wpdb->prefix}sbha_notifications WHERE customer_id = %d AND is_read = 0
        ", $customer_id));
        wp_send_json_success(array('notifications' => $notifs, 'unread' => intval($unread)));
    }

    public function mark_notification_read() {
        global $wpdb;
        $id = intval($_POST['id'] ?? 0);
        $customer_id = $this->get_customer_id();
        if ($id && $customer_id) {
            $wpdb->update($wpdb->prefix . 'sbha_notifications', array('is_read' => 1),
                array('id' => $id, 'customer_id' => $customer_id));
        }
        wp_send_json_success();
    }

    public function check_session() {
        $customer = $this->get_customer();
        if ($customer) {
            wp_send_json_success(array('logged_in' => true, 'customer' => array(
                'id' => $customer['id'],
                'name' => $customer['first_name'] . ' ' . $customer['last_name'],
                'email' => $customer['email']
            )));
        }
        wp_send_json_success(array('logged_in' => false));
    }

    public function get_services() {
        global $wpdb;
        $category = sanitize_text_field($_POST['category'] ?? 'all');
        $where = "status = 'active'";
        if ($category !== 'all') {
            $where .= $wpdb->prepare(" AND category = %s", $category);
        }
        $services = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}sbha_services WHERE {$where} ORDER BY display_order ASC");
        wp_send_json_success(array('services' => $services));
    }

    public function approve_quote() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        global $wpdb;
        $order_id = intval($_POST['order_id'] ?? 0);
        $note = sanitize_textarea_field($_POST['note'] ?? '');
        if (!$order_id) wp_send_json_error('Invalid order.');
        
        $order = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}sbha_orders WHERE id = %d", $order_id), ARRAY_A);
        if (!$order) wp_send_json_error('Order not found.');
        
        $wpdb->update($wpdb->prefix . 'sbha_orders', array(
            'quote_status' => 'approved',
            'quote_response_note' => $note,
            'quote_responded_at' => current_time('mysql'),
            'status' => 'confirmed'
        ), array('id' => $order_id));
        
        $this->notify($order['customer_id'], 'quote_approved', 'Quote Approved!',
            "Your quote #{$order['order_number']} has been approved!" . ($note ? " Note: {$note}" : ''));
        
        wp_send_json_success(array('message' => 'Quote approved!'));
    }

    public function decline_quote() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        global $wpdb;
        $order_id = intval($_POST['order_id'] ?? 0);
        $note = sanitize_textarea_field($_POST['note'] ?? '');
        if (!$order_id) wp_send_json_error('Invalid order.');
        
        $order = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}sbha_orders WHERE id = %d", $order_id), ARRAY_A);
        if (!$order) wp_send_json_error('Order not found.');
        
        $wpdb->update($wpdb->prefix . 'sbha_orders', array(
            'quote_status' => 'declined',
            'quote_response_note' => $note,
            'quote_responded_at' => current_time('mysql'),
            'status' => 'cancelled'
        ), array('id' => $order_id));
        
        $this->notify($order['customer_id'], 'quote_declined', 'Quote Update',
            "Your quote #{$order['order_number']} could not be approved." . ($note ? " Note: {$note}" : ''));
        
        wp_send_json_success(array('message' => 'Quote declined.'));
    }

    /**
     * AI Chat - works for logged-in AND non-logged-in users
     */
    public function ai_chat() {
        $message = sanitize_text_field($_POST['message'] ?? '');
        $context_json = stripslashes($_POST['context'] ?? '{}');
        
        if (empty($message)) {
            wp_send_json_error('Please enter a message.');
        }

        $context = json_decode($context_json, true);
        if (!is_array($context)) {
            $context = array();
        }
        
        require_once SBHA_PLUGIN_DIR . 'includes/class-sbha-smart-ai.php';
        $ai = new SBHA_Smart_AI();
        $result = $ai->process($message, $context);
        
        wp_send_json_success(array(
            'message' => $result['message'],
            'buttons' => $result['buttons'] ?? array(),
            'context' => $result['context'] ?? array(),
            'show_quote_form' => $result['show_quote_form'] ?? false,
            'quote_data' => $result['quote_data'] ?? null
        ));
    }

    /**
     * Submit quote from AI conversation - SBH prefix, creates account if needed
     */
    public function submit_ai_quote() {
        global $wpdb;

        // Verify nonce - support both GET and POST
        $nonce = $_POST['nonce'] ?? $_REQUEST['nonce'] ?? '';
        if (!wp_verify_nonce($nonce, 'sbha_nonce')) {
            wp_send_json_error('Security check failed. Please refresh and try again.');
        }

        $name = sanitize_text_field($_POST['name'] ?? '');
        $email = sanitize_email($_POST['email'] ?? '');
        $phone = sanitize_text_field($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $quote_data = json_decode(stripslashes($_POST['quote_data'] ?? '{}'), true);
        $transcript = sanitize_textarea_field($_POST['transcript'] ?? '');

        $customer_id = $this->get_customer_id();
        $account_created = false;
        
        if (!$customer_id) {
            if (empty($name) || empty($phone) || empty($email)) {
                wp_send_json_error('Please enter your name, WhatsApp number, and email.');
            }
            
            $phone = preg_replace('/[^0-9]/', '', $phone);
            
            $table = $wpdb->prefix . 'sbha_customers';
            $existing = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table WHERE cell_number = %s OR whatsapp_number = %s OR email = %s",
                $phone, $phone, $email
            ));
            
            if ($existing) {
                $customer_id = $existing->id;
            } else {
                $parts = explode(' ', $name, 2);
                $new_password = $password ?: wp_generate_password(8);
                
                $wpdb->insert($table, array(
                    'first_name' => $parts[0],
                    'last_name' => $parts[1] ?? '',
                    'email' => $email,
                    'cell_number' => $phone,
                    'whatsapp_number' => $phone,
                    'business_name' => '',
                    'password' => password_hash($new_password, PASSWORD_DEFAULT),
                    'status' => 'active'
                ));
                $customer_id = $wpdb->insert_id;
                $account_created = true;
                
                $token = $this->create_session($customer_id);
                setcookie('sbha_token', $token, time() + (30 * 24 * 60 * 60), COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);
            }
        } else {
            $c = $this->get_customer();
            if ($c) {
                $name = $name ?: $c['first_name'] . ' ' . $c['last_name'];
                $email = $email ?: $c['email'];
                $phone = $phone ?: $c['cell_number'];
            }
        }

        $count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}sbha_quotes WHERE quote_number LIKE 'QT-SBH%'") + 1;
        $quote_number = 'QT-SBH' . str_pad($count, 4, '0', STR_PAD_LEFT);

        $items_array = $quote_data['items'] ?? array();
        $total = 0;
        $items_summary = array();
        
        foreach ($items_array as $item) {
            $item_total = $item['item_total'] ?? (($item['unit_price'] ?? $item['price'] ?? 0) * ($item['quantity'] ?? 1));
            $total += $item_total;
            $items_summary[] = $item;
        }

        if (!empty($quote_data['estimate_total'])) {
            $total = $quote_data['estimate_total'];
        }

        // Handle file uploads
        $files = array();
        if (!empty($_FILES['files'])) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');
            require_once(ABSPATH . 'wp-admin/includes/image.php');

            $uploaded = $_FILES['files'];
            $file_count = is_array($uploaded['name']) ? count($uploaded['name']) : 1;

            for ($i = 0; $i < $file_count; $i++) {
                $file = is_array($uploaded['name']) ? array(
                    'name' => $uploaded['name'][$i],
                    'type' => $uploaded['type'][$i],
                    'tmp_name' => $uploaded['tmp_name'][$i],
                    'error' => $uploaded['error'][$i],
                    'size' => $uploaded['size'][$i]
                ) : $uploaded;

                if ($file['error'] === UPLOAD_ERR_OK) {
                    $_FILES['upload'] = $file;
                    $att_id = media_handle_upload('upload', 0);
                    if (!is_wp_error($att_id)) {
                        $files[] = array('id' => $att_id, 'url' => wp_get_attachment_url($att_id), 'name' => $file['name']);
                    }
                }
            }
        }

        $delivery_info = $quote_data['delivery_info'] ?? '';
        if (!$delivery_info && !empty($items_array)) {
            foreach ($items_array as $item) {
                if (!empty($item['delivery'])) {
                    $delivery_info = $item['delivery'];
                    break;
                }
            }
        }

        // Ensure extra columns exist
        $table_name = $wpdb->prefix . 'sbha_quotes';
        $cols = $wpdb->get_col("SHOW COLUMNS FROM {$table_name}", 0);
        if (!in_array('files', $cols)) {
            $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN files LONGTEXT");
        }
        if (!in_array('quote_type', $cols)) {
            $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN quote_type VARCHAR(20) DEFAULT 'quote'");
        }
        if (!in_array('payment_proof', $cols)) {
            $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN payment_proof TEXT");
        }
        
        $has_design = false;
        foreach ($items_summary as $it) {
            if (!empty($it['needs_design'])) { $has_design = true; break; }
        }

        $insert_data = array(
            'quote_number' => $quote_number,
            'customer_id' => $customer_id,
            'email' => $email,
            'phone' => $phone,
            'company' => '',
            'items' => json_encode($items_summary),
            'item_count' => count($items_summary),
            'needs_design' => $has_design ? 1 : 0,
            'delivery_needed' => !empty($delivery_info) ? 1 : 0,
            'delivery_location' => $delivery_info,
            'special_notes' => '',
            'chat_transcript' => $transcript,
            'total' => $total,
            'status' => 'pending',
            'created_at' => current_time('mysql'),
        );
        
        if (in_array('files', $cols) || true) {
            $insert_data['files'] = json_encode($files);
        }
        
        $wpdb->insert($table_name, $insert_data);

        $quote_id = $wpdb->insert_id;

        $admin_email = get_option('sbha_business_email', get_option('admin_email'));
        $email_body = "New Quote: {$quote_number}\n\n";
        $email_body .= "Customer: {$name}\nEmail: {$email}\nPhone: {$phone}\n\n";
        $email_body .= "--- ITEMS ---\n";
        foreach ($items_summary as $item) {
            $email_body .= "- {$item['product_name']} / {$item['variant_name']}\n";
            $email_body .= "  Qty: {$item['quantity']} @ R{$item['unit_price']} = R{$item['item_total']}\n";
            if (!empty($item['needs_design'])) $email_body .= "  NEEDS DESIGN (+R{$item['design_cost']})\n";
            if (!empty($item['design_brief'])) $email_body .= "  Brief: {$item['design_brief']}\n";
            if (!empty($item['purpose'])) $email_body .= "  Purpose: {$item['purpose']}\n";
            if (!empty($item['delivery'])) $email_body .= "  Delivery: {$item['delivery']}\n";
            if (!empty($item['special_notes'])) $email_body .= "  Notes: {$item['special_notes']}\n";
        }
        $email_body .= "\nESTIMATED TOTAL: R" . number_format($total, 2) . "\n";
        if (!empty($files)) {
            $email_body .= "\nFiles uploaded: " . count($files) . "\n";
            foreach ($files as $f) {
                $email_body .= "- {$f['name']}: {$f['url']}\n";
            }
        }

        wp_mail($admin_email, "New Quote: {$quote_number} - {$name}", $email_body);

        if ($email) {
            $cust_email = "Hi {$name},\n\n";
            $cust_email .= "Thank you for your quote request!\n\n";
            $cust_email .= "Quote Reference: {$quote_number}\n";
            $cust_email .= "Estimated Total: R" . number_format($total, 2) . "\n\n";
            $cust_email .= "We'll review your request and send you a formal quote shortly.\n\n";
            $cust_email .= "WhatsApp: 068 147 4232\n\n";
            $cust_email .= "Switch Graphics (Pty) Ltd\n16 Harding Street, Newcastle, 2940\nwww.switchgraphics.co.za";
            wp_mail($email, "Quote Received - {$quote_number}", $cust_email);
        }

        wp_send_json_success(array(
            'message' => 'Quote submitted!',
            'quote_number' => $quote_number,
            'quote_id' => $quote_id,
            'account_created' => $account_created
        ));
    }

    private function create_session($customer_id) {
        global $wpdb;
        $token = bin2hex(random_bytes(32));
        $wpdb->insert($wpdb->prefix . 'sbha_sessions', array(
            'customer_id' => $customer_id,
            'session_token' => $token,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'expires_at' => date('Y-m-d H:i:s', time() + 2592000)
        ));
        return $token;
    }

    private function get_customer_id() {
        $c = $this->get_customer();
        return $c ? $c['id'] : null;
    }

    private function get_customer() {
        global $wpdb;
        $token = $_COOKIE['sbha_token'] ?? ($_POST['token'] ?? '');
        if (!$token) return null;
        $sess = $wpdb->get_row($wpdb->prepare("
            SELECT * FROM {$wpdb->prefix}sbha_sessions WHERE session_token = %s AND expires_at > NOW()
        ", $token), ARRAY_A);
        if (!$sess) return null;
        return $wpdb->get_row($wpdb->prepare("
            SELECT * FROM {$wpdb->prefix}sbha_customers WHERE id = %d AND status = 'active'
        ", $sess['customer_id']), ARRAY_A);
    }

    private function notify($customer_id, $type, $title, $message, $link = '') {
        global $wpdb;
        $wpdb->insert($wpdb->prefix . 'sbha_notifications', array(
            'customer_id' => $customer_id, 'type' => $type, 'title' => $title, 'message' => $message, 'link' => $link
        ));
    }
}

new SBHA_Ajax();
