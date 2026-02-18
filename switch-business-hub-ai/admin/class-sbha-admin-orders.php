<?php
/**
 * Admin Orders & Invoices Management
 *
 * Full management of quotes and invoices
 * - View all orders
 * - Verify payment proof
 * - Generate WhatsApp messages
 * - Update status
 *
 * @package SwitchBusinessHub
 * @version 1.9.1
 */

if (!defined('ABSPATH')) {
    exit;
}

class SBHA_Admin_Orders {

    public function __construct() {
        add_action('wp_ajax_sbha_update_order_status', array($this, 'ajax_update_status'));
        add_action('wp_ajax_sbha_verify_payment', array($this, 'ajax_verify_payment'));
    }

    public function render() {
        global $wpdb;
        
        $tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'all';
        $view_id = isset($_GET['view']) ? intval($_GET['view']) : 0;
        
        // Get counts
        $all_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}sbha_quotes");
        $pending_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}sbha_quotes WHERE status = 'pending'");
        $invoice_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}sbha_quotes WHERE quote_number LIKE 'INV-%'");
        $quote_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}sbha_quotes WHERE quote_number LIKE 'QT-%'");
        
        // Build query
        $where = "1=1";
        if ($tab === 'pending') $where = "status = 'pending'";
        if ($tab === 'invoices') $where = "quote_number LIKE 'INV-%'";
        if ($tab === 'quotes') $where = "quote_number LIKE 'QT-%'";
        
        $orders = $wpdb->get_results("
            SELECT q.*, c.first_name, c.last_name, c.cell_number, c.email
            FROM {$wpdb->prefix}sbha_quotes q
            LEFT JOIN {$wpdb->prefix}sbha_customers c ON q.customer_id = c.id
            WHERE {$where}
            ORDER BY q.created_at DESC
            LIMIT 100
        ");
        
        // View single order
        $single = null;
        if ($view_id) {
            $single = $wpdb->get_row($wpdb->prepare("
                SELECT q.*, c.first_name, c.last_name, c.cell_number, c.email
                FROM {$wpdb->prefix}sbha_quotes q
                LEFT JOIN {$wpdb->prefix}sbha_customers c ON q.customer_id = c.id
                WHERE q.id = %d
            ", $view_id));
        }
        
        $whatsapp = '27681474232';
        ?>
        <div class="wrap sbha-admin-wrap">
            <h1 class="sbha-admin-title">📦 Orders & Invoices</h1>
            
            <style>
                .sbha-tabs{display:flex;gap:0;margin-bottom:20px;border-bottom:2px solid #ddd}
                .sbha-tab{padding:12px 20px;background:#f8f9fa;border:none;cursor:pointer;font-weight:600;color:#666;border-bottom:2px solid transparent;margin-bottom:-2px}
                .sbha-tab.active{background:#fff;color:#FF6600;border-bottom-color:#FF6600}
                .sbha-tab .count{background:#eee;padding:2px 8px;border-radius:10px;font-size:11px;margin-left:6px}
                .sbha-tab.active .count{background:#FF6600;color:#fff}
                
                .sbha-orders-table{width:100%;border-collapse:collapse;background:#fff;box-shadow:0 2px 5px rgba(0,0,0,0.1)}
                .sbha-orders-table th,.sbha-orders-table td{padding:12px 15px;text-align:left;border-bottom:1px solid #eee}
                .sbha-orders-table th{background:#f8f9fa;font-weight:600;font-size:12px;text-transform:uppercase}
                .sbha-orders-table tr:hover{background:#fff7ed}
                .sbha-orders-table tr{cursor:pointer}
                
                .sbha-status{display:inline-block;padding:4px 10px;border-radius:15px;font-size:11px;font-weight:600;text-transform:uppercase}
                .sbha-status-pending{background:#fef3c7;color:#d97706}
                .sbha-status-processing{background:#dbeafe;color:#2563eb}
                .sbha-status-completed{background:#d1fae5;color:#059669}
                .sbha-status-cancelled{background:#fee2e2;color:#dc2626}
                
                .sbha-type{display:inline-block;padding:2px 8px;border-radius:4px;font-size:10px;font-weight:600}
                .sbha-type-invoice{background:#e0e7ff;color:#4f46e5}
                .sbha-type-quote{background:#fce7f3;color:#db2777}
                
                .sbha-order-detail{display:grid;grid-template-columns:2fr 1fr;gap:20px;margin-top:20px}
                @media(max-width:900px){.sbha-order-detail{grid-template-columns:1fr}}
                
                .sbha-card{background:#fff;border-radius:10px;padding:20px;box-shadow:0 2px 5px rgba(0,0,0,0.1);margin-bottom:20px}
                .sbha-card h3{margin:0 0 15px;font-size:16px;display:flex;align-items:center;gap:8px}
                
                .sbha-info-row{display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #eee}
                .sbha-info-row:last-child{border:none}
                .sbha-info-label{color:#666;font-size:13px}
                .sbha-info-value{font-weight:600;font-size:13px}
                
                .sbha-items-table{width:100%;border-collapse:collapse;margin-top:10px}
                .sbha-items-table th,.sbha-items-table td{padding:8px;text-align:left;border-bottom:1px solid #eee;font-size:13px}
                .sbha-items-table th{background:#f8f9fa;font-weight:600}
                
                .sbha-wa-box{background:#d1fae5;border:2px solid #059669;border-radius:10px;padding:15px;margin-top:15px}
                .sbha-wa-box h4{color:#059669;margin:0 0 10px;font-size:14px}
                .sbha-wa-msg{background:#fff;padding:12px;border-radius:8px;font-size:13px;line-height:1.5;white-space:pre-wrap;margin-bottom:10px}
                .sbha-wa-btn{display:inline-flex;align-items:center;gap:6px;background:#25D366;color:#fff;padding:10px 16px;border-radius:8px;text-decoration:none;font-weight:600;font-size:13px}
                
                .sbha-status-select{padding:8px 12px;border:2px solid #ddd;border-radius:6px;font-size:13px;margin-right:10px}
                .sbha-btn{padding:10px 20px;border:none;border-radius:6px;font-weight:600;cursor:pointer;font-size:13px}
                .sbha-btn-primary{background:#FF6600;color:#fff}
                .sbha-btn-success{background:#059669;color:#fff}
                
                .sbha-payment-proof{max-width:300px;border-radius:8px;margin-top:10px;cursor:pointer}
                .sbha-payment-proof:hover{opacity:0.8}
            </style>
            
            <!-- Tabs -->
            <div class="sbha-tabs">
                <a href="?page=sbha-orders&tab=all" class="sbha-tab <?php echo $tab==='all'?'active':''; ?>">All <span class="count"><?php echo $all_count; ?></span></a>
                <a href="?page=sbha-orders&tab=pending" class="sbha-tab <?php echo $tab==='pending'?'active':''; ?>">⏳ Pending <span class="count"><?php echo $pending_count; ?></span></a>
                <a href="?page=sbha-orders&tab=invoices" class="sbha-tab <?php echo $tab==='invoices'?'active':''; ?>">🧾 Invoices <span class="count"><?php echo $invoice_count; ?></span></a>
                <a href="?page=sbha-orders&tab=quotes" class="sbha-tab <?php echo $tab==='quotes'?'active':''; ?>">📋 Quotes <span class="count"><?php echo $quote_count; ?></span></a>
            </div>
            
            <?php if ($single): ?>
            <!-- Single Order View -->
            <a href="?page=sbha-orders&tab=<?php echo $tab; ?>" style="display:inline-flex;align-items:center;gap:6px;margin-bottom:15px;color:#666;text-decoration:none">← Back to list</a>
            
            <div class="sbha-order-detail">
                <div>
                    <!-- Order Info -->
                    <div class="sbha-card">
                        <h3>
                            <?php echo esc_html($single->quote_number); ?>
                            <?php $type = strpos($single->quote_number, 'INV-') === 0 ? 'invoice' : 'quote'; ?>
                            <span class="sbha-type sbha-type-<?php echo $type; ?>"><?php echo strtoupper($type); ?></span>
                            <span class="sbha-status sbha-status-<?php echo esc_attr($single->status); ?>"><?php echo esc_html($single->status); ?></span>
                        </h3>
                        
                        <div class="sbha-info-row">
                            <span class="sbha-info-label">Customer</span>
                            <span class="sbha-info-value"><?php echo esc_html($single->first_name . ' ' . $single->last_name); ?></span>
                        </div>
                        <div class="sbha-info-row">
                            <span class="sbha-info-label">Phone</span>
                            <span class="sbha-info-value"><?php echo esc_html($single->cell_number); ?></span>
                        </div>
                        <div class="sbha-info-row">
                            <span class="sbha-info-label">Email</span>
                            <span class="sbha-info-value"><?php echo esc_html($single->email ?: '-'); ?></span>
                        </div>
                        <div class="sbha-info-row">
                            <span class="sbha-info-label">Date</span>
                            <span class="sbha-info-value"><?php echo date('d M Y H:i', strtotime($single->created_at)); ?></span>
                        </div>
                        <div class="sbha-info-row">
                            <span class="sbha-info-label">Total</span>
                            <span class="sbha-info-value" style="font-size:18px;color:#FF6600">R<?php echo number_format($single->total, 2); ?></span>
                        </div>
                    </div>
                    
                    <!-- Items -->
                    <div class="sbha-card">
                        <h3>📦 Items</h3>
                        <?php $items = json_decode($single->items, true) ?: array(); ?>
                        <table class="sbha-items-table">
                            <thead><tr><th>Item</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead>
                            <tbody>
                            <?php foreach ($items as $item): 
                                $item_total = ($item['price'] ?? $item['unit_price'] ?? 0) * ($item['quantity'] ?? 1);
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc_html($item['name'] ?? $item['product_name'] ?? ''); ?></strong>
                                    <br><small style="color:#666"><?php echo esc_html($item['variation'] ?? $item['variant_name'] ?? ''); ?></small>
                                    <?php if (!empty($item['brief'])): ?>
                                    <br><small style="color:#059669">📝 <?php echo esc_html(substr($item['brief'], 0, 100)); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html($item['quantity'] ?? 1); ?></td>
                                <td>R<?php echo number_format($item['price'] ?? $item['unit_price'] ?? 0, 2); ?></td>
                                <td>R<?php echo number_format($item_total, 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Payment Proof -->
                    <div class="sbha-card">
                        <h3>💳 Payment Proof</h3>
                        <?php if (!empty($single->payment_proof)): ?>
                        <p style="color:var(--success);margin-bottom:10px">✅ Proof uploaded</p>
                        <?php 
                        $is_pdf = strpos($single->payment_proof, '.pdf') !== false;
                        if ($is_pdf): ?>
                        <a href="<?php echo esc_url($single->payment_proof); ?>" target="_blank" style="display:inline-block;padding:10px 20px;background:#dbeafe;color:#2563eb;border-radius:8px;text-decoration:none">📄 View PDF</a>
                        <?php else: ?>
                        <img src="<?php echo esc_url($single->payment_proof); ?>" class="sbha-payment-proof" onclick="window.open(this.src)">
                        <?php endif; ?>
                        <?php if ($single->status === 'pending'): ?>
                        <div style="margin-top:15px">
                            <button class="sbha-btn sbha-btn-success" onclick="verifyPayment(<?php echo $single->id; ?>)">✅ Verify Payment & Start Work</button>
                        </div>
                        <?php endif; ?>
                        <?php else: ?>
                        <p style="color:var(--muted)">No payment proof uploaded yet</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div>
                    <!-- Update Status -->
                    <div class="sbha-card">
                        <h3>⚙️ Update Status</h3>
                        <select class="sbha-status-select" id="statusSelect">
                            <option value="pending" <?php selected($single->status, 'pending'); ?>>⏳ Pending</option>
                            <option value="processing" <?php selected($single->status, 'processing'); ?>>🔄 Processing</option>
                            <option value="ready" <?php selected($single->status, 'ready'); ?>>✅ Ready</option>
                            <option value="completed" <?php selected($single->status, 'completed'); ?>>🎉 Completed</option>
                            <option value="cancelled" <?php selected($single->status, 'cancelled'); ?>>❌ Cancelled</option>
                        </select>
                        <button class="sbha-btn sbha-btn-primary" onclick="updateStatus(<?php echo $single->id; ?>)">Update</button>
                    </div>
                    
                    <!-- WhatsApp Messages -->
                    <div class="sbha-card">
                        <h3>💬 WhatsApp Messages</h3>
                        <p style="font-size:12px;color:#666;margin-bottom:15px">Copy these messages to send to customer:</p>
                        
                        <?php
                        $name = $single->first_name;
                        $num = $single->quote_number;
                        $total = 'R' . number_format($single->total, 2);
                        $phone = preg_replace('/[^0-9]/', '', $single->cell_number);
                        if (substr($phone, 0, 1) === '0') $phone = '27' . substr($phone, 1);
                        
                        $messages = array(
                            'Order Received' => "Hi {$name}! 👋\n\nThank you for your order {$num}!\n\n💰 Total: {$total}\n\n🏦 Banking Details:\nFNB/RMB\nSwitch Graphics (Pty) Ltd\nAcc: 6308421871 8\nBranch: 250655\n\nPlease use {$num} as reference.\n\nOnce paid, upload proof on your account or WhatsApp it to us.\n\nThank you!\nSwitch Graphics 🧡",
                            
                            'Payment Received' => "Hi {$name}! 👋\n\n✅ Payment received for {$num}!\n\nWe're now working on your order. We'll update you when it's ready.\n\nThank you!\nSwitch Graphics 🧡",
                            
                            'Order Ready' => "Hi {$name}! 👋\n\n🎉 Great news! Your order {$num} is READY!\n\n📍 Collection: 16 Harding Street, Newcastle\n🕐 Hours: Mon-Fri 8am-5pm, Sat 9am-1pm\n\nOr let us know if you need delivery.\n\nThank you!\nSwitch Graphics 🧡",
                            
                            'Order Completed' => "Hi {$name}! 👋\n\n✅ Order {$num} completed!\n\nThank you for choosing Switch Graphics. We hope you love your {$type}!\n\nPlease leave us a review if you're happy with our service. 🙏\n\nSee you again soon!\nSwitch Graphics 🧡",
                        );
                        
                        foreach ($messages as $title => $msg):
                            $wa_link = "https://wa.me/{$phone}?text=" . rawurlencode($msg);
                        ?>
                        <div class="sbha-wa-box" style="margin-bottom:15px;background:#f8f9fa;border-color:#ddd">
                            <h4 style="color:#333"><?php echo $title; ?></h4>
                            <div class="sbha-wa-msg"><?php echo esc_html($msg); ?></div>
                            <button onclick="copyMsg(this)" style="margin-right:10px" class="sbha-btn">📋 Copy</button>
                            <a href="<?php echo esc_url($wa_link); ?>" target="_blank" class="sbha-wa-btn">💬 Send WhatsApp</a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <script>
            function copyMsg(btn) {
                const msg = btn.previousElementSibling.textContent;
                navigator.clipboard.writeText(msg);
                btn.textContent = '✓ Copied!';
                setTimeout(() => btn.textContent = '📋 Copy', 2000);
            }
            
            function updateStatus(id) {
                const status = document.getElementById('statusSelect').value;
                fetch(ajaxurl, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: new URLSearchParams({action: 'sbha_update_order_status', order_id: id, status: status})
                }).then(r => r.json()).then(d => {
                    if (d.success) { alert('Status updated!'); location.reload(); }
                    else alert(d.data || 'Error');
                });
            }
            
            function verifyPayment(id) {
                if (!confirm('Verify payment and mark as processing?')) return;
                fetch(ajaxurl, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: new URLSearchParams({action: 'sbha_verify_payment', order_id: id})
                }).then(r => r.json()).then(d => {
                    if (d.success) { alert('Payment verified!'); location.reload(); }
                    else alert(d.data || 'Error');
                });
            }
            </script>
            
            <?php else: ?>
            <!-- Orders List -->
            <table class="sbha-orders-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Type</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                    <tr><td colspan="6" style="text-align:center;padding:40px;color:#666">No orders found</td></tr>
                    <?php else: foreach ($orders as $order): 
                        $type = strpos($order->quote_number, 'INV-') === 0 ? 'invoice' : 'quote';
                    ?>
                    <tr onclick="location.href='?page=sbha-orders&tab=<?php echo $tab; ?>&view=<?php echo $order->id; ?>'">
                        <td><strong><?php echo esc_html($order->quote_number); ?></strong></td>
                        <td><span class="sbha-type sbha-type-<?php echo $type; ?>"><?php echo strtoupper($type); ?></span></td>
                        <td>
                            <?php echo esc_html($order->first_name . ' ' . $order->last_name); ?>
                            <br><small style="color:#666"><?php echo esc_html($order->cell_number); ?></small>
                        </td>
                        <td>R<?php echo number_format($order->total, 2); ?></td>
                        <td><span class="sbha-status sbha-status-<?php echo esc_attr($order->status); ?>"><?php echo esc_html($order->status); ?></span></td>
                        <td><?php echo date('d M Y', strtotime($order->created_at)); ?></td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
        <?php
    }
    
    public function ajax_update_status() {
        global $wpdb;
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Not authorized');
        }
        
        $order_id = intval($_POST['order_id'] ?? 0);
        $status = sanitize_text_field($_POST['status'] ?? '');
        
        if (!$order_id || !$status) {
            wp_send_json_error('Invalid data');
        }
        
        $wpdb->update(
            $wpdb->prefix . 'sbha_quotes',
            array('status' => $status),
            array('id' => $order_id)
        );
        
        wp_send_json_success('Updated');
    }
    
    public function ajax_verify_payment() {
        global $wpdb;
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Not authorized');
        }
        
        $order_id = intval($_POST['order_id'] ?? 0);
        
        if (!$order_id) {
            wp_send_json_error('Invalid order');
        }
        
        $wpdb->update(
            $wpdb->prefix . 'sbha_quotes',
            array('status' => 'processing', 'payment_verified' => 1),
            array('id' => $order_id)
        );
        
        wp_send_json_success('Payment verified');
    }
}
