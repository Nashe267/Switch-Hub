<?php
/**
 * AJAX Handler Class
 *
 * All frontend AJAX operations
 *
 * @package SwitchBusinessHub
 */

if (!defined('ABSPATH')) {
    exit;
}

class SBHA_Ajax {

    public function __construct() {
        // Customer auth (no WordPress users)
        add_action('wp_ajax_nopriv_sbha_register', array($this, 'register'));
        add_action('wp_ajax_sbha_register', array($this, 'register'));
        add_action('wp_ajax_nopriv_sbha_login', array($this, 'login'));
        add_action('wp_ajax_sbha_login', array($this, 'login'));
        add_action('wp_ajax_sbha_logout', array($this, 'logout'));
        add_action('wp_ajax_nopriv_sbha_logout', array($this, 'logout'));
        add_action('wp_ajax_nopriv_sbha_reset_password', array($this, 'reset_password'));
        add_action('wp_ajax_sbha_reset_password', array($this, 'reset_password'));

        // Quote submission (uses submit_ai_quote which handles account creation)
        add_action('wp_ajax_sbha_submit_quote', array($this, 'submit_ai_quote'));
        add_action('wp_ajax_nopriv_sbha_submit_quote', array($this, 'submit_ai_quote'));

        // Order tracking
        add_action('wp_ajax_sbha_track_order', array($this, 'track_order'));
        add_action('wp_ajax_nopriv_sbha_track_order', array($this, 'track_order'));

        // Customer orders
        add_action('wp_ajax_sbha_get_my_orders', array($this, 'get_my_orders'));
        add_action('wp_ajax_nopriv_sbha_get_my_orders', array($this, 'get_my_orders'));

        // Documents
        add_action('wp_ajax_sbha_get_documents', array($this, 'get_documents'));
        add_action('wp_ajax_nopriv_sbha_get_documents', array($this, 'get_documents'));

        // Contact
        add_action('wp_ajax_sbha_contact', array($this, 'contact'));
        add_action('wp_ajax_nopriv_sbha_contact', array($this, 'contact'));

        // Notifications
        add_action('wp_ajax_sbha_get_notifications', array($this, 'get_notifications'));
        add_action('wp_ajax_sbha_mark_read', array($this, 'mark_notification_read'));

        // Session check
        add_action('wp_ajax_sbha_check_session', array($this, 'check_session'));
        add_action('wp_ajax_nopriv_sbha_check_session', array($this, 'check_session'));

        // Services
        add_action('wp_ajax_sbha_get_services', array($this, 'get_services'));
        add_action('wp_ajax_nopriv_sbha_get_services', array($this, 'get_services'));

        // Admin: Approve/Decline quotes
        add_action('wp_ajax_sbha_approve_quote', array($this, 'approve_quote'));
        add_action('wp_ajax_sbha_decline_quote', array($this, 'decline_quote'));

        // AI Chat
        add_action('wp_ajax_sbha_ai_chat', array($this, 'ai_chat'));
        add_action('wp_ajax_nopriv_sbha_ai_chat', array($this, 'ai_chat'));

        // Chat history persistence
        add_action('wp_ajax_sbha_save_chat_history', array($this, 'save_chat_history'));
        add_action('wp_ajax_nopriv_sbha_save_chat_history', array($this, 'save_chat_history'));
        add_action('wp_ajax_sbha_get_chat_history', array($this, 'get_chat_history'));
        add_action('wp_ajax_nopriv_sbha_get_chat_history', array($this, 'get_chat_history'));
        
        // Invoice creation (from shop)
        add_action('wp_ajax_sbha_create_invoice', array($this, 'create_invoice'));
        
        // Payment proof upload
        add_action('wp_ajax_sbha_upload_payment_proof', array($this, 'upload_payment_proof'));
    }

    /**
     * Register customer - PHONE ONLY required
     */
    public function register() {
        global $wpdb;

        // Support both old and new parameter names
        $name = sanitize_text_field($_POST['name'] ?? '');
        $first_name = sanitize_text_field($_POST['first_name'] ?? '');
        $last_name = sanitize_text_field($_POST['last_name'] ?? '');
        
        // If name provided, split it
        if ($name && !$first_name) {
            $parts = explode(' ', $name, 2);
            $first_name = $parts[0];
            $last_name = $parts[1] ?? '';
        }
        
        $phone = sanitize_text_field($_POST['phone'] ?? $_POST['cell_number'] ?? '');
        $email = sanitize_email($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // Phone is required, email is optional
        if (empty($first_name) || empty($phone) || empty($password)) {
            wp_send_json_error('Please enter name, WhatsApp number and password.');
        }

        if (strlen($password) < 4) {
            wp_send_json_error('Password must be at least 4 characters.');
        }

        // Normalize phone number
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phone) < 10) {
            wp_send_json_error('Please enter a valid phone number.');
        }

        $email = $this->resolve_customer_email($email, $phone);

        $table = $wpdb->prefix . 'sbha_customers';
        
