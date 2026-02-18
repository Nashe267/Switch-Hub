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
        add_action('wp_ajax_sbha_download_document', array($this, 'download_document'));
        add_action('wp_ajax_nopriv_sbha_download_document', array($this, 'download_document'));

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
        add_action('wp_ajax_nopriv_sbha_create_invoice', array($this, 'create_invoice'));
        
        // Payment proof upload
        add_action('wp_ajax_sbha_upload_payment_proof', array($this, 'upload_payment_proof'));

        // Frontend super-admin controls
        add_action('wp_ajax_sbha_super_admin_update_quote_status', array($this, 'super_admin_update_quote_status'));
        add_action('wp_ajax_nopriv_sbha_super_admin_update_quote_status', array($this, 'super_admin_update_quote_status'));
        add_action('wp_ajax_sbha_super_admin_update_product_variation', array($this, 'super_admin_update_product_variation'));
        add_action('wp_ajax_nopriv_sbha_super_admin_update_product_variation', array($this, 'super_admin_update_product_variation'));
        add_action('wp_ajax_sbha_super_admin_update_document', array($this, 'super_admin_update_document'));
        add_action('wp_ajax_nopriv_sbha_super_admin_update_document', array($this, 'super_admin_update_document'));
        add_action('wp_ajax_sbha_super_admin_save_branding', array($this, 'super_admin_save_branding'));
        add_action('wp_ajax_nopriv_sbha_super_admin_save_branding', array($this, 'super_admin_save_branding'));
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
        $this->verify_public_nonce();
        
        $customer_id = $this->get_customer_id();
        if (!$customer_id) {
            $guest_name = sanitize_text_field($_POST['guest_name'] ?? '');
            $guest_phone = sanitize_text_field($_POST['guest_phone'] ?? '');
            $guest_email = sanitize_email($_POST['guest_email'] ?? '');

            if (empty($guest_name) || empty($guest_phone)) {
                wp_send_json_error('Please provide your name and WhatsApp number.');
            }

            $customer_id = $this->resolve_or_create_guest_customer($guest_name, $guest_phone, $guest_email);
            if (!$customer_id) {
                wp_send_json_error('Could not create guest checkout profile. Please try again.');
            }
        }
        
        $items = json_decode(stripslashes($_POST['items'] ?? '[]'), true);
        
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
        if (!$customer) {
            $customer = (object) array(
                'email' => '',
                'cell_number' => '',
                'first_name' => 'Client',
                'last_name' => ''
            );
        }

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
                'pdf_url' => '',
                'created_at' => current_time('mysql')
            ),
            array('%s', '%d', '%s', '%s', '%s', '%d', '%f', '%s', '%s', '%s', '%s')
        );
        
        $order_id = $wpdb->insert_id;
        $document_url = '';
        if ($order_id) {
            $document_url = $this->generate_document_file($order_id);
        }

        // Send admin + customer email notifications.
        $admin_email = get_option('sbha_business_email', get_option('admin_email'));
        $customer_name = trim(($customer->first_name ?? '') . ' ' . ($customer->last_name ?? ''));
        if ($customer_name === '') {
            $customer_name = 'Client';
        }

        $lines = array();
        foreach ($items as $item) {
            $name = sanitize_text_field($item['name'] ?? $item['product'] ?? 'Item');
            $variant = sanitize_text_field($item['variation'] ?? $item['variant'] ?? '');
            $qty = intval($item['quantity'] ?? 1);
            $price = floatval($item['price'] ?? 0);
            $line_total = $qty * $price;
            $lines[] = "{$name}" . ($variant ? " ({$variant})" : '') . " x{$qty} = R" . number_format($line_total, 2);
        }

        $admin_body = "New invoice created: {$invoice_number}\n\n";
        $admin_body .= "Customer: {$customer_name}\n";
        $admin_body .= "Email: " . ($customer->email ?? '-') . "\n";
        $admin_body .= "Phone: " . ($customer->cell_number ?? '-') . "\n\n";
        $admin_body .= "Items:\n- " . implode("\n- ", $lines) . "\n\n";
        $admin_body .= "Total: R" . number_format($calculated_total, 2) . "\n";
        if ($document_url) {
            $admin_body .= "Document: {$document_url}\n";
        }
        $admin_sent = $this->send_portal_email($admin_email, "New Invoice {$invoice_number}", $admin_body, $customer->email ?? '');

        $customer_sent = false;
        $customer_mail = sanitize_email($customer->email ?? '');
        if (strpos((string) $customer_mail, '@switchhub.local') !== false || strpos((string) $customer_mail, '@switchgraphics.local') !== false) {
            $customer_mail = '';
        }
        if (!empty($customer_mail)) {
            $customer_body = "Hi {$customer_name},\n\n";
            $customer_body .= "Your invoice has been created.\n";
            $customer_body .= "Invoice: {$invoice_number}\n";
            $customer_body .= "Total: R" . number_format($calculated_total, 2) . "\n\n";
            $customer_body .= "Banking Details:\n";
            $customer_body .= "Bank: " . get_option('sbha_bank_name', 'FNB/RMB') . "\n";
            $customer_body .= "Account Name: " . get_option('sbha_bank_account_name', 'Switch Graphics (Pty) Ltd') . "\n";
            $customer_body .= "Account Number: " . get_option('sbha_bank_account_number', '630 842 187 18') . "\n";
            $customer_body .= "Branch Code: " . get_option('sbha_bank_branch_code', '250 655') . "\n";
            $customer_body .= "Reference: {$invoice_number}\n\n";
            if ($document_url) {
                $customer_body .= "View/Download: {$document_url}\n\n";
            }
            $customer_body .= "Thank you for choosing Switch Graphics.";
            $customer_sent = $this->send_portal_email($customer_mail, "Invoice Created - {$invoice_number}", $customer_body);
        }

        wp_send_json_success(array(
            'invoice_number' => $invoice_number,
            'order_id' => $order_id,
            'total' => $calculated_total,
            'message' => 'Invoice created! Pay via EFT and upload proof.',
            'document_url' => $document_url,
            'email_admin_sent' => $admin_sent,
            'email_customer_sent' => $customer_sent
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
        $this->verify_public_nonce();

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

        $docs = array();
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}sbha_quotes WHERE customer_id = %d ORDER BY created_at DESC LIMIT 100",
            $customer_id
        ), ARRAY_A);

        foreach ($rows as $q) {
            $items = json_decode($q['items'] ?? '[]', true);
            $first_item = is_array($items) && !empty($items[0]['product']) ? $items[0]['product'] : (is_array($items) && !empty($items[0]['product_name']) ? $items[0]['product_name'] : 'Order');
            $number = $q['quote_number'];
            $docs[] = array(
                'type' => strpos((string) $number, 'INV-') === 0 ? 'Invoice' : 'Quote',
                'number' => $number,
                'service' => $first_item,
                'total' => 'R' . number_format(floatval($q['total']), 2),
                'date' => date('d M Y', strtotime($q['created_at'])),
                'status' => $q['status'],
                'pdf_url' => $q['pdf_url'],
                'download_url' => add_query_arg(array(
                    'action' => 'sbha_download_document',
                    'number' => $number,
                    'download' => 1
                ), admin_url('admin-ajax.php')),
                'view_url' => add_query_arg(array(
                    'action' => 'sbha_download_document',
                    'number' => $number
                ), admin_url('admin-ajax.php'))
            );
        }

        wp_send_json_success(array('documents' => $docs));
    }

    /**
     * Download or view generated quote/invoice document by number.
     */
    public function download_document() {
        global $wpdb;

        $number = sanitize_text_field($_REQUEST['number'] ?? '');
        if ($number === '') {
            wp_die('Document number missing.');
        }

        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}sbha_quotes WHERE quote_number = %s LIMIT 1",
            $number
        ), ARRAY_A);

        if (!$row) {
            wp_die('Document not found.');
        }

        $is_invoice = strpos((string) ($row['quote_number'] ?? ''), 'INV-') === 0;
        $template_url = $is_invoice
            ? get_option('sbha_invoice_template_url', '')
            : get_option('sbha_quote_template_url', '');
        if (!empty($template_url) && empty($_REQUEST['force_generate']) && intval($_REQUEST['download'] ?? 0) !== 1) {
            wp_redirect(esc_url_raw($template_url));
            exit;
        }

        if (empty($row['pdf_url'])) {
            $generated_url = $this->generate_document_file(intval($row['id']));
            if (!empty($generated_url)) {
                $row['pdf_url'] = $generated_url;
            }
        }

        if (!empty($row['pdf_url']) && empty($_REQUEST['force_generate'])) {
            wp_redirect(esc_url_raw($row['pdf_url']));
            exit;
        }

        $html = $this->build_document_html($row);
        $download = intval($_REQUEST['download'] ?? 0) === 1;

        nocache_headers();
        header('Content-Type: text/html; charset=' . get_bloginfo('charset'));
        if ($download) {
            header('Content-Disposition: attachment; filename="' . sanitize_file_name($number . '.html') . '"');
        } else {
            header('Content-Disposition: inline; filename="' . sanitize_file_name($number . '.html') . '"');
        }

        echo $html;
        exit;
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

        $this->send_portal_email(
            get_option('sbha_business_email', get_option('admin_email')),
            "Message from {$name}",
            "Name: {$name}\nEmail: {$email}\nPhone: {$phone}\n\n{$message}",
            $email
        );

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
        $this->verify_public_nonce();

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
        $this->verify_public_nonce();

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

    /**
     * Public endpoints should not hard-fail on nonce mismatch in cached pages.
     * Returns true when nonce is valid, otherwise false.
     */
    private function verify_public_nonce() {
        $nonce = sanitize_text_field($_REQUEST['nonce'] ?? '');
        if ($nonce === '') {
            return false;
        }
        return (bool) wp_verify_nonce($nonce, 'sbha_nonce');
    }

    private function generate_guest_phone() {
        return '7' . wp_rand(100000000, 999999999);
    }

    private function resolve_or_create_guest_customer($name, $phone, $email = '') {
        global $wpdb;

        $phone = preg_replace('/[^0-9]/', '', (string) $phone);
        if (strlen($phone) < 9) {
            return 0;
        }

        $email = sanitize_email($email);
        $table = $wpdb->prefix . 'sbha_customers';

        if (!empty($email)) {
            $existing = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$table} WHERE cell_number = %s OR whatsapp_number = %s OR email = %s LIMIT 1",
                $phone,
                $phone,
                $email
            ), ARRAY_A);
        } else {
            $existing = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$table} WHERE cell_number = %s OR whatsapp_number = %s LIMIT 1",
                $phone,
                $phone
            ), ARRAY_A);
        }

        if ($existing && !empty($existing['id'])) {
            // Keep existing account and refresh contact details.
            $parts = explode(' ', trim((string) $name), 2);
            $update = array(
                'first_name' => $parts[0] ?: ($existing['first_name'] ?? 'Guest'),
                'last_name' => $parts[1] ?? ($existing['last_name'] ?? ''),
                'cell_number' => $phone,
                'whatsapp_number' => $phone
            );
            if (!empty($email)) {
                $update['email'] = $email;
            }
            $wpdb->update($table, $update, array('id' => intval($existing['id'])));
            return intval($existing['id']);
        }

        $parts = explode(' ', trim((string) $name), 2);
        $resolved_email = $this->resolve_customer_email($email, $phone);

        $inserted = $wpdb->insert($table, array(
            'first_name' => $parts[0] ?: 'Guest',
            'last_name' => $parts[1] ?? '',
            'business_name' => '',
            'email' => $resolved_email,
            'cell_number' => $phone,
            'whatsapp_number' => $phone,
            'password' => password_hash(wp_generate_password(20), PASSWORD_DEFAULT),
            'status' => 'active'
        ));

        if (!$inserted) {
            return 0;
        }

        return intval($wpdb->insert_id);
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

    private function send_portal_email($to, $subject, $body, $reply_to = '') {
        $to = sanitize_email($to);
        if (empty($to)) {
            return false;
        }

        $from_email = sanitize_email(get_option('sbha_business_email', get_option('admin_email')));
        if (empty($from_email)) {
            $from_email = 'noreply@' . preg_replace('/^www\./', '', parse_url(home_url(), PHP_URL_HOST));
        }

        $from_name = sanitize_text_field(get_option('sbha_business_name', 'Switch Graphics'));
        $headers = array(
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . $from_name . ' <' . $from_email . '>'
        );

        $reply = sanitize_email($reply_to);
        if (!empty($reply)) {
            $headers[] = 'Reply-To: ' . $reply;
        } elseif (!empty($from_email)) {
            $headers[] = 'Reply-To: ' . $from_email;
        }

        $sent = wp_mail($to, $subject, $body, $headers);

        $log = get_option('sbha_email_delivery_log', array());
        if (!is_array($log)) {
            $log = array();
        }
        $log[] = array(
            'to' => $to,
            'subject' => sanitize_text_field($subject),
            'sent' => $sent ? 1 : 0,
            'time' => current_time('mysql')
        );
        if (count($log) > 100) {
            $log = array_slice($log, -100);
        }
        update_option('sbha_email_delivery_log', $log);

        return (bool) $sent;
    }

    private function build_document_html($row) {
        global $wpdb;

        $number = sanitize_text_field($row['quote_number'] ?? '');
        $is_invoice = strpos($number, 'INV-') === 0;
        $title = $is_invoice ? 'INVOICE' : 'QUOTATION';

        $customer = null;
        if (!empty($row['customer_id'])) {
            $customer = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}sbha_customers WHERE id = %d",
                intval($row['customer_id'])
            ), ARRAY_A);
        }

        $items = json_decode($row['items'] ?? '[]', true);
        if (!is_array($items)) {
            $items = array();
        }

        $business_name = get_option('sbha_business_name', 'Switch Graphics (Pty) Ltd');
        $business_reg = get_option('sbha_business_reg_number', 'Reg: 2023/000000/07');
        $business_csd = get_option('sbha_business_csd_number', 'CSD: MAAA0000000');
        $business_email = get_option('sbha_business_email', 'info@switchgraphics.co.za');
        $business_phone = get_option('sbha_business_phone', '068 147 4232');
        $business_address = get_option('sbha_business_address', '16 Harding Street, Newcastle, 2940');
        $business_logo = get_option('sbha_business_logo', '');

        $bank_name = get_option('sbha_bank_name', 'FNB/RMB');
        $bank_account_name = get_option('sbha_bank_account_name', 'Switch Graphics (Pty) Ltd');
        $bank_account_number = get_option('sbha_bank_account_number', '630 842 187 18');
        $bank_branch_code = get_option('sbha_bank_branch_code', '250 655');

        $customer_name = trim(($customer['first_name'] ?? '') . ' ' . ($customer['last_name'] ?? ''));
        if ($customer_name === '') {
            $customer_name = 'Client';
        }
        $customer_phone = $row['phone'] ?? ($customer['cell_number'] ?? '');
        $customer_email = $row['email'] ?? ($customer['email'] ?? '');

        $line_rows = '';
        $calc_total = 0.0;
        foreach ($items as $i => $item) {
            $product = sanitize_text_field($item['product'] ?? $item['product_name'] ?? 'Item');
            $variant = sanitize_text_field($item['variant'] ?? $item['variant_name'] ?? '');
            $sku = sanitize_text_field($item['sku'] ?? $item['variant_sku'] ?? '');
            $qty = max(1, intval($item['quantity'] ?? $item['qty'] ?? 1));
            $unit = floatval($item['unit_price'] ?? $item['price'] ?? 0);
            $design_fee = floatval($item['design_fee'] ?? 0);
            $delivery_fee = floatval($item['delivery_fee'] ?? 0);
            $subtotal = ($unit * $qty) + $design_fee + $delivery_fee;
            if (!empty($item['subtotal'])) {
                $subtotal = floatval($item['subtotal']);
            }
            $calc_total += $subtotal;

            $meta = trim($variant . ($sku ? " | SKU: {$sku}" : ''));
            $line_rows .= '<tr>'
                . '<td>' . ($i + 1) . '</td>'
                . '<td><strong>' . esc_html($product) . '</strong>' . ($meta ? '<div class="meta">' . esc_html($meta) . '</div>' : '') . '</td>'
                . '<td>' . $qty . '</td>'
                . '<td>R' . number_format($unit, 2) . '</td>'
                . '<td>R' . number_format($subtotal, 2) . '</td>'
                . '</tr>';
        }
        if ($line_rows === '') {
            $line_rows = '<tr><td>1</td><td><strong>Custom Item</strong></td><td>1</td><td>R' . number_format(floatval($row['total']), 2) . '</td><td>R' . number_format(floatval($row['total']), 2) . '</td></tr>';
            $calc_total = floatval($row['total']);
        }

        $total = floatval($row['total'] ?? $calc_total);
        if ($total <= 0) {
            $total = $calc_total;
        }

        $notes = trim((string) ($row['special_notes'] ?? ''));
        $created = !empty($row['created_at']) ? date('d M Y', strtotime($row['created_at'])) : date('d M Y');
        $logo_html = !empty($business_logo)
            ? '<img src="' . esc_url($business_logo) . '" alt="Logo" style="height:56px;max-width:180px;object-fit:contain;">'
            : '<div style="font-size:26px;font-weight:800;color:#FF6600;">SWITCH GRAPHICS</div>';

        $html = '<!doctype html><html><head><meta charset="utf-8"><title>' . esc_html($number) . '</title>'
            . '<style>'
            . 'body{font-family:Arial,sans-serif;background:#f4f5f8;color:#111;padding:20px;}'
            . '.wrap{max-width:920px;margin:0 auto;background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:24px;}'
            . '.top{display:flex;justify-content:space-between;align-items:flex-start;gap:20px;margin-bottom:18px;}'
            . '.doc{font-size:30px;font-weight:800;color:#FF6600;margin:0;}'
            . '.muted{color:#6b7280;font-size:12px;}'
            . '.grid{display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:16px;}'
            . '.card{background:#f8fafc;border:1px solid #e5e7eb;border-radius:10px;padding:12px;}'
            . 'table{width:100%;border-collapse:collapse;margin-top:12px;} th,td{border:1px solid #e5e7eb;padding:8px;font-size:13px;text-align:left;} th{background:#fff7ed;}'
            . '.meta{font-size:11px;color:#6b7280;margin-top:3px;}'
            . '.totals{margin-top:16px;display:flex;justify-content:flex-end;} .totals .box{min-width:300px;border:1px solid #e5e7eb;border-radius:10px;padding:12px;background:#fff7ed;}'
            . '.row{display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px dashed #fed7aa;} .row:last-child{border-bottom:none;font-size:18px;font-weight:800;}'
            . '.bank{margin-top:16px;background:#111;color:#fff;border-radius:10px;padding:12px;} .bank h4{margin:0 0 8px;color:#FFB27F;}'
            . '.print{margin-top:16px;text-align:right;} .btn{background:#FF6600;color:#fff;border:none;border-radius:8px;padding:10px 14px;cursor:pointer;}'
            . '@media print {.print{display:none} body{background:#fff;padding:0} .wrap{border:none;padding:0;max-width:100%;}}'
            . '</style></head><body><div class="wrap">'
            . '<div class="top"><div>' . $logo_html . '<div class="muted">' . esc_html($business_reg) . ' • ' . esc_html($business_csd) . '</div></div>'
            . '<div style="text-align:right"><p class="doc">' . esc_html($title) . '</p><div><strong>' . esc_html($number) . '</strong></div><div class="muted">Date: ' . esc_html($created) . '</div></div></div>'
            . '<div class="grid"><div class="card"><strong>From</strong><br>' . esc_html($business_name) . '<br>' . esc_html($business_address) . '<br>' . esc_html($business_phone) . '<br>' . esc_html($business_email) . '</div>'
            . '<div class="card"><strong>To</strong><br>' . esc_html($customer_name) . '<br>' . esc_html($customer_phone) . '<br>' . esc_html($customer_email) . '</div></div>'
            . '<table><thead><tr><th>#</th><th>Description</th><th>Qty</th><th>Unit</th><th>Line Total</th></tr></thead><tbody>' . $line_rows . '</tbody></table>'
            . '<div class="totals"><div class="box"><div class="row"><span>Subtotal</span><span>R' . number_format($total, 2) . '</span></div><div class="row"><span>Total</span><span>R' . number_format($total, 2) . '</span></div></div></div>';

        if ($notes !== '') {
            $html .= '<div class="card" style="margin-top:14px;"><strong>Notes</strong><br>' . nl2br(esc_html($notes)) . '</div>';
        }

        $html .= '<div class="bank"><h4>Banking Details</h4>'
            . 'Bank: ' . esc_html($bank_name) . '<br>'
            . 'Account Name: ' . esc_html($bank_account_name) . '<br>'
            . 'Account Number: ' . esc_html($bank_account_number) . '<br>'
            . 'Branch Code: ' . esc_html($bank_branch_code) . '<br>'
            . 'Reference: ' . esc_html($number)
            . '</div>'
            . '<div class="print"><button class="btn" onclick="window.print()">Print / Save as PDF</button></div>'
            . '</div></body></html>';

        return $html;
    }

    private function generate_document_file($quote_id) {
        global $wpdb;

        if (!$quote_id) {
            return '';
        }

        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}sbha_quotes WHERE id = %d LIMIT 1",
            intval($quote_id)
        ), ARRAY_A);
        if (!$row) {
            return '';
        }

        $html = $this->build_document_html($row);
        $filename = sanitize_file_name(($row['quote_number'] ?? 'document') . '-' . date('YmdHis') . '.html');
        $saved = wp_upload_bits($filename, null, $html);
        if (!empty($saved['error']) || empty($saved['url'])) {
            return '';
        }

        $wpdb->update(
            $wpdb->prefix . 'sbha_quotes',
            array('pdf_url' => esc_url_raw($saved['url'])),
            array('id' => intval($row['id']))
        );

        return esc_url_raw($saved['url']);
    }

    private function is_super_admin_customer($customer = null) {
        if (!$customer || !is_array($customer)) {
            return false;
        }

        $admin_email = strtolower((string) get_option('sbha_super_admin_email', 'tinashe@switchgraphics.co.za'));
        $admin_phone = preg_replace('/[^0-9]/', '', (string) get_option('sbha_super_admin_phone', '0681474232'));
        $customer_email = strtolower((string) ($customer['email'] ?? ''));
        $customer_phone = preg_replace('/[^0-9]/', '', (string) ($customer['cell_number'] ?? $customer['whatsapp_number'] ?? ''));

        if (!empty($admin_email) && $customer_email === $admin_email) {
            return true;
        }
        if (!empty($admin_phone) && $customer_phone === $admin_phone) {
            return true;
        }

        return false;
    }

    public function super_admin_update_quote_status() {
        check_ajax_referer('sbha_nonce', 'nonce');
        global $wpdb;

        $customer = $this->get_customer();
        if (!$this->is_super_admin_customer($customer)) {
            wp_send_json_error('Unauthorized');
        }

        $quote_id = intval($_POST['quote_id'] ?? 0);
        $status = sanitize_text_field($_POST['status'] ?? '');
        $allowed = array('pending', 'reviewed', 'quoted', 'accepted', 'rejected', 'expired', 'processing', 'ready', 'completed', 'cancelled');

        if ($quote_id < 1 || !in_array($status, $allowed, true)) {
            wp_send_json_error('Invalid status update.');
        }

        $updated = $wpdb->update(
            $wpdb->prefix . 'sbha_quotes',
            array('status' => $status),
            array('id' => $quote_id)
        );

        if ($updated === false) {
            wp_send_json_error('Unable to update status.');
        }

        wp_send_json_success(array('message' => 'Status updated.'));
    }

    public function super_admin_update_product_variation() {
        check_ajax_referer('sbha_nonce', 'nonce');

        $customer = $this->get_customer();
        if (!$this->is_super_admin_customer($customer)) {
            wp_send_json_error('Unauthorized');
        }

        $product_key = sanitize_text_field($_POST['product_key'] ?? '');
        $variation_index = intval($_POST['variation_index'] ?? -1);
        $price = floatval($_POST['price'] ?? 0);

        if ($product_key === '' || $variation_index < 0 || $price <= 0) {
            wp_send_json_error('Invalid product update input.');
        }

        require_once SBHA_PLUGIN_DIR . 'includes/class-sbha-products.php';
        $all_products = SBHA_Products::get_all();
        if (empty($all_products[$product_key]) || empty($all_products[$product_key]['variations'][$variation_index])) {
            wp_send_json_error('Product variation not found.');
        }

        $custom_products = get_option('sbha_custom_products', array());
        $target = $custom_products[$product_key] ?? $all_products[$product_key];
        if (empty($target['variations'][$variation_index])) {
            wp_send_json_error('Variation not found.');
        }

        $target['variations'][$variation_index]['price'] = $price;
        $custom_products[$product_key] = $target;
        update_option('sbha_custom_products', $custom_products);

        wp_send_json_success(array(
            'message' => 'Product pricing updated.',
            'product_key' => $product_key,
            'variation_index' => $variation_index,
            'price' => $price
        ));
    }

    public function super_admin_update_document() {
        check_ajax_referer('sbha_nonce', 'nonce');
        global $wpdb;

        $customer = $this->get_customer();
        if (!$this->is_super_admin_customer($customer)) {
            wp_send_json_error('Unauthorized');
        }

        $quote_id = intval($_POST['quote_id'] ?? 0);
        $total = floatval($_POST['total'] ?? 0);
        $status = sanitize_text_field($_POST['status'] ?? '');
        $admin_notes = sanitize_textarea_field($_POST['admin_notes'] ?? '');

        if ($quote_id < 1) {
            wp_send_json_error('Invalid document.');
        }

        $payload = array();
        if ($total > 0) {
            $payload['total'] = $total;
        }
        if ($admin_notes !== '') {
            $payload['admin_notes'] = $admin_notes;
        }

        $allowed = array('pending', 'reviewed', 'quoted', 'accepted', 'rejected', 'expired', 'processing', 'ready', 'completed', 'cancelled');
        if ($status && in_array($status, $allowed, true)) {
            $payload['status'] = $status;
        }

        if (empty($payload)) {
            wp_send_json_error('Nothing to update.');
        }

        $updated = $wpdb->update($wpdb->prefix . 'sbha_quotes', $payload, array('id' => $quote_id));
        if ($updated === false) {
            wp_send_json_error('Update failed.');
        }

        $document_url = $this->generate_document_file($quote_id);

        wp_send_json_success(array(
            'message' => 'Document updated.',
            'document_url' => $document_url
        ));
    }

    public function super_admin_save_branding() {
        check_ajax_referer('sbha_nonce', 'nonce');

        $customer = $this->get_customer();
        if (!$this->is_super_admin_customer($customer)) {
            wp_send_json_error('Unauthorized');
        }

        $updates = array(
            'sbha_business_name' => sanitize_text_field($_POST['business_name'] ?? get_option('sbha_business_name', 'Switch Graphics (Pty) Ltd')),
            'sbha_business_reg_number' => sanitize_text_field($_POST['business_reg_number'] ?? get_option('sbha_business_reg_number', '')),
            'sbha_business_csd_number' => sanitize_text_field($_POST['business_csd_number'] ?? get_option('sbha_business_csd_number', '')),
            'sbha_business_logo' => esc_url_raw($_POST['business_logo'] ?? get_option('sbha_business_logo', '')),
            'sbha_bank_name' => sanitize_text_field($_POST['bank_name'] ?? get_option('sbha_bank_name', 'FNB/RMB')),
            'sbha_bank_account_name' => sanitize_text_field($_POST['bank_account_name'] ?? get_option('sbha_bank_account_name', 'Switch Graphics (Pty) Ltd')),
            'sbha_bank_account_number' => sanitize_text_field($_POST['bank_account_number'] ?? get_option('sbha_bank_account_number', '630 842 187 18')),
            'sbha_bank_branch_code' => sanitize_text_field($_POST['bank_branch_code'] ?? get_option('sbha_bank_branch_code', '250 655')),
            'sbha_quote_template_url' => esc_url_raw($_POST['quote_template_url'] ?? get_option('sbha_quote_template_url', '')),
            'sbha_invoice_template_url' => esc_url_raw($_POST['invoice_template_url'] ?? get_option('sbha_invoice_template_url', ''))
        );

        foreach ($updates as $key => $value) {
            update_option($key, $value);
        }

        wp_send_json_success(array(
            'message' => 'Branding details saved.',
            'business_logo' => $updates['sbha_business_logo']
        ));
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
        $this->verify_public_nonce();

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

        $this->verify_public_nonce();

        $name = sanitize_text_field($_POST['name'] ?? '');
        $email_input = sanitize_email($_POST['email'] ?? '');
        $phone = sanitize_text_field($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $quote_data = json_decode(stripslashes($_POST['quote_data'] ?? '{}'), true);
        $requested_document = sanitize_text_field($_POST['document_type'] ?? '');
        $transcript_raw = $_POST['transcript'] ?? '';
        $transcript = is_string($transcript_raw) ? sanitize_textarea_field($transcript_raw) : wp_json_encode($transcript_raw);

        if (!is_array($quote_data)) {
            $quote_data = array();
        }

        if (empty($requested_document)) {
            $requested_document = sanitize_text_field($quote_data['preferred_document'] ?? 'quote');
        }
        if (!in_array($requested_document, array('quote', 'invoice'), true)) {
            $requested_document = 'quote';
        }

        if (empty($name)) {
            $name = 'Client';
        }

        // Normalize phone, but auto-generate one for low-friction guest flow.
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phone) < 9) {
            $phone = $this->generate_guest_phone();
        }

        $email = $this->resolve_customer_email($email_input, $phone);

        // Generate document number
        if ($requested_document === 'invoice') {
            $count = intval($wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}sbha_quotes WHERE quote_number LIKE 'INV-SBH%'")) + 1;
            $quote_number = 'INV-SBH' . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
        } else {
            $count = intval($wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}sbha_quotes WHERE quote_number LIKE 'QT-SBH%'")) + 1;
            $quote_number = 'QT-SBH' . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
        }

        // Check if customer exists
        $table = $wpdb->prefix . 'sbha_customers';
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE cell_number = %s OR whatsapp_number = %s",
            $phone, $phone
        ));
        
        $account_created = false;
        $has_authenticated_session = !empty($this->get_customer());
        
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
            // Create new customer profile. If no password is provided, treat as guest profile.
            if (empty($password)) {
                $customer_id = $this->resolve_or_create_guest_customer($name, $phone, $email_input);
                if (!$customer_id) {
                    wp_send_json_error('Unable to create customer profile.');
                }
            } else {
                $parts = explode(' ', $name, 2);
                $wpdb->insert($table, array(
                    'first_name' => $parts[0],
                    'last_name' => $parts[1] ?? '',
                    'email' => $email,
                    'cell_number' => $phone,
                    'whatsapp_number' => $phone,
                    'business_name' => '',
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'status' => 'active'
                ));
                $customer_id = $wpdb->insert_id;
                $account_created = true;
            }
        }

        // Only auto-login if customer already had a valid session or supplied a password.
        $token = '';
        if ($has_authenticated_session || !empty($password)) {
            $token = $this->create_session($customer_id);
            setcookie('sbha_token', $token, time() + (30 * 24 * 60 * 60), COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);
        }

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
            'email' => $email_input ?: $email,
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
            'pdf_url' => '',
            'status' => 'pending',
            'created_at' => current_time('mysql')
        ));

        $quote_id = $wpdb->insert_id;
        $document_url = '';
        if ($quote_id) {
            $document_url = $this->generate_document_file($quote_id);
        }

        // Email admin
        $admin_email = get_option('sbha_business_email', get_option('admin_email'));
        
        $admin_document_label = $requested_document === 'invoice' ? 'Invoice Request' : 'Quote Request';
        $email_body = "New {$admin_document_label}: {$quote_number}\n\n";
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

        $admin_sent = $this->send_portal_email($admin_email, "New {$admin_document_label}: {$quote_number} - {$name}", $email_body, $email_input);

        // Email customer
        $customer_email = "Hi {$name},\n\n";
        $customer_email .= $requested_document === 'invoice'
            ? "Thank you for your order! Your invoice has been created.\n\n"
            : "Thank you for your quote request!\n\n";
        $customer_email .= ($requested_document === 'invoice' ? "Invoice Reference: " : "Quote Reference: ") . "{$quote_number}\n";
        $customer_email .= "Estimated Total: R" . number_format($total, 2) . "\n\n";
        if ($requested_document === 'invoice') {
            $customer_email .= "Please use this reference when making payment and upload your proof in the portal.\n\n";
        } else {
            $customer_email .= "We'll review your request and send you a formal quote shortly.\n\n";
        }
        if (!empty($document_url)) {
            $customer_email .= "View/Download Document: {$document_url}\n\n";
        }
        $customer_email .= "If you have any questions, feel free to WhatsApp us at " . get_option('sbha_whatsapp', '068 147 4232') . "\n\n";
        $customer_email .= "Thank you for choosing Switch Graphics!\n\n";
        $customer_email .= "---\n";
        $customer_email .= "Switch Graphics (Pty) Ltd\n";
        $customer_email .= "16 Harding Street, Newcastle, 2940\n";
        $customer_email .= "Tel: 068 147 4232\n";
        $customer_email .= "www.switchgraphics.co.za";

        $customer_email_target = !empty($email_input) ? $email_input : $email;
        if (strpos((string) $customer_email_target, '@switchhub.local') !== false || strpos((string) $customer_email_target, '@switchgraphics.local') !== false) {
            $customer_email_target = '';
        }

        $customer_sent = false;
        if (!empty($customer_email_target)) {
            $customer_subject = $requested_document === 'invoice'
                ? "Invoice Created - {$quote_number}"
                : "Quote Request Received - {$quote_number}";
            $customer_sent = $this->send_portal_email($customer_email_target, $customer_subject, $customer_email);
        }

        wp_send_json_success(array(
            'message' => $requested_document === 'invoice' ? 'Invoice created successfully!' : 'Quote submitted successfully!',
            'quote_number' => $quote_number,
            'invoice_number' => $requested_document === 'invoice' ? $quote_number : '',
            'quote_id' => $quote_id,
            'account_created' => $account_created,
            'token' => $token,
            'document_type' => $requested_document,
            'document_number' => $quote_number,
            'document_url' => $document_url,
            'email_admin_sent' => $admin_sent,
            'email_customer_sent' => $customer_sent
        ));
    }
}

new SBHA_Ajax();