        // Check if phone already exists
        $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE cell_number = %s OR whatsapp_number = %s", $phone, $phone));
        if ($exists) {
            wp_send_json_error('This phone number is already registered. Please login.');
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

    /**
     * Login - by PHONE NUMBER
     */
    public function login() {
        global $wpdb;

        // Support both phone and email login
        $phone = sanitize_text_field($_POST['phone'] ?? '');
        $email = sanitize_email($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ((empty($phone) && empty($email)) || empty($password)) {
            wp_send_json_error('Enter phone/email and password.');
        }

        $table = $wpdb->prefix . 'sbha_customers';
        
        // Try phone first, then email
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

    /**
     * Logout
     */
    public function logout() {
        global $wpdb;
        $token = $_COOKIE['sbha_token'] ?? '';
        if ($token) {
            $wpdb->delete($wpdb->prefix . 'sbha_sessions', array('session_token' => $token));
        }
        setcookie('sbha_token', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);
        wp_send_json_success(array('message' => 'Logged out.'));
    }

    /**
     * Reset password (just email + new password)
     */
    public function reset_password() {
        global $wpdb;

        // Support both phone and email
        $phone = sanitize_text_field($_POST['phone'] ?? '');
        $email = sanitize_email($_POST['email'] ?? '');
        $new_password = $_POST['password'] ?? $_POST['new_password'] ?? '';

        if (empty($phone) && empty($email)) {
            wp_send_json_error('Enter your phone number.');
        }

        if (empty($new_password)) {
            wp_send_json_error('Enter a new password.');
        }

        if (strlen($new_password) < 4) {
            wp_send_json_error('Password must be at least 4 characters.');
        }

        $table = $wpdb->prefix . 'sbha_customers';
        
        // Find customer by phone or email
        if ($phone) {
            $phone_clean = preg_replace('/[^0-9]/', '', $phone);
            $customer = $wpdb->get_row($wpdb->prepare(
                "SELECT id FROM $table WHERE cell_number LIKE %s OR whatsapp_number LIKE %s",
                '%' . $phone_clean . '%',
                '%' . $phone_clean . '%'
            ));
        } else {
            $customer = $wpdb->get_row($wpdb->prepare("SELECT id FROM $table WHERE email = %s", $email));
        }

        if (!$customer) {
            wp_send_json_error('Account not found with this phone number.');
        }

        $wpdb->update($table, array('password' => password_hash($new_password, PASSWORD_DEFAULT)), array('id' => $customer->id));

        wp_send_json_success(array('message' => 'Password updated! You can now login.'));
    }

    /**
     * Submit quote/order
     */
    public function submit_quote() {
        global $wpdb;

        $customer_id = $this->get_customer_id();

        // Create customer from form if not logged in
        if (!$customer_id) {
            $email = sanitize_email($_POST['customer_email'] ?? '');
            $name = sanitize_text_field($_POST['customer_name'] ?? '');
            $phone = sanitize_text_field($_POST['customer_phone'] ?? '');

            if (empty($email) || empty($name) || empty($phone)) {
                wp_send_json_error('Please fill in your details.');
            }

            $table = $wpdb->prefix . 'sbha_customers';
            $existing = $wpdb->get_row($wpdb->prepare("SELECT id FROM $table WHERE email = %s", $email));

            if ($existing) {
                $customer_id = $existing->id;
            } else {
                $parts = explode(' ', $name, 2);
                $wpdb->insert($table, array(
                    'first_name' => $parts[0],
                    'last_name' => $parts[1] ?? '',
                    'email' => $email,
                    'cell_number' => $phone,
                    'whatsapp_number' => $phone,
                    'password' => password_hash(wp_generate_password(12), PASSWORD_DEFAULT),
                    'status' => 'active'
                ));
                $customer_id = $wpdb->insert_id;
            }
        }

        $service_id = intval($_POST['service_type'] ?? 0);
        $custom_service = sanitize_text_field($_POST['custom_service'] ?? '');
        $description = sanitize_textarea_field($_POST['description'] ?? '');
        $quantity = max(1, intval($_POST['quantity'] ?? 1));
        $urgency = sanitize_text_field($_POST['urgency'] ?? 'standard');
        $title = sanitize_text_field($_POST['project_title'] ?? '');
        $client_budget = !empty($_POST['client_budget']) ? floatval($_POST['client_budget']) : null;
        $budget_notes = sanitize_textarea_field($_POST['budget_notes'] ?? '');

        if (!$service_id && empty($custom_service) && empty($title)) {
            wp_send_json_error('Please select a service or describe your request.');
        }

        $service_name = $custom_service ?: 'Custom Request';
        $unit_price = 0;

        if ($service_id) {
            $service = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}sbha_services WHERE id = %d", $service_id
            ));
            if ($service) {
                $unit_price = floatval($service->base_price);
                $service_name = $service->name;
            }
        }

        $mult = $urgency === 'express' ? 1.25 : ($urgency === 'rush' ? 1.5 : 1);
        $total = $unit_price * $quantity * $mult;

        // Generate invoice number (SBH-XXXXXX format)
        $count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}sbha_orders") + 1;
        $order_number = 'SBH-' . str_pad($count, 6, '0', STR_PAD_LEFT);

        // Handle files
        $files = array();
        if (!empty($_FILES['files'])) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');
            require_once(ABSPATH . 'wp-admin/includes/image.php');

            $uploaded = $_FILES['files'];
            $count = is_array($uploaded['name']) ? count($uploaded['name']) : 1;

            for ($i = 0; $i < $count; $i++) {
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

        $wpdb->insert($wpdb->prefix . 'sbha_orders', array(
            'order_number' => $order_number,
            'customer_id' => $customer_id,
            'service_id' => $service_id ?: null,
            'custom_service' => $custom_service,
            'title' => $title ?: $service_name,
            'description' => $description,
            'quantity' => $quantity,
            'urgency' => $urgency,
            'unit_price' => $unit_price,
            'total' => $total,
            'client_budget' => $client_budget,
            'budget_notes' => $budget_notes,
            'files' => json_encode($files),
            'status' => 'pending',
            'quote_status' => 'pending'
        ));

        $order_id = $wpdb->insert_id;

        // Notify
        $this->notify($customer_id, 'order', 'Request Submitted', "Your request #{$order_number} is received!");

        // Email admin
        $customer = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}sbha_customers WHERE id = %d", $customer_id));
        $admin_email = get_option('sbha_business_email', get_option('admin_email'));

        $budget_info = $client_budget ? "\nClient Budget: R" . number_format($client_budget, 2) : '';
        $budget_info .= $budget_notes ? "\nBudget Notes: {$budget_notes}" : '';

        wp_mail($admin_email, "New Order: {$order_number}",
            "Order: {$order_number}\nCustomer: {$customer->first_name} {$customer->last_name}\n" .
            "Email: {$customer->email}\nPhone: {$customer->cell_number}\n" .
            "Service: {$service_name}\nOur Quote: R" . number_format($total, 2) . $budget_info .
            "\n\nPlease review and approve/decline in admin panel."
        );

        wp_send_json_success(array(
            'message' => 'Request submitted!',
            'order_number' => $order_number,
            'total' => 'R' . number_format($total, 2)
        ));
    }

    /**
     * Create invoice from shop (fixed pricing)
     */
    public function create_invoice() {
        global $wpdb;
        check_ajax_referer('sbha_nonce', 'nonce');
        
        $customer_id = $this->get_customer_id();
        if (!$customer_id) {
            wp_send_json_error('Please login to place an order.');
        }
        
        $items = json_decode(stripslashes($_POST['items'] ?? '[]'), true);
        $total = floatval($_POST['total'] ?? 0);
        
        if (empty($items)) {
            wp_send_json_error('Cart is empty.');
        }
        
        // Generate invoice number: INV-SBH0001
        $count = intval($wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}sbha_quotes WHERE quote_number LIKE 'INV-SBH%'")) + 1;
        $invoice_number = 'INV-SBH' . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
        
        // Calculate total from items
        $calculated_total = 0;
        foreach ($items as $idx => $item) {
            $qty = intval($item['quantity'] ?? $item['qty'] ?? 1);
            if ($qty < 1) {
                $qty = 1;
            }
            $price = floatval($item['price'] ?? 0);
            $items[$idx]['quantity'] = $qty;
            $items[$idx]['qty'] = $qty;
            $items[$idx]['price'] = $price;
            $calculated_total += $price * $qty;
        }

        // Optional customer artwork upload from shop checkout.
        $uploaded_files = $this->upload_quote_files('order_files');
        $special_notes = '';
        if (!empty($uploaded_files)) {
            $lines = array("Uploaded artwork files:");
            foreach ($uploaded_files as $f) {
                $lines[] = '- ' . ($f['name'] ?? 'file') . ': ' . ($f['url'] ?? '');
            }
            $special_notes = implode("\n", $lines);
        }
        
        // Customer profile snapshot
        $customer = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}sbha_customers WHERE id = %d",
            $customer_id
        ));

        // Insert invoice record into quotes table
        $wpdb->insert(
            $wpdb->prefix . 'sbha_quotes',
            array(
                'quote_number' => $invoice_number,
                'customer_id' => $customer_id,
                'email' => $customer->email ?? '',
                'phone' => $customer->cell_number ?? '',
                'items' => json_encode($items),
                'item_count' => count($items),
                'total' => $calculated_total,
                'status' => 'pending',
                'special_notes' => $special_notes,
                'created_at' => current_time('mysql')
            ),
            array('%s', '%d', '%s', '%s', '%s', '%d', '%f', '%s', '%s', '%s')
        );
        
        $order_id = $wpdb->insert_id;

        wp_send_json_success(array(
            'invoice_number' => $invoice_number,
            'order_id' => $order_id,
            'total' => $calculated_total,
            'message' => 'Invoice created! Pay via EFT and upload proof.'
        ));
    }

    /**
     * Upload payment proof
     */
    public function upload_payment_proof() {
        global $wpdb;
        check_ajax_referer('sbha_nonce', 'nonce');
        
        $customer_id = $this->get_customer_id();
        if (!$customer_id) {
            wp_send_json_error('Please login first.');
        }
        
        $order_id = intval($_POST['order_id'] ?? 0);
        $invoice_number = sanitize_text_field($_POST['invoice_number'] ?? '');
        
        if (!$order_id) {
            // Try to find by invoice number
            if ($invoice_number) {
                $order = $wpdb->get_row($wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}sbha_quotes WHERE quote_number = %s AND customer_id = %d",
                    $invoice_number, $customer_id
                ));
                if ($order) $order_id = $order->id;
            }
        }
        
        if (!$order_id) {
            wp_send_json_error('Invalid order.');
        }
        
        // Check order belongs to customer
        $order = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}sbha_quotes WHERE id = %d AND customer_id = %d",
            $order_id, $customer_id
        ));
        
        if (!$order) {
            wp_send_json_error('Order not found.');
        }
        
        // Handle file upload
        if (empty($_FILES['payment_proof'])) {
            wp_send_json_error('No file uploaded.');
        }
        
        $file = $_FILES['payment_proof'];
        
        // Check for errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors = array(
                UPLOAD_ERR_INI_SIZE => 'File too large (server limit)',
                UPLOAD_ERR_FORM_SIZE => 'File too large',
                UPLOAD_ERR_PARTIAL => 'File only partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Server error (no temp dir)',
                UPLOAD_ERR_CANT_WRITE => 'Server error (cannot write)',
            );
            wp_send_json_error($errors[$file['error']] ?? 'Upload error');
        }
        
        // Validate file type - allow images AND PDF
        $allowed = array('image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf');
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mime, $allowed)) {
            wp_send_json_error('Invalid file type. Please upload an image or PDF.');
        }
        
        // Upload using WordPress
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        
        $upload = wp_handle_upload($file, array('test_form' => false));
        
        if (isset($upload['error'])) {
            wp_send_json_error('Upload failed: ' . $upload['error']);
        }
        
        // Ensure payment_proof column exists
        $column_exists = $wpdb->get_var("SHOW COLUMNS FROM {$wpdb->prefix}sbha_quotes LIKE 'payment_proof'");
        if (!$column_exists) {
            $wpdb->query("ALTER TABLE {$wpdb->prefix}sbha_quotes ADD COLUMN payment_proof TEXT");
        }
        
        // Update order with payment proof URL
        $updated = $wpdb->update(
            $wpdb->prefix . 'sbha_quotes',
            array('payment_proof' => $upload['url']),
            array('id' => $order_id)
        );
        
        if ($updated === false) {
            wp_send_json_error('Failed to save. Please try again.');
        }
        
        wp_send_json_success(array(
            'message' => 'Payment proof uploaded! Awaiting verification.',
            'proof_url' => $upload['url']
        ));
    }

    /**
     * Track order - INVOICE NUMBER ONLY
     */
    public function track_order() {
        global $wpdb;
        check_ajax_referer('sbha_nonce', 'nonce');

        $invoice = sanitize_text_field($_POST['invoice'] ?? '');
        if (empty($invoice)) {
            wp_send_json_error('Please enter your invoice number (example: INV-SBH0001).');
        }

        $raw = strtoupper(str_replace(array(' ', '_'), '', $invoice));
        $raw = str_replace('--', '-', $raw);

        $sequence = '';
        if (preg_match('/^INV-?SBH-?(\d+)$/', $raw, $m)) {
            $sequence = $m[1];
        } elseif (preg_match('/^SBH-?(\d+)$/', $raw, $m)) {
            $sequence = $m[1];
        } elseif (preg_match('/^(\d+)$/', $raw, $m)) {
            $sequence = $m[1];
        }

        $candidates = array();
        if ($sequence !== '') {
            $seq_int = intval($sequence);
            $candidates[] = 'INV-SBH' . str_pad((string) $seq_int, 4, '0', STR_PAD_LEFT);
            $candidates[] = 'INV-SBH' . str_pad((string) $seq_int, 2, '0', STR_PAD_LEFT); // older installs
            $candidates[] = 'SBH-' . str_pad((string) $seq_int, 6, '0', STR_PAD_LEFT); // legacy order numbers
        }
        $candidates[] = $raw;
        $candidates = array_values(array_unique(array_filter($candidates)));

        // 1) Check quote/invoice records table first (shop invoices + AI quotes)
        $quote = null;
        foreach ($candidates as $candidate) {
            $quote = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}sbha_quotes WHERE quote_number = %s LIMIT 1",
                $candidate
            ), ARRAY_A);
            if ($quote) {
                break;
            }
        }

        if (!$quote && $sequence !== '') {
            $needle = '%SBH' . ltrim($sequence, '0') . '%';
            $quote = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}sbha_quotes WHERE quote_number LIKE %s ORDER BY created_at DESC LIMIT 1",
                $needle
            ), ARRAY_A);
        }

        if ($quote) {
            $has_proof = !empty($quote['payment_proof']);
            $status_label = $quote['status'];
            if ($quote['status'] === 'pending' && $has_proof) {
                $status_label = 'verifying';
            }

            wp_send_json_success(array(
                'quote_number' => $quote['quote_number'],
                'invoice_number' => $quote['quote_number'],
                'status' => $quote['status'],
                'status_label' => $status_label,
                'date' => date('d M Y', strtotime($quote['created_at'])),
                'description' => !empty($quote['special_notes']) ? $quote['special_notes'] : 'Quote / invoice tracking',
                'total' => 'R' . number_format(floatval($quote['total']), 2),
                'payment_proof' => $quote['payment_proof'] ?? ''
            ));
        }

        // 2) Backward-compat fallback to old orders table
        $order = null;
        foreach ($candidates as $candidate) {
            $order = $wpdb->get_row($wpdb->prepare("
                SELECT o.*, c.first_name, c.last_name, s.name as service_name
                FROM {$wpdb->prefix}sbha_orders o
                LEFT JOIN {$wpdb->prefix}sbha_customers c ON o.customer_id = c.id
                LEFT JOIN {$wpdb->prefix}sbha_services s ON o.service_id = s.id
                WHERE o.order_number = %s
                LIMIT 1
            ", $candidate), ARRAY_A);
            if ($order) {
                break;
            }
        }

        if (!$order && $sequence !== '') {
            $order = $wpdb->get_row($wpdb->prepare("
                SELECT o.*, c.first_name, c.last_name, s.name as service_name
                FROM {$wpdb->prefix}sbha_orders o
                LEFT JOIN {$wpdb->prefix}sbha_customers c ON o.customer_id = c.id
                LEFT JOIN {$wpdb->prefix}sbha_services s ON o.service_id = s.id
                WHERE o.order_number LIKE %s
                ORDER BY o.created_at DESC
                LIMIT 1
            ", '%SBH%' . intval($sequence) . '%'), ARRAY_A);
        }

        if (!$order) {
            wp_send_json_error('Invoice not found. Please check the SBH invoice number.');
        }

        wp_send_json_success(array(
            'quote_number' => $order['order_number'],
            'invoice_number' => $order['order_number'],
            'status' => $order['status'],
            'date' => date('d M Y', strtotime($order['created_at'])),
            'description' => $order['service_name'] ?: $order['custom_service'] ?: $order['title'],
            'total' => 'R' . number_format(floatval($order['total']), 2)
        ));
    }

    /**
     * Get customer's orders
     */
    public function get_my_orders() {
        global $wpdb;

        $customer_id = $this->get_customer_id();
        if (!$customer_id) {
            wp_send_json_success(array('orders' => array()));
        }

        $orders = $wpdb->get_results($wpdb->prepare("
            SELECT o.*, s.name as service_name
            FROM {$wpdb->prefix}sbha_orders o
            LEFT JOIN {$wpdb->prefix}sbha_services s ON o.service_id = s.id
            WHERE o.customer_id = %d ORDER BY o.created_at DESC LIMIT 20
        ", $customer_id), ARRAY_A);

        $result = array();
        $biz = get_option('sbha_business_name', 'Switch Hub');

        foreach ($orders as $o) {
            $result[] = array(
                'order_number' => $o['order_number'],
                'service_name' => $o['service_name'] ?: $o['custom_service'] ?: $o['title'],
                'status' => $o['status'],
                'status_label' => ucfirst(str_replace('_', ' ', $o['status'])),
                'total' => 'R' . number_format($o['total'], 2),
                'created_date' => date('d M Y', strtotime($o['created_at'])),
                'admin_response' => $o['admin_response'],
                'has_new_response' => $o['admin_response'] && !$o['customer_viewed_response'],
                'invoice_url' => $o['invoice_pdf_url'],
                'business_name' => $biz
            );
        }

        wp_send_json_success(array('orders' => $result));
    }

    /**
     * Get documents
     */
    public function get_documents() {
        global $wpdb;

        $email = sanitize_email($_POST['email'] ?? '');
        $customer_id = $this->get_customer_id();

        if (!$customer_id && $email) {
            $c = $wpdb->get_row($wpdb->prepare("SELECT id FROM {$wpdb->prefix}sbha_customers WHERE email = %s", $email));
            $customer_id = $c ? $c->id : null;
        }

        if (!$customer_id) {
            wp_send_json_success(array('documents' => array()));
        }

        $quotes = $wpdb->get_results($wpdb->prepare("
            SELECT q.*, o.title as service FROM {$wpdb->prefix}sbha_quotes q
            JOIN {$wpdb->prefix}sbha_orders o ON q.order_id = o.id
            WHERE q.customer_id = %d ORDER BY q.created_at DESC
        ", $customer_id), ARRAY_A);

        $invoices = $wpdb->get_results($wpdb->prepare("
            SELECT i.*, o.title as service FROM {$wpdb->prefix}sbha_invoices i
            JOIN {$wpdb->prefix}sbha_orders o ON i.order_id = o.id
            WHERE i.customer_id = %d ORDER BY i.created_at DESC
        ", $customer_id), ARRAY_A);

        $docs = array();
        foreach ($quotes as $q) {
            $docs[] = array('type' => 'Quote', 'number' => $q['quote_number'], 'service' => $q['service'],
                'total' => 'R' . number_format($q['total'], 2), 'date' => date('d M Y', strtotime($q['created_at'])),
                'status' => $q['status'], 'pdf_url' => $q['pdf_url']);
        }
        foreach ($invoices as $i) {
            $docs[] = array('type' => 'Invoice', 'number' => $i['invoice_number'], 'service' => $i['service'],
                'total' => 'R' . number_format($i['total'], 2), 'date' => date('d M Y', strtotime($i['created_at'])),
                'status' => $i['status'], 'pdf_url' => $i['pdf_url']);
        }

        wp_send_json_success(array('documents' => $docs));
    }

    /**
     * Contact
     */
    public function contact() {
        global $wpdb;

        $name = sanitize_text_field($_POST['name'] ?? '');
        $email = sanitize_email($_POST['email'] ?? '');
        $phone = sanitize_text_field($_POST['phone'] ?? '');
        $message = sanitize_textarea_field($_POST['message'] ?? '');

        if (empty($name) || empty($email) || empty($message)) {
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

    /**
     * Get notifications
     */
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

    /**
     * Mark notification read
     */
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

    /**
     * Check session
     */
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

    /**
     * Get services
     */
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

    /**
     * Persist AI chat history (customer/session scoped).
     */
    public function save_chat_history() {
        check_ajax_referer('sbha_nonce', 'nonce');

        $history_raw = stripslashes($_POST['history'] ?? '[]');
        $history = json_decode($history_raw, true);
        if (!is_array($history)) {
            $history = array();
        }

        // Keep only latest 120 entries and normalize shape.
        $history = array_slice($history, -120);
        $normalized = array();
        foreach ($history as $entry) {
            if (!is_array($entry)) {
                continue;
            }
            $normalized[] = array(
                'role' => sanitize_text_field($entry['role'] ?? ''),
                'text' => sanitize_textarea_field($entry['text'] ?? ''),
                'ts' => sanitize_text_field($entry['ts'] ?? current_time('mysql'))
            );
        }

        $customer_id = $this->get_customer_id();
        $session_id = $this->get_public_session_id();

        if (!$customer_id && !$session_id) {
            wp_send_json_success(array('saved' => false));
        }

        $key = $this->build_chat_history_key($customer_id, $session_id);
        set_transient($key, $normalized, 30 * DAY_IN_SECONDS);

        wp_send_json_success(array('saved' => true));
    }

    /**
     * Return stored chat history.
     */
    public function get_chat_history() {
        check_ajax_referer('sbha_nonce', 'nonce');

        $customer_id = $this->get_customer_id();
        $session_id = $this->get_public_session_id();
        $history = array();

        if ($customer_id) {
            $history = get_transient($this->build_chat_history_key($customer_id, ''));
        }

        if (empty($history) && $session_id) {
            $history = get_transient($this->build_chat_history_key(0, $session_id));
        }

        if (!is_array($history)) {
            $history = array();
        }

        wp_send_json_success(array('history' => $history));
    }

    // Helpers
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

    private function build_chat_history_key($customer_id, $session_id) {
        if ($customer_id) {
            return 'sbha_chat_history_customer_' . intval($customer_id);
        }
        return 'sbha_chat_history_session_' . md5((string) $session_id);
    }

    private function get_public_session_id() {
        $session = sanitize_text_field($_COOKIE['sbha_session'] ?? $_POST['session_id'] ?? '');
        return $session;
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

    /**
     * Email is optional in the portal, but table has unique email.
     * Generate a deterministic placeholder from phone when email is blank.
     */
    private function resolve_customer_email($email, $phone) {
        $email = sanitize_email($email);
        if (!empty($email)) {
            return $email;
        }

        $digits = preg_replace('/[^0-9]/', '', (string) $phone);
        if (empty($digits)) {
            $digits = wp_generate_password(8, false, false);
        }

        return 'customer+' . $digits . '@switchhub.local';
    }

    /**
     * Upload files attached from quote form.
     */
    private function upload_quote_files($field_name = 'quote_files') {
        $files = array();

        if (empty($_FILES[$field_name])) {
            return $files;
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $uploaded = $_FILES[$field_name];
        $count = is_array($uploaded['name']) ? count($uploaded['name']) : 1;

        for ($i = 0; $i < $count; $i++) {
            $file = is_array($uploaded['name']) ? array(
                'name' => $uploaded['name'][$i] ?? '',
                'type' => $uploaded['type'][$i] ?? '',
                'tmp_name' => $uploaded['tmp_name'][$i] ?? '',
                'error' => $uploaded['error'][$i] ?? UPLOAD_ERR_NO_FILE,
                'size' => $uploaded['size'][$i] ?? 0
            ) : $uploaded;

            if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                continue;
            }

            $_FILES['sbha_quote_upload'] = $file;
            $attachment_id = media_handle_upload('sbha_quote_upload', 0);

            if (!is_wp_error($attachment_id)) {
                $files[] = array(
                    'id' => $attachment_id,
                    'name' => sanitize_file_name($file['name']),
                    'url' => wp_get_attachment_url($attachment_id)
                );
            }
        }

        return $files;
    }

    private function notify($customer_id, $type, $title, $message, $link = '') {
        global $wpdb;
        $wpdb->insert($wpdb->prefix . 'sbha_notifications', array(
            'customer_id' => $customer_id, 'type' => $type, 'title' => $title, 'message' => $message, 'link' => $link
        ));
    }

    /**
     * Approve quote (Admin only)
     */
    public function approve_quote() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        global $wpdb;
        $order_id = intval($_POST['order_id'] ?? 0);
        $note = sanitize_textarea_field($_POST['note'] ?? '');

        if (!$order_id) {
            wp_send_json_error('Invalid order.');
        }

        $order = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}sbha_orders WHERE id = %d", $order_id), ARRAY_A);
        if (!$order) {
            wp_send_json_error('Order not found.');
        }

        $wpdb->update($wpdb->prefix . 'sbha_orders', array(
            'quote_status' => 'approved',
            'quote_response_note' => $note,
            'quote_responded_at' => current_time('mysql'),
            'status' => 'confirmed'
        ), array('id' => $order_id));

        // Notify customer
        $this->notify(
            $order['customer_id'],
            'quote_approved',
            'Quote Approved!',
            "Great news! Your quote #{$order['order_number']} has been approved." . ($note ? " Note: {$note}" : '')
        );

        // Email customer
        $customer = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}sbha_customers WHERE id = %d", $order['customer_id']));
        if ($customer) {
            $biz = get_option('sbha_business_name', 'Switch Hub');
            wp_mail($customer->email, "Quote Approved - {$order['order_number']}",
                "Hi {$customer->first_name},\n\nGreat news! Your quote #{$order['order_number']} has been APPROVED.\n\n" .
                ($note ? "Note from {$biz}: {$note}\n\n" : "") .
                "We will begin working on your order shortly.\n\nThank you!\n{$biz}"
            );
        }

        wp_send_json_success(array('message' => 'Quote approved!'));
    }

    /**
     * Decline quote (Admin only)
     */
    public function decline_quote() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        global $wpdb;
        $order_id = intval($_POST['order_id'] ?? 0);
        $note = sanitize_textarea_field($_POST['note'] ?? '');

        if (!$order_id) {
            wp_send_json_error('Invalid order.');
        }

        $order = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}sbha_orders WHERE id = %d", $order_id), ARRAY_A);
        if (!$order) {
            wp_send_json_error('Order not found.');
        }

        $wpdb->update($wpdb->prefix . 'sbha_orders', array(
            'quote_status' => 'declined',
            'quote_response_note' => $note,
            'quote_responded_at' => current_time('mysql'),
            'status' => 'cancelled'
        ), array('id' => $order_id));

        // Notify customer
        $this->notify(
            $order['customer_id'],
            'quote_declined',
            'Quote Update',
            "Your quote #{$order['order_number']} could not be approved at this time." . ($note ? " Note: {$note}" : '')
        );

        // Email customer
        $customer = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}sbha_customers WHERE id = %d", $order['customer_id']));
        if ($customer) {
            $biz = get_option('sbha_business_name', 'Switch Hub');
            wp_mail($customer->email, "Quote Update - {$order['order_number']}",
                "Hi {$customer->first_name},\n\nWe've reviewed your quote #{$order['order_number']}.\n\n" .
                "Unfortunately, we're unable to proceed with this request at this time.\n\n" .
                ($note ? "Note: {$note}\n\n" : "") .
                "Please feel free to submit a new request or contact us to discuss alternatives.\n\nThank you!\n{$biz}"
            );
        }

        wp_send_json_success(array('message' => 'Quote declined.'));
    }

    /**
     * AI Chat - Smart Print & Graphics Industry Assistant
     */
    public function ai_chat() {
        check_ajax_referer('sbha_nonce', 'nonce');

        $message = sanitize_text_field($_POST['message'] ?? '');
        $context_json = stripslashes($_POST['context'] ?? '{}');
        
        if (empty($message)) {
            wp_send_json_error('Please enter a message.');
        }

        // Parse context
        $context = json_decode($context_json, true);
        if (!is_array($context)) {
            $context = array();
        }
        
        // Load Smart AI
        require_once SBHA_PLUGIN_DIR . 'includes/class-sbha-smart-ai.php';
        $ai = new SBHA_Smart_AI();
        
        // Process message
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
     * Submit quote from AI conversation
     * Creates account if needed and logs user in
     */
    public function submit_ai_quote() {
        global $wpdb;

        check_ajax_referer('sbha_nonce', 'nonce');

        $name = sanitize_text_field($_POST['name'] ?? '');
        $email_input = sanitize_email($_POST['email'] ?? '');
        $phone = sanitize_text_field($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $quote_data = json_decode(stripslashes($_POST['quote_data'] ?? '{}'), true);
        $transcript_raw = $_POST['transcript'] ?? '';
        $transcript = is_string($transcript_raw) ? sanitize_textarea_field($transcript_raw) : wp_json_encode($transcript_raw);

        if (!is_array($quote_data)) {
            $quote_data = array();
        }

        // Phone is required
        if (empty($name) || empty($phone)) {
            wp_send_json_error('Please enter your name and WhatsApp number.');
        }

        // Normalize phone
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (strlen($phone) < 9) {
            wp_send_json_error('Please enter a valid WhatsApp number.');
        }

        $email = $this->resolve_customer_email($email_input, $phone);

        // Generate quote number
        $count = intval($wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}sbha_quotes WHERE quote_number LIKE 'QT-SBH%'")) + 1;
        $quote_number = 'QT-SBH' . str_pad((string) $count, 4, '0', STR_PAD_LEFT);

        // Check if customer exists
        $table = $wpdb->prefix . 'sbha_customers';
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE cell_number = %s OR whatsapp_number = %s",
            $phone, $phone
        ));
        
        $account_created = false;
        
        if ($existing) {
            $customer_id = $existing->id;

            // Update customer profile with latest provided info.
            $parts = explode(' ', $name, 2);
            $update_data = array(
                'first_name' => $parts[0] ?? $existing->first_name,
                'last_name' => $parts[1] ?? $existing->last_name,
                'cell_number' => $phone,
                'whatsapp_number' => $phone
            );
            if (!empty($email_input)) {
                $update_data['email'] = $email_input;
            }
            $wpdb->update($table, $update_data, array('id' => $customer_id));
        } else {
            // Create new customer
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
        }

        // Ensure customer session cookie exists for quote dashboard visibility.
        $token = $this->create_session($customer_id);
        setcookie('sbha_token', $token, time() + (30 * 24 * 60 * 60), COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);

        // Fallback transcript from saved history if frontend did not send one.
        if (empty($transcript)) {
            $saved_history = get_transient($this->build_chat_history_key($customer_id, ''));
            if (is_array($saved_history) && !empty($saved_history)) {
                $lines = array();
                foreach ($saved_history as $entry) {
                    $role = strtoupper($entry['role'] ?? '');
                    $text = trim($entry['text'] ?? '');
                    if ($text !== '') {
                        $lines[] = "{$role}: {$text}";
                    }
                }
                $transcript = implode("\n", $lines);
            }
        }

        // If still empty, build a compact transcript from provided quote notes.
        if (empty($transcript)) {
            $transcript = "AI Quote Session\n";
            $transcript .= "Customer Request: " . ($quote_data['special_notes'] ?? $quote_data['design_details'] ?? 'N/A');
        }

        // Build items summary from quote_data or context
        $items_summary = array();
        $total = 0.0;
        $needs_design = 0;
        $design_note_parts = array();
        $delivery_note_parts = array();
        
        // Try to get items from quote_data
        $items_array = $quote_data['items'] ?? array();
        
        if (!empty($items_array)) {
            foreach ($items_array as $item) {
                $unit_price = floatval($item['unit_price'] ?? $item['price'] ?? 0);
                $qty = intval($item['quantity'] ?? $item['qty'] ?? 1);
                $qty = max(1, $qty);
                $design_fee = floatval($item['design_fee'] ?? 0);
                $delivery_fee = floatval($item['delivery_fee'] ?? 0);
                $item_total = ($unit_price * $qty) + $design_fee + $delivery_fee;
                $total += $item_total;
                
                $item_needs_design = !empty($item['needs_design']) ? 1 : 0;
                if ($item_needs_design) {
                    $needs_design = 1;
                    $design_note_parts[] = ($item['product_name'] ?? $item['product'] ?? 'Item') . ': Design service requested';
                }
                if (!empty($item['delivery_location'])) {
                    $delivery_note_parts[] = sanitize_text_field($item['delivery_location']);
                }

                $items_summary[] = array(
                    'product' => $item['product_name'] ?? $item['product'] ?? '',
                    'variant' => $item['variant_name'] ?? '',
                    'sku' => $item['variant_sku'] ?? $item['sku'] ?? '',
                    'size' => $item['size'] ?? '',
                    'quantity' => $qty,
                    'unit_price' => $unit_price,
                    'design_fee' => $design_fee,
                    'delivery_fee' => $delivery_fee,
                    'subtotal' => $item_total,
                    'needs_design' => $item_needs_design,
                    'design_details' => $item['design_details'] ?? ''
                );
            }
        }

        // Use AI estimate total if provided (already includes extras in v2.1 flow)
        if (!empty($quote_data['estimate_total']) && floatval($quote_data['estimate_total']) > 0) {
            $total = floatval($quote_data['estimate_total']);
        }

        // If we have product info but no items, create one
        if (empty($items_summary) && !empty($quote_data['product_name'])) {
            $qty = max(1, intval($quote_data['quantity'] ?? 1));
            $unit = floatval($quote_data['unit_price'] ?? 0);
            $items_summary[] = array(
                'product' => $quote_data['product_name'],
                'quantity' => $qty,
                'unit_price' => $unit,
                'needs_design' => $quote_data['needs_design'] ?? false
            );
            $total = $unit * $qty;
        }

        if (empty($items_summary)) {
            $items_summary[] = array(
                'product' => 'Custom Quote Request',
                'variant' => '',
                'sku' => 'CUSTOM',
                'quantity' => 1,
                'unit_price' => $total,
                'subtotal' => $total,
                'needs_design' => !empty($quote_data['needs_design']) ? 1 : 0
            );
        }

        // Handle optional file uploads from quote modal.
        $uploaded_files = $this->upload_quote_files('quote_files');
        $file_note = '';
        if (!empty($uploaded_files)) {
            $file_lines = array();
            foreach ($uploaded_files as $f) {
                $file_lines[] = ($f['name'] ?? 'file') . ': ' . ($f['url'] ?? '');
            }
            $file_note = "Uploaded files:\n" . implode("\n", $file_lines);
        }

        $special_notes = sanitize_textarea_field($quote_data['special_notes'] ?? '');
        if (!empty($file_note)) {
            $special_notes = trim($special_notes . "\n\n" . $file_note);
        }
        if (!empty($delivery_note_parts) && empty($quote_data['delivery_location'])) {
            $quote_data['delivery_location'] = implode(' | ', array_unique($delivery_note_parts));
        }

        // Insert quote
        $wpdb->insert($wpdb->prefix . 'sbha_quotes', array(
            'quote_number' => $quote_number,
            'customer_id' => $customer_id,
            'email' => $email_input,
            'phone' => $phone,
            'company' => '',
            'items' => json_encode($items_summary),
            'item_count' => count($items_summary),
            'needs_design' => !empty($quote_data['needs_design']) || $needs_design ? 1 : 0,
            'design_details' => !empty($quote_data['design_details']) ? $quote_data['design_details'] : implode(' | ', $design_note_parts),
            'event_type' => $quote_data['event_type'] ?? '',
            'event_date' => $quote_data['event_date'] ?? '',
            'delivery_needed' => (!empty($quote_data['delivery_needed']) || !empty($delivery_note_parts)) ? 1 : 0,
            'delivery_location' => $quote_data['delivery_location'] ?? '',
            'special_notes' => $special_notes,
            'chat_transcript' => $transcript,
            'total' => $total,
            'status' => 'pending',
            'created_at' => current_time('mysql')
        ));

        $quote_id = $wpdb->insert_id;

        // Email admin
        $admin_email = get_option('sbha_business_email', get_option('admin_email'));
        
        $email_body = "New Quote Request: {$quote_number}\n\n";
        $email_body .= "Customer: {$name}\n";
        $email_body .= "Email: " . (!empty($email_input) ? $email_input : '-') . "\n";
        $email_body .= "Phone: {$phone}\n";
        $email_body .= "\n--- ITEMS ---\n";
        
        foreach ($items_summary as $item) {
            $email_body .= "\n{$item['product']} - {$item['variant']}\n";
            $email_body .= "  SKU: {$item['sku']}\n";
            $email_body .= "  Qty: {$item['quantity']} @ R{$item['unit_price']} = R{$item['subtotal']}\n";
            if ($item['needs_design']) {
                $email_body .= "  NEEDS DESIGN\n";
            }
        }
        
        $email_body .= "\n--- DETAILS ---\n";
        if (!empty($quote_data['event_type'])) $email_body .= "Event: {$quote_data['event_type']}\n";
        if (!empty($quote_data['event_date'])) $email_body .= "Needed by: {$quote_data['event_date']}\n";
        if (!empty($quote_data['delivery_location'])) $email_body .= "Delivery: {$quote_data['delivery_location']}\n";
        if (!empty($special_notes)) $email_body .= "Notes: {$special_notes}\n";
        
        $email_body .= "\n💰 ESTIMATED TOTAL: R" . number_format($total, 2) . "\n";
        $email_body .= "\n--- CHAT TRANSCRIPT ---\n" . $transcript;

        wp_mail($admin_email, "New Quote: {$quote_number} - {$name}", $email_body);

        // Email customer
        $customer_email = "Hi {$name},\n\n";
        $customer_email .= "Thank you for your quote request!\n\n";
        $customer_email .= "Quote Reference: {$quote_number}\n";
        $customer_email .= "Estimated Total: R" . number_format($total, 2) . "\n\n";
        $customer_email .= "We'll review your request and send you a formal quote shortly.\n\n";
        $customer_email .= "If you have any questions, feel free to WhatsApp us at " . get_option('sbha_whatsapp', '068 147 4232') . "\n\n";
        $customer_email .= "Thank you for choosing Switch Graphics!\n\n";
        $customer_email .= "---\n";
        $customer_email .= "Switch Graphics (Pty) Ltd\n";
        $customer_email .= "16 Harding Street, Newcastle, 2940\n";
        $customer_email .= "Tel: 068 147 4232\n";
        $customer_email .= "www.switchgraphics.co.za";

        if (!empty($email_input)) {
            wp_mail($email_input, "Quote Request Received - {$quote_number}", $customer_email);
        }

        wp_send_json_success(array(
            'message' => 'Quote submitted successfully!',
            'quote_number' => $quote_number,
            'quote_id' => $quote_id,
            'account_created' => $account_created,
            'token' => $token
        ));
    }
}

new SBHA_Ajax();
