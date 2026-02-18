<?php
/**
 * Switch Graphics Customer Portal v2.0
 * PREMIUM DARK THEME - APP-LIKE EXPERIENCE
 * 
 * @package SwitchBusinessHub
 * @version 2.2.1
 */

if (!defined('ABSPATH')) exit;

class SBHA_Shortcodes {

    private static $rendered_once = false;

    public function __construct() {
        add_shortcode('switch_hub', array($this, 'render'));
    }

    public function render($atts) {
        if (self::$rendered_once) {
            return '';
        }
        self::$rendered_once = true;

        global $wpdb;

        $ajax = admin_url('admin-ajax.php');
        $nonce = wp_create_nonce('sbha_nonce');

        $customer = $this->get_logged_in_customer();
        $logged_in = !empty($customer);

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-sbha-products.php';
        $products = SBHA_Products::get_all();
        $categories = SBHA_Products::get_categories();
        foreach ($products as $p_key => $product) {
            $products[$p_key]['image_url'] = SBHA_Products::get_product_image($p_key, $product);
        }
        
        $portfolio_items = get_option('sbha_portfolio_items', $this->get_default_portfolio());
        $portfolio_cats = array_merge(array('All'), $this->get_portfolio_categories($portfolio_items));
        
        $customer_orders = array();
        $notifications = array();
        $invoice_count = 0;
        $quote_count = 0;
        $pending_count = 0;
        $is_super_admin = false;
        $admin_quotes = array();
        if ($logged_in) {
            $customer_orders = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}sbha_quotes WHERE customer_id = %d ORDER BY created_at DESC LIMIT 20",
                $customer['id']
            ));
            foreach ($customer_orders as $o) {
                $is_quote_doc = strpos((string) ($o->quote_number ?? ''), 'QT-') === 0;
                if ($is_quote_doc) {
                    $quote_count++;
                } else {
                    $invoice_count++;
                }
                if (($o->status ?? '') === 'pending') {
                    $pending_count++;
                }
            }
            // Generate notifications from orders
            foreach ($customer_orders as $o) {
                if ($o->status === 'ready') {
                    $notifications[] = array('type' => 'success', 'msg' => "{$o->quote_number} is ready for collection!", 'time' => $o->updated_at ?? $o->created_at);
                } elseif ($o->status === 'processing') {
                    $notifications[] = array('type' => 'info', 'msg' => "{$o->quote_number} is being processed", 'time' => $o->updated_at ?? $o->created_at);
                }
            }

            $super_admin_email = strtolower((string) get_option('sbha_super_admin_email', 'tinashe@switchgraphics.co.za'));
            $super_admin_phone = preg_replace('/[^0-9]/', '', (string) get_option('sbha_super_admin_phone', '0681474232'));
            $customer_email = strtolower((string) ($customer['email'] ?? ''));
            $customer_phone = preg_replace('/[^0-9]/', '', (string) ($customer['cell_number'] ?? $customer['whatsapp_number'] ?? ''));
            $is_super_admin = (!empty($super_admin_email) && $customer_email === $super_admin_email) || (!empty($super_admin_phone) && $customer_phone === $super_admin_phone);

            if ($is_super_admin) {
                $admin_quotes = $wpdb->get_results("SELECT q.*, c.first_name, c.last_name, c.cell_number FROM {$wpdb->prefix}sbha_quotes q LEFT JOIN {$wpdb->prefix}sbha_customers c ON q.customer_id=c.id ORDER BY q.created_at DESC LIMIT 30");
            }
        }
        
        $business_name = get_option('sbha_business_name', 'Switch Graphics (Pty) Ltd');
        $business_reg_number = get_option('sbha_business_reg_number', 'Reg: 2023/000000/07');
        $business_csd_number = get_option('sbha_business_csd_number', 'CSD: MAAA0000000');
        $business_logo = get_option('sbha_business_logo', '');
        $phone_option = get_option('sbha_business_phone', '068 147 4232');
        $wa_option = get_option('sbha_whatsapp', $phone_option);
        $email = get_option('sbha_business_email', 'info@switchgraphics.co.za');
        $address = get_option('sbha_business_address', '16 Harding Street, Newcastle, 2940');
        $bank_name = get_option('sbha_bank_name', 'FNB/RMB');
        $bank_account_name = get_option('sbha_bank_account_name', 'Switch Graphics (Pty) Ltd');
        $bank_account_number = get_option('sbha_bank_account_number', '630 842 187 18');
        $bank_branch_code = get_option('sbha_bank_branch_code', '250 655');
        $quote_template_url = get_option('sbha_quote_template_url', '');
        $invoice_template_url = get_option('sbha_invoice_template_url', '');

        $wa_digits = preg_replace('/[^0-9]/', '', $wa_option);
        if (strpos($wa_digits, '0') === 0) {
            $wa = '27' . substr($wa_digits, 1);
        } elseif (strpos($wa_digits, '27') === 0) {
            $wa = $wa_digits;
        } else {
            $wa = '27' . $wa_digits;
        }
        $wa_display = $wa_option;
        $phone_display = $phone_option;

        ob_start();
        ?>
        <style>
        :root{--bg:#000000;--card:#0f0f0f;--card2:#171717;--primary:#FF6600;--primary-glow:rgba(255,102,0,0.3);--text:#ffffff;--text2:#b0b0b0;--border:#2d2d2d;--success:#00C853;--info:#2196F3;--warning:#FFC107;--danger:#FF5252;--radius:16px;--shadow:0 6px 26px rgba(0,0,0,0.45)}
        html,body{margin:0!important;padding:0!important;background:#000!important}
        .sgp{font-family:'Inter',-apple-system,BlinkMacSystemFont,sans-serif;background:var(--bg);min-height:100dvh;color:var(--text);padding-bottom:92px;width:100vw;max-width:100vw;position:relative;left:50%;right:50%;margin-left:-50vw;margin-right:-50vw}
        .sgp *{box-sizing:border-box;margin:0;padding:0}
        .site-main,.entry-content,.wp-site-blocks,.is-layout-constrained{max-width:100%!important;padding-left:0!important;padding-right:0!important}
        
        /* Header */
        .sgp-header{background:linear-gradient(135deg,#1a1a1a,#2a2a2a);padding:20px;border-bottom:1px solid var(--border)}
        .sgp-header-top{display:flex;justify-content:space-between;align-items:center}
        .sgp-logo{display:flex;align-items:center;gap:12px}
        .sgp-logo-icon{width:44px;height:44px;background:var(--primary);border-radius:12px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:16px}
        .sgp-logo-icon img{width:100%;height:100%;object-fit:cover;border-radius:12px}
        .sgp-logo-text h1{font-size:16px;font-weight:700;color:var(--primary)}
        .sgp-logo-text p{font-size:10px;color:#ffd7bd}
        .sgp-header-actions{display:flex;gap:8px}
        .sgp-icon-btn{width:40px;height:40px;border-radius:12px;background:var(--card2);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;color:var(--text);cursor:pointer;position:relative}
        .sgp-icon-btn svg{width:20px;height:20px}
        .sgp-badge{position:absolute;top:-4px;right:-4px;background:var(--primary);color:#fff;font-size:10px;min-width:18px;height:18px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-weight:700}
        
        /* User Welcome */
        .sgp-welcome{padding:16px 20px;background:linear-gradient(135deg,var(--primary),#ff8533);margin:16px;border-radius:var(--radius)}
        .sgp-welcome h2{font-size:20px;margin-bottom:4px}
        .sgp-welcome p{opacity:0.9;font-size:13px}
        
        /* AI Section */
        .sgp-ai{margin:16px;background:var(--card);border-radius:var(--radius);border:1px solid var(--border);overflow:hidden}
        .sgp-ai-header{padding:16px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:12px}
        .sgp-ai-avatar{width:40px;height:40px;background:linear-gradient(135deg,var(--primary),#ff8533);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:20px}
        .sgp-ai-header h3{font-size:14px}
        .sgp-ai-header p{font-size:11px;color:var(--text2)}
        .sgp-chat{padding:16px;max-height:300px;overflow-y:auto}
        .sgp-msg{margin-bottom:12px;max-width:85%}
        .sgp-msg-ai{background:var(--card2);padding:12px 16px;border-radius:16px 16px 16px 4px;font-size:13px;line-height:1.6}
        .sgp-msg-user{background:var(--primary);margin-left:auto;padding:10px 16px;border-radius:16px 16px 4px 16px;font-size:13px}
        .sgp-quick{display:flex;flex-wrap:wrap;gap:8px;margin-top:12px}
        .sgp-quick-btn{background:var(--card);border:1px solid var(--border);color:var(--text);padding:8px 14px;border-radius:20px;font-size:11px;font-weight:600;cursor:pointer;transition:all 0.2s}
        .sgp-quick-btn:hover{border-color:var(--primary);color:var(--primary)}
        .sgp-ai-input{padding:12px 16px;border-top:1px solid var(--border);display:flex;gap:10px}
        .sgp-ai-input input{flex:1;background:var(--card2);border:1px solid var(--border);border-radius:25px;padding:12px 18px;color:var(--text);font-size:13px}
        .sgp-ai-input input::placeholder{color:var(--text2)}
        .sgp-ai-input button{width:44px;height:44px;background:var(--primary);border:none;border-radius:50%;color:#fff;font-size:18px;cursor:pointer}
        
        /* WhatsApp Direct */
        .sgp-wa-box{margin:0 16px 16px;background:var(--card);border-radius:var(--radius);border:1px solid var(--border);padding:16px}
        .sgp-wa-box h4{font-size:13px;margin-bottom:10px;display:flex;align-items:center;gap:8px}
        .sgp-wa-box textarea{width:100%;background:var(--card2);border:1px solid var(--border);border-radius:12px;padding:12px;color:var(--text);font-size:13px;min-height:70px;resize:none}
        .sgp-wa-box textarea::placeholder{color:var(--text2)}
        
        /* Buttons */
        .sgp-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:12px 24px;border:none;border-radius:12px;font-size:13px;font-weight:600;cursor:pointer;transition:all 0.2s}
        .sgp-btn-primary{background:var(--primary);color:#fff}
        .sgp-btn-primary:hover{box-shadow:0 0 20px var(--primary-glow)}
        .sgp-btn-outline{background:transparent;border:2px solid var(--border);color:var(--text)}
        .sgp-btn-success{background:var(--success);color:#fff}
        .sgp-btn-whatsapp{background:#25D366;color:#fff}
        .sgp-btn-block{width:100%;margin-top:12px}
        
        /* Navigation */
        .sgp-nav{position:fixed;bottom:0;left:0;right:0;background:rgba(15,15,15,0.96);backdrop-filter:blur(12px);border-top:1px solid var(--border);display:flex;justify-content:space-around;padding:8px 0 12px;z-index:100}
        .sgp-nav-btn{display:flex;flex-direction:column;align-items:center;gap:4px;background:none;border:none;color:var(--text2);font-size:10px;cursor:pointer;padding:8px 16px;border-radius:12px;transition:all 0.2s}
        .sgp-nav-btn svg{width:22px;height:22px}
        .sgp-nav-btn.active{color:var(--primary);background:rgba(255,102,0,0.15)}
        
        /* Panels */
        .sgp-panel{display:none;padding:16px}
        .sgp-panel.active{display:block}
        .sgp-panel-title{font-size:20px;font-weight:700;margin-bottom:16px}
        
        /* Cards */
        .sgp-card{background:var(--card);border-radius:var(--radius);border:1px solid var(--border);padding:16px;margin-bottom:12px}
        .sgp-card-title{font-size:14px;font-weight:700;margin-bottom:12px;display:flex;align-items:center;gap:8px}
        .sgp-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:12px}
        .sgp-stat{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:12px}
        .sgp-stat-label{font-size:10px;color:var(--text2);text-transform:uppercase;letter-spacing:.08em}
        .sgp-stat-value{font-size:20px;font-weight:800;color:var(--primary);margin-top:4px}
        
        /* Orders */
        .sgp-order{display:flex;justify-content:space-between;align-items:center;padding:14px 0;border-bottom:1px solid var(--border)}
        .sgp-order:last-child{border:none}
        .sgp-order-num{font-weight:700;font-size:14px}
        .sgp-order-meta{font-size:11px;color:var(--text2);margin-top:2px}
        .sgp-doc-actions{display:flex;gap:6px;justify-content:flex-end;margin-top:8px;flex-wrap:wrap}
        .sgp-product-brand{position:absolute;top:8px;right:8px;width:26px;height:26px;border-radius:6px;background:rgba(0,0,0,0.65);display:flex;align-items:center;justify-content:center;overflow:hidden;border:1px solid rgba(255,255,255,0.18)}
        .sgp-product-brand img{width:100%;height:100%;object-fit:cover}
        .sgp-status{padding:4px 10px;border-radius:20px;font-size:10px;font-weight:700;text-transform:uppercase}
        .sgp-status-pending{background:rgba(255,193,7,0.2);color:var(--warning)}
        .sgp-status-verifying{background:rgba(33,150,243,0.2);color:var(--info)}
        .sgp-status-processing{background:rgba(33,150,243,0.2);color:var(--info)}
        .sgp-status-ready{background:rgba(0,200,83,0.2);color:var(--success)}
        .sgp-status-completed{background:rgba(0,200,83,0.2);color:var(--success)}
        .sgp-status-cancelled{background:rgba(255,82,82,0.2);color:var(--danger)}
        .sgp-btn-sm{padding:6px 12px;font-size:10px;border-radius:8px}
        
        /* Shop */
        .sgp-search{margin-bottom:16px}
        .sgp-search input{width:100%;background:var(--card);border:1px solid var(--border);border-radius:12px;padding:14px 18px 14px 48px;color:var(--text);font-size:14px;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23666'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:16px center;background-size:20px}
        .sgp-search input::placeholder{color:var(--text2)}
        .sgp-filters{display:flex;overflow-x:auto;gap:8px;padding-bottom:12px;margin-bottom:16px}
        .sgp-filters::-webkit-scrollbar{display:none}
        .sgp-filter{flex-shrink:0;padding:10px 18px;background:var(--card);border:1px solid var(--border);border-radius:25px;font-size:12px;font-weight:500;cursor:pointer;white-space:nowrap;color:var(--text);transition:all 0.2s}
        .sgp-filter.active{background:var(--primary);border-color:var(--primary)}
        .sgp-products{display:grid;grid-template-columns:repeat(2,1fr);gap:12px}
        .sgp-product{background:var(--card);border-radius:var(--radius);overflow:hidden;border:1px solid var(--border);cursor:pointer;transition:all 0.2s;position:relative}
        .sgp-product:hover{border-color:var(--primary);transform:translateY(-2px)}
        .sgp-product.hidden{display:none}
        .sgp-product-img{height:100px;background:linear-gradient(135deg,#2a2a2a,#1a1a1a);display:flex;align-items:center;justify-content:center;font-size:40px;overflow:hidden}
        .sgp-product-img img{width:100%;height:100%;object-fit:cover}
        .sgp-product-info{padding:12px}
        .sgp-product-name{font-size:12px;font-weight:600;margin-bottom:4px}
        .sgp-product-price{font-size:18px;font-weight:800;color:var(--primary)}
        .sgp-product-price span{font-size:10px;font-weight:400;color:var(--text2)}
        
        /* Portfolio */
        .sgp-portfolio-tabs{display:flex;overflow-x:auto;gap:8px;margin-bottom:20px}
        .sgp-portfolio-tabs::-webkit-scrollbar{display:none}
        .sgp-portfolio-tab{flex-shrink:0;padding:10px 20px;background:var(--card);border:1px solid var(--border);border-radius:25px;font-size:12px;font-weight:600;cursor:pointer;color:var(--text);transition:all 0.2s}
        .sgp-portfolio-tab.active{background:var(--primary);border-color:var(--primary)}
        .sgp-portfolio-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:12px}
        .sgp-portfolio-item{background:var(--card);border-radius:var(--radius);overflow:hidden;border:1px solid var(--border)}
        .sgp-portfolio-item-img{height:150px;background:var(--card2);overflow:hidden}
        .sgp-portfolio-item-img img{width:100%;height:100%;object-fit:cover}
        .sgp-portfolio-item-img .placeholder{width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:50px;background:linear-gradient(135deg,#2a2a2a,#1a1a1a)}
        .sgp-portfolio-item-info{padding:12px}
        .sgp-portfolio-item-info h3{font-size:13px;font-weight:600;margin-bottom:4px}
        .sgp-portfolio-item-info span{font-size:10px;color:var(--text2)}
        
        /* Contact */
        .sgp-contact-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px}
        .sgp-contact-card{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:20px;background:var(--card);border-radius:var(--radius);border:1px solid var(--border);text-decoration:none;color:var(--text);text-align:center;min-height:120px}
        .sgp-contact-icon{width:50px;height:50px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:24px;margin-bottom:10px}
        .sgp-contact-icon.call{background:rgba(33,150,243,0.2)}
        .sgp-contact-icon.wa{background:rgba(37,211,102,0.2)}
        .sgp-contact-icon.mail{background:rgba(233,30,99,0.2)}
        .sgp-contact-icon.loc{background:rgba(255,152,0,0.2)}
        .sgp-contact-label{font-size:10px;color:var(--text2);margin-bottom:2px}
        .sgp-contact-value{font-weight:600;font-size:12px}
        
        .sgp-bank{background:linear-gradient(135deg,#2f1b0f,#3a2214);border-radius:var(--radius);padding:20px;margin-bottom:16px;border:1px solid rgba(255,102,0,0.4)}
        .sgp-bank h4{color:#ffb27f;font-size:14px;margin-bottom:14px;display:flex;align-items:center;gap:8px}
        .sgp-bank-row{display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid rgba(255,255,255,0.12);font-size:13px}
        .sgp-bank-row:last-child{border:none}
        .sgp-bank-row span:first-child{color:#ffd7bd}
        
        /* Modals */
        .sgp-modal{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.8);z-index:1000;align-items:flex-end;justify-content:center}
        .sgp-modal.show{display:flex}
        .sgp-modal-content{background:var(--card);border-radius:24px 24px 0 0;padding:24px;width:100%;max-width:500px;max-height:90vh;overflow-y:auto}
        .sgp-modal-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px}
        .sgp-modal-header h2{font-size:18px}
        .sgp-modal-close{width:36px;height:36px;background:var(--card2);border:none;border-radius:50%;font-size:20px;cursor:pointer;color:var(--text)}
        
        /* Forms */
        .sgp-form-group{margin-bottom:14px}
        .sgp-form-group label{display:block;font-size:11px;font-weight:600;color:var(--text2);margin-bottom:6px;text-transform:uppercase}
        .sgp-form-group input,.sgp-form-group textarea,.sgp-form-group select{width:100%;padding:14px 16px;background:var(--card2);border:1px solid var(--border);border-radius:12px;color:var(--text);font-size:14px}
        .sgp-form-group input:focus{outline:none;border-color:var(--primary)}
        
        /* Upload */
        .sgp-upload{border:2px dashed var(--border);border-radius:var(--radius);padding:30px;text-align:center;cursor:pointer;transition:all 0.2s}
        .sgp-upload:hover{border-color:var(--primary)}
        .sgp-upload input{display:none}
        .sgp-upload-icon{font-size:40px;margin-bottom:10px}
        .sgp-upload-text{font-size:13px;color:var(--text2)}
        .sgp-upload-name{color:var(--success);margin-top:8px;font-size:12px}
        
        /* Notifications Panel */
        .sgp-notif-panel{position:fixed;top:0;right:-100%;width:100%;max-width:350px;height:100%;background:var(--card);z-index:1001;transition:right 0.3s;overflow-y:auto}
        .sgp-notif-panel.open{right:0}
        .sgp-notif-header{padding:20px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center}
        .sgp-notif-item{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;gap:12px}
        .sgp-notif-icon{width:40px;height:40px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0}
        .sgp-notif-icon.success{background:rgba(0,200,83,0.2)}
        .sgp-notif-icon.info{background:rgba(33,150,243,0.2)}
        .sgp-notif-content p{font-size:13px;margin-bottom:4px}
        .sgp-notif-content span{font-size:11px;color:var(--text2)}
        
        .sgp-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000}
        .sgp-overlay.show{display:block}
        
        /* Loading */
        .sgp-loading{display:inline-block;width:18px;height:18px;border:2px solid rgba(255,255,255,0.3);border-radius:50%;border-top-color:#fff;animation:spin 0.8s linear infinite}
        @keyframes spin{to{transform:rotate(360deg)}}
        
        /* Auth */
        .sgp-auth-btns{display:flex;gap:10px}
        .sgp-auth-btns .sgp-btn{flex:1}
        </style>

        <div class="sgp">
            <!-- Header -->
            <header class="sgp-header">
                <div class="sgp-header-top">
                    <div class="sgp-logo">
                        <div class="sgp-logo-icon">
                            <?php if (!empty($business_logo)): ?>
                                <img src="<?php echo esc_url($business_logo); ?>" alt="<?php echo esc_attr($business_name); ?>">
                            <?php else: ?>
                                SG
                            <?php endif; ?>
                        </div>
                        <div class="sgp-logo-text">
                            <h1><?php echo esc_html($business_name); ?></h1>
                            <p><?php echo esc_html($business_reg_number); ?></p>
                            <p><?php echo esc_html($business_csd_number); ?></p>
                        </div>
                    </div>
                    <div class="sgp-header-actions">
                        <?php if ($logged_in): ?>
                        <button class="sgp-icon-btn" onclick="sgpOpenNotifications()">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            <?php if (count($notifications) > 0): ?><span class="sgp-badge"><?php echo count($notifications); ?></span><?php endif; ?>
                        </button>
                        <?php endif; ?>
                        <button class="sgp-icon-btn" onclick="sgpOpenCart()">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            <span class="sgp-badge" id="cartCount">0</span>
                        </button>
                        <?php if ($logged_in): ?>
                        <button class="sgp-icon-btn" onclick="sgpLogout()" title="Logout">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if (!$logged_in): ?>
                <div class="sgp-auth-btns" style="margin-top:16px">
                    <button class="sgp-btn sgp-btn-primary" onclick="sgpShowAuth('login')">Login</button>
                    <button class="sgp-btn sgp-btn-outline" onclick="sgpShowAuth('register')">Register</button>
                </div>
                <?php endif; ?>
            </header>

            <!-- PANELS -->
            
            <?php if ($logged_in): ?>
            <!-- Dashboard -->
            <div class="sgp-panel active" id="panelHome">
                <div class="sgp-welcome" style="margin:0 0 16px">
                    <h2>👋 Hi <?php echo esc_html($customer['first_name']); ?>!</h2>
                    <p>What would you like to create today?</p>
                </div>
                <h2 class="sgp-panel-title">📦 My Orders</h2>
                <div class="sgp-stats">
                    <div class="sgp-stat"><div class="sgp-stat-label">Invoices</div><div class="sgp-stat-value"><?php echo (int) $invoice_count; ?></div></div>
                    <div class="sgp-stat"><div class="sgp-stat-label">Quotes</div><div class="sgp-stat-value"><?php echo (int) $quote_count; ?></div></div>
                    <div class="sgp-stat"><div class="sgp-stat-label">Pending</div><div class="sgp-stat-value"><?php echo (int) $pending_count; ?></div></div>
                </div>
                
                <div class="sgp-card">
                    <?php if ($customer_orders): foreach ($customer_orders as $o): 
                        $has_proof = !empty($o->payment_proof);
                        $is_quote = strpos((string) $o->quote_number, 'QT-') === 0;
                        $st_class = 'pending';
                        $st_text = $is_quote ? 'Quote Pending' : 'Awaiting Payment';
                        if ($is_quote) {
                            if ($o->status === 'reviewed') { $st_class = 'processing'; $st_text = 'Under Review'; }
                            elseif ($o->status === 'quoted') { $st_class = 'ready'; $st_text = 'Quoted'; }
                            elseif ($o->status === 'accepted') { $st_class = 'completed'; $st_text = 'Accepted'; }
                            elseif ($o->status === 'rejected') { $st_class = 'cancelled'; $st_text = 'Rejected'; }
                        } else {
                            if ($has_proof && $o->status === 'pending') { $st_class = 'verifying'; $st_text = 'Verifying'; }
                            elseif ($o->status === 'processing') { $st_class = 'processing'; $st_text = 'Processing'; }
                            elseif ($o->status === 'ready') { $st_class = 'ready'; $st_text = 'Ready'; }
                            elseif ($o->status === 'completed') { $st_class = 'completed'; $st_text = 'Completed'; }
                        }
                    ?>
                    <div class="sgp-order">
                        <div>
                            <div class="sgp-order-num"><?php echo esc_html($o->quote_number); ?></div>
                            <div class="sgp-order-meta"><?php echo date('d M Y', strtotime($o->created_at)); ?> • R<?php echo number_format($o->total, 2); ?></div>
                        </div>
                        <div style="text-align:right">
                            <span class="sgp-status sgp-status-<?php echo $st_class; ?>"><?php echo $st_text; ?></span>
                            <?php if (!$is_quote && !$has_proof && $o->status === 'pending'): ?>
                            <br><button class="sgp-btn sgp-btn-primary sgp-btn-sm" style="margin-top:6px" onclick="sgpUploadProof(<?php echo $o->id; ?>,'<?php echo esc_js($o->quote_number); ?>')">Upload Proof</button>
                            <?php endif; ?>
                            <div class="sgp-doc-actions">
                                <button class="sgp-btn sgp-btn-outline sgp-btn-sm" onclick="sgpViewDoc('<?php echo esc_js($o->quote_number); ?>')">View</button>
                                <button class="sgp-btn sgp-btn-outline sgp-btn-sm" onclick="sgpDownloadDoc('<?php echo esc_js($o->quote_number); ?>')">Download</button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; else: ?>
                    <p style="text-align:center;padding:30px;color:var(--text2)">No orders yet. Start shopping!</p>
                    <?php endif; ?>
                </div>

                <div class="sgp-card">
                    <div class="sgp-card-title">📄 My Documents</div>
                    <?php if ($customer_orders): foreach ($customer_orders as $o): ?>
                    <div class="sgp-order">
                        <div>
                            <div class="sgp-order-num"><?php echo esc_html($o->quote_number); ?></div>
                            <div class="sgp-order-meta"><?php echo strpos((string) $o->quote_number, 'INV-') === 0 ? 'Invoice' : 'Quotation'; ?> • <?php echo date('d M Y', strtotime($o->created_at)); ?></div>
                        </div>
                        <div class="sgp-doc-actions">
                            <button class="sgp-btn sgp-btn-outline sgp-btn-sm" onclick="sgpViewDoc('<?php echo esc_js($o->quote_number); ?>')">Open</button>
                            <button class="sgp-btn sgp-btn-primary sgp-btn-sm" onclick="sgpDownloadDoc('<?php echo esc_js($o->quote_number); ?>')">PDF / Save</button>
                        </div>
                    </div>
                    <?php endforeach; else: ?>
                    <p style="color:var(--text2)">No documents yet.</p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- AI Assistant -->
            <div class="sgp-panel <?php echo !$logged_in ? 'active' : ''; ?>" id="panelAI">
                <h2 class="sgp-panel-title">🤖 AI Order Assistant</h2>
                <div class="sgp-ai" style="margin:0 0 14px">
                    <div class="sgp-ai-header">
                        <div class="sgp-ai-avatar">🤖</div>
                        <div>
                            <h3>Smart Print & Design Assistant</h3>
                            <p>Guided options + estimated pricing + invoice/quote flow</p>
                        </div>
                    </div>
                    <div class="sgp-chat" id="sgpChat">
                        <div class="sgp-msg sgp-msg-ai">
                            👋 Hi! I guide step-by-step and prepare your order automatically. Tell me what you need.
                            <div class="sgp-quick">
                                <button class="sgp-quick-btn" onclick="sgpSend('wedding welcome board')">💒 Welcome Board</button>
                                <button class="sgp-quick-btn" onclick="sgpSend('business cards')">💳 Cards</button>
                                <button class="sgp-quick-btn" onclick="sgpSend('flyers')">📄 Flyers</button>
                                <button class="sgp-quick-btn" onclick="sgpSend('custom job')">🛠️ Custom Job</button>
                            </div>
                        </div>
                    </div>
                    <div class="sgp-ai-input">
                        <input type="text" id="sgpInput" placeholder="Describe what you need...">
                        <button onclick="sgpSendInput()">➤</button>
                    </div>
                </div>

                <div class="sgp-wa-box" style="margin:0">
                    <h4>📝 Prefer to message directly?</h4>
                    <textarea id="waText" placeholder="Describe what you need..."></textarea>
                    <button class="sgp-btn sgp-btn-whatsapp sgp-btn-block" onclick="sgpSendWA()">💬 Send to WhatsApp</button>
                </div>
            </div>

            <!-- Shop -->
            <div class="sgp-panel" id="panelShop">
                <h2 class="sgp-panel-title">🛍️ Shop</h2>
                
                <div class="sgp-search">
                    <input type="text" id="shopSearch" placeholder="Search products..." oninput="sgpSearch(this.value)">
                </div>
                
                <div class="sgp-filters">
                    <button class="sgp-filter active" data-cat="all">All</button>
                    <?php foreach ($categories as $key => $cat): ?>
                    <button class="sgp-filter" data-cat="<?php echo esc_attr($key); ?>"><?php echo esc_html($cat['emoji'].' '.$cat['name']); ?></button>
                    <?php endforeach; ?>
                </div>
                
                <div class="sgp-products">
                    <?php foreach ($products as $key => $product): 
                        $min = SBHA_Products::get_min_price($product);
                        $icon = $categories[$product['category']]['emoji'] ?? '📦';
                        $image_url = $product['image_url'] ?? '';
                    ?>
                    <div class="sgp-product" data-cat="<?php echo esc_attr($product['category']); ?>" data-name="<?php echo esc_attr(strtolower($product['name'].' '.$product['description'])); ?>" onclick="sgpShowProduct('<?php echo esc_js($key); ?>')">
                        <div class="sgp-product-img">
                            <?php if (!empty($image_url)): ?>
                                <img src="<?php echo esc_attr($image_url); ?>" alt="<?php echo esc_attr($product['name']); ?>">
                            <?php else: ?>
                                <?php echo esc_html($icon); ?>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($business_logo)): ?>
                        <div class="sgp-product-brand"><img src="<?php echo esc_url($business_logo); ?>" alt="Brand"></div>
                        <?php endif; ?>
                        <div class="sgp-product-info">
                            <div class="sgp-product-name"><?php echo esc_html($product['name']); ?></div>
                            <div class="sgp-product-price">R<?php echo number_format($min, 0); ?> <span>from</span></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Portfolio -->
            <div class="sgp-panel" id="panelPortfolio">
                <h2 class="sgp-panel-title">🖼️ Our Work</h2>
                
                <div class="sgp-portfolio-tabs">
                    <?php foreach ($portfolio_cats as $i => $cat): ?>
                    <button class="sgp-portfolio-tab <?php echo $i === 0 ? 'active' : ''; ?>" onclick="sgpFilterPortfolio('<?php echo esc_js($cat); ?>', this)"><?php echo esc_html($cat); ?></button>
                    <?php endforeach; ?>
                </div>
                
                <div class="sgp-portfolio-grid" id="portfolioGrid">
                    <?php foreach ($portfolio_items as $item): ?>
                    <div class="sgp-portfolio-item" data-cat="<?php echo esc_attr($item['category']); ?>">
                        <div class="sgp-portfolio-item-img">
                            <?php if (!empty($item['image'])): ?>
                            <img src="<?php echo esc_url($item['image']); ?>" alt="">
                            <?php else: ?>
                            <div class="placeholder">🎨</div>
                            <?php endif; ?>
                        </div>
                        <div class="sgp-portfolio-item-info">
                            <h3><?php echo esc_html($item['title']); ?></h3>
                            <span><?php echo esc_html($item['category']); ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Track -->
            <div class="sgp-panel" id="panelTrack">
                <h2 class="sgp-panel-title">📍 Track Order</h2>
                <div class="sgp-card">
                    <div class="sgp-form-group">
                        <label>Invoice Number</label>
                        <input type="text" id="trackNum" placeholder="INV-SBH0001">
                    </div>
                    <button class="sgp-btn sgp-btn-primary sgp-btn-block" onclick="sgpTrack()">Track</button>
                    <div id="trackResult"></div>
                </div>
            </div>

            <!-- Contact -->
            <div class="sgp-panel" id="panelContact">
                <h2 class="sgp-panel-title">📞 Contact</h2>
                
                <div class="sgp-contact-grid">
                    <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $phone_display)); ?>" class="sgp-contact-card">
                        <div class="sgp-contact-icon call">📞</div>
                        <span class="sgp-contact-label">Call</span>
                        <span class="sgp-contact-value"><?php echo esc_html($phone_display); ?></span>
                    </a>
                    <a href="https://wa.me/<?php echo $wa; ?>" target="_blank" class="sgp-contact-card">
                        <div class="sgp-contact-icon wa">💬</div>
                        <span class="sgp-contact-label">WhatsApp</span>
                        <span class="sgp-contact-value"><?php echo esc_html($wa_display); ?></span>
                    </a>
                    <a href="mailto:<?php echo $email; ?>" class="sgp-contact-card">
                        <div class="sgp-contact-icon mail">✉️</div>
                        <span class="sgp-contact-label">Email</span>
                        <span class="sgp-contact-value" style="font-size:10px"><?php echo esc_html($email); ?></span>
                    </a>
                    <a href="https://maps.google.com/?q=<?php echo urlencode($address); ?>" target="_blank" class="sgp-contact-card">
                        <div class="sgp-contact-icon loc">📍</div>
                        <span class="sgp-contact-label">Visit</span>
                        <span class="sgp-contact-value"><?php echo esc_html(wp_trim_words($address, 3, '')); ?></span>
                    </a>
                </div>
                
                <div class="sgp-bank">
                    <h4>🏦 Banking Details</h4>
                    <div class="sgp-bank-row"><span>Bank</span><span><?php echo esc_html($bank_name); ?></span></div>
                    <div class="sgp-bank-row"><span>Account</span><span><?php echo esc_html($bank_account_name); ?></span></div>
                    <div class="sgp-bank-row"><span>Number</span><span><?php echo esc_html($bank_account_number); ?></span></div>
                    <div class="sgp-bank-row"><span>Branch</span><span><?php echo esc_html($bank_branch_code); ?></span></div>
                </div>
                
                <button class="sgp-btn sgp-btn-whatsapp sgp-btn-block" onclick="sgpRequestPayLink()">💳 Request Online Payment Link</button>
            </div>

            <?php if ($is_super_admin): ?>
            <div class="sgp-panel" id="panelAdmin">
                <h2 class="sgp-panel-title">🛠️ Super Admin Dashboard</h2>
                <div class="sgp-card">
                    <div class="sgp-card-title">Recent Quotes & Invoices</div>
                    <?php if (!empty($admin_quotes)): ?>
                        <?php foreach ($admin_quotes as $aq): ?>
                            <div class="sgp-order">
                                <div>
                                    <div class="sgp-order-num"><?php echo esc_html($aq->quote_number); ?></div>
                                    <div class="sgp-order-meta"><?php echo esc_html(trim(($aq->first_name ?? '') . ' ' . ($aq->last_name ?? ''))); ?> • <?php echo esc_html($aq->cell_number ?? '-'); ?> • R<?php echo number_format((float) $aq->total, 2); ?></div>
                                </div>
                                <div style="text-align:right;min-width:180px">
                                    <select id="adm_st_<?php echo (int) $aq->id; ?>" style="width:120px;background:var(--card2);color:var(--text);border:1px solid var(--border);border-radius:8px;padding:6px">
                                        <?php foreach (array('pending','reviewed','quoted','accepted','rejected','expired','processing','ready','completed','cancelled') as $st): ?>
                                        <option value="<?php echo esc_attr($st); ?>" <?php selected($aq->status, $st); ?>><?php echo esc_html(ucfirst($st)); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="number" step="0.01" id="adm_total_<?php echo (int) $aq->id; ?>" value="<?php echo esc_attr(number_format((float) $aq->total, 2, '.', '')); ?>" style="width:120px;margin-top:6px;background:var(--card2);color:var(--text);border:1px solid var(--border);border-radius:8px;padding:6px">
                                    <button class="sgp-btn sgp-btn-primary sgp-btn-sm" style="margin-top:6px" onclick="sgpAdminUpdateDocument(<?php echo (int) $aq->id; ?>)">Save</button>
                                    <div class="sgp-doc-actions">
                                        <button class="sgp-btn sgp-btn-outline sgp-btn-sm" onclick="sgpViewDoc('<?php echo esc_js($aq->quote_number); ?>')">View</button>
                                        <button class="sgp-btn sgp-btn-outline sgp-btn-sm" onclick="sgpDownloadDoc('<?php echo esc_js($aq->quote_number); ?>')">Download</button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color:var(--text2)">No records yet.</p>
                    <?php endif; ?>
                </div>

                <div class="sgp-card">
                    <div class="sgp-card-title">Catalog Quick Price Editor</div>
                    <div class="sgp-form-group">
                        <label>Select Product</label>
                        <select id="admProductSelect" onchange="sgpAdminLoadVariations()"></select>
                    </div>
                    <div class="sgp-form-group">
                        <label>Select Variation</label>
                        <select id="admVariationSelect"></select>
                    </div>
                    <div class="sgp-form-group">
                        <label>New Selling Price (R)</label>
                        <input type="number" step="0.01" id="admVariationPrice" placeholder="0.00">
                    </div>
                    <button class="sgp-btn sgp-btn-success sgp-btn-block" onclick="sgpAdminSaveVariation()">💾 Save Price</button>
                    <p style="font-size:11px;color:var(--text2);margin-top:8px">Updates are applied directly inside the plugin catalog without opening WordPress admin screens.</p>
                </div>

                <div class="sgp-card">
                    <div class="sgp-card-title">Branding & Banking</div>
                    <div class="sgp-form-group"><label>Business Name</label><input type="text" id="admBizName" value="<?php echo esc_attr($business_name); ?>"></div>
                    <div class="sgp-form-group"><label>Reg Number</label><input type="text" id="admBizReg" value="<?php echo esc_attr($business_reg_number); ?>"></div>
                    <div class="sgp-form-group"><label>CSD Number</label><input type="text" id="admBizCsd" value="<?php echo esc_attr($business_csd_number); ?>"></div>
                    <div class="sgp-form-group"><label>Logo URL</label><input type="url" id="admBizLogo" value="<?php echo esc_attr($business_logo); ?>" placeholder="https://.../logo.png"></div>
                    <div class="sgp-form-group"><label>Bank</label><input type="text" id="admBankName" value="<?php echo esc_attr($bank_name); ?>"></div>
                    <div class="sgp-form-group"><label>Account Name</label><input type="text" id="admBankAccName" value="<?php echo esc_attr($bank_account_name); ?>"></div>
                    <div class="sgp-form-group"><label>Account Number</label><input type="text" id="admBankAccNo" value="<?php echo esc_attr($bank_account_number); ?>"></div>
                    <div class="sgp-form-group"><label>Branch Code</label><input type="text" id="admBankBranch" value="<?php echo esc_attr($bank_branch_code); ?>"></div>
                    <div class="sgp-form-group"><label>Quote Template URL (optional)</label><input type="url" id="admQuoteTemplate" value="<?php echo esc_attr($quote_template_url); ?>" placeholder="https://.../quote-template.pdf"></div>
                    <div class="sgp-form-group"><label>Invoice Template URL (optional)</label><input type="url" id="admInvoiceTemplate" value="<?php echo esc_attr($invoice_template_url); ?>" placeholder="https://.../invoice-template.pdf"></div>
                    <button class="sgp-btn sgp-btn-success sgp-btn-block" onclick="sgpAdminSaveBranding()">💾 Save Branding</button>
                </div>
            </div>
            <?php endif; ?>

            <!-- Nav -->
            <nav class="sgp-nav">
                <?php if ($logged_in): ?>
                <button class="sgp-nav-btn active" onclick="sgpNav('panelHome',this)"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>Home</button>
                <?php endif; ?>
                <button class="sgp-nav-btn <?php echo !$logged_in?'active':''; ?>" onclick="sgpNav('panelAI',this)"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1.5-3H4.5A2.5 2.5 0 012 14.5v-9A2.5 2.5 0 014.5 3h15A2.5 2.5 0 0122 5.5v9a2.5 2.5 0 01-2.5 2.5h-3L15 20l-.75-3H9.75z"/></svg>AI</button>
                <button class="sgp-nav-btn" onclick="sgpNav('panelShop',this)"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>Shop</button>
                <button class="sgp-nav-btn" onclick="sgpNav('panelPortfolio',this)"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>Portfolio</button>
                <button class="sgp-nav-btn" onclick="sgpNav('panelTrack',this)"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>Track</button>
                <button class="sgp-nav-btn" onclick="sgpNav('panelContact',this)"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>Contact</button>
                <?php if ($is_super_admin): ?>
                <button class="sgp-nav-btn" onclick="sgpNav('panelAdmin',this)"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927C11.469 1.648 13.53 1.648 13.95 2.927l1.034 3.155a1 1 0 00.95.69h3.322c1.345 0 1.904 1.723.817 2.513l-2.688 1.953a1 1 0 00-.364 1.118l1.034 3.154c.42 1.28-1.243 2.326-2.33 1.537l-2.688-1.953a1 1 0 00-1.175 0l-2.688 1.953c-1.087.79-2.75-.257-2.33-1.537l1.034-3.154a1 1 0 00-.364-1.118L3.927 9.285c-1.087-.79-.528-2.513.817-2.513h3.322a1 1 0 00.95-.69l1.034-3.155z"/></svg>Admin</button>
                <?php endif; ?>
            </nav>

            <!-- Notifications Panel -->
            <div class="sgp-overlay" id="overlay" onclick="sgpCloseNotifications()"></div>
            <div class="sgp-notif-panel" id="notifPanel">
                <div class="sgp-notif-header">
                    <h3>🔔 Notifications</h3>
                    <button class="sgp-modal-close" onclick="sgpCloseNotifications()">×</button>
                </div>
                <?php if ($notifications): foreach ($notifications as $n): ?>
                <div class="sgp-notif-item">
                    <div class="sgp-notif-icon <?php echo $n['type']; ?>">
                        <?php echo $n['type'] === 'success' ? '✅' : 'ℹ️'; ?>
                    </div>
                    <div class="sgp-notif-content">
                        <p><?php echo esc_html($n['msg']); ?></p>
                        <span><?php echo date('d M H:i', strtotime($n['time'])); ?></span>
                    </div>
                </div>
                <?php endforeach; else: ?>
                <div style="padding:40px;text-align:center;color:var(--text2)">No notifications</div>
                <?php endif; ?>
            </div>

            <!-- Modals -->
            <div class="sgp-modal" id="productModal"><div class="sgp-modal-content"><div class="sgp-modal-header"><h2 id="prodTitle">Product</h2><button class="sgp-modal-close" onclick="sgpClose('productModal')">×</button></div><div id="prodBody"></div></div></div>
            <div class="sgp-modal" id="cartModal"><div class="sgp-modal-content"><div class="sgp-modal-header"><h2>🛒 Cart</h2><button class="sgp-modal-close" onclick="sgpClose('cartModal')">×</button></div><div id="cartBody"></div></div></div>
            <div class="sgp-modal" id="authModal"><div class="sgp-modal-content"><div class="sgp-modal-header"><h2 id="authTitle">Login</h2><button class="sgp-modal-close" onclick="sgpClose('authModal')">×</button></div><div id="authBody"></div></div></div>
            <div class="sgp-modal" id="uploadModal"><div class="sgp-modal-content"><div class="sgp-modal-header"><h2>📤 Upload Proof</h2><button class="sgp-modal-close" onclick="sgpClose('uploadModal')">×</button></div><div id="uploadBody"></div></div></div>
            <div class="sgp-modal" id="quoteModal"><div class="sgp-modal-content"><div class="sgp-modal-header"><h2>📋 Submit Request</h2><button class="sgp-modal-close" onclick="sgpClose('quoteModal')">×</button></div><div id="quoteBody"></div></div></div>
        </div>

        <script>
        (function(){
            const ajax='<?php echo esc_js($ajax); ?>',nonce='<?php echo esc_js($nonce); ?>',isLoggedIn=<?php echo $logged_in?'true':'false'; ?>,products=<?php echo json_encode($products); ?>,categories=<?php echo json_encode($categories); ?>,wa='<?php echo $wa; ?>',invoices=<?php echo json_encode($customer_orders?:[]); ?>,currentCustomer=<?php echo wp_json_encode($customer ?: array()); ?>,businessName='<?php echo esc_js($business_name); ?>',bankName='<?php echo esc_js($bank_name); ?>',bankAccName='<?php echo esc_js($bank_account_name); ?>',bankAccNo='<?php echo esc_js($bank_account_number); ?>',bankBranch='<?php echo esc_js($bank_branch_code); ?>',businessLogo='<?php echo esc_js($business_logo); ?>';
            let cart=JSON.parse(localStorage.getItem('sgp_cart')||'[]'),aiCtx={},chatHistory=JSON.parse(localStorage.getItem('sgp_chat_history')||'[]');
            updateCartCount();

            function requestJSON(payload,isFormData=false){
                const opts=isFormData?{method:'POST',body:payload}:{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:new URLSearchParams(payload)};
                return fetch(ajax,opts).then(async(r)=>{const txt=await r.text();try{return JSON.parse(txt);}catch(e){throw new Error(txt&&txt.length<220?txt:'Server returned an invalid response. Please refresh and try again.');}});
            }

            function pushChat(role,text){if(!text)return;chatHistory.push({role,text,ts:new Date().toISOString()});if(chatHistory.length>120)chatHistory=chatHistory.slice(-120);localStorage.setItem('sgp_chat_history',JSON.stringify(chatHistory));}
            function compileTranscript(){return chatHistory.map(e=>`${(e.role||'').toUpperCase()}: ${e.text||''}`).join('\n');}
            function syncChatHistory(){if(!isLoggedIn)return;requestJSON({action:'sbha_save_chat_history',nonce,history:JSON.stringify(chatHistory)}).catch(()=>{});}
            function loadChatHistory(){if(!isLoggedIn)return;requestJSON({action:'sbha_get_chat_history',nonce}).then(d=>{if(!d.success||!Array.isArray(d.data.history)||!d.data.history.length)return;const alreadyHas=document.querySelectorAll('#sgpChat .sgp-msg').length>1;if(alreadyHas)return;chatHistory=d.data.history;localStorage.setItem('sgp_chat_history',JSON.stringify(chatHistory));chatHistory.forEach(e=>{if(e.role==='user')addMsg(e.text,'user');if(e.role==='ai')addMsg((e.text||'').replace(/\*\*(.*?)\*\*/g,'<strong>$1</strong>'),'ai');});}).catch(()=>{});}
            loadChatHistory();

            (function initTabFromHash(){
                const hash=(window.location.hash||'').replace('#','');
                if(!hash)return;
                const panel=document.getElementById(hash);
                if(!panel)return;
                const navBtn=[...document.querySelectorAll('.sgp-nav-btn')].find(b=>(b.getAttribute('onclick')||'').includes(`'${hash}'`));
                document.querySelectorAll('.sgp-panel').forEach(p=>p.classList.remove('active'));
                document.querySelectorAll('.sgp-nav-btn').forEach(b=>b.classList.remove('active'));
                panel.classList.add('active');
                if(navBtn)navBtn.classList.add('active');
            })();

            // Navigation
            window.sgpNav=(id,btn)=>{const panel=document.getElementById(id);if(!panel)return;document.querySelectorAll('.sgp-panel').forEach(p=>p.classList.remove('active'));document.querySelectorAll('.sgp-nav-btn').forEach(b=>b.classList.remove('active'));panel.classList.add('active');if(btn)btn.classList.add('active');if(window.history&&window.history.replaceState)window.history.replaceState(null,'',`#${id}`);window.scrollTo({top:0,behavior:'smooth'});};
            window.sgpClose=(id)=>document.getElementById(id).classList.remove('show');
            window.sgpOpen=(id)=>document.getElementById(id).classList.add('show');
            window.sgpViewDoc=(num)=>window.open(`${ajax}?action=sbha_download_document&number=${encodeURIComponent(num)}`,'_blank');
            window.sgpDownloadDoc=(num)=>window.open(`${ajax}?action=sbha_download_document&number=${encodeURIComponent(num)}&download=1`,'_blank');
            window.sgpLogout=()=>{requestJSON({action:'sbha_logout'}).finally(()=>location.reload());};
            
            // Notifications
            window.sgpOpenNotifications=()=>{document.getElementById('overlay').classList.add('show');document.getElementById('notifPanel').classList.add('open');};
            window.sgpCloseNotifications=()=>{document.getElementById('overlay').classList.remove('show');document.getElementById('notifPanel').classList.remove('open');};

            // Search & Filter
            window.sgpSearch=(q)=>{q=q.toLowerCase();document.querySelectorAll('.sgp-product').forEach(p=>p.classList.toggle('hidden',q&&!p.dataset.name.includes(q)));};
            document.querySelectorAll('.sgp-filter').forEach(b=>b.addEventListener('click',function(){const cat=this.dataset.cat;document.querySelectorAll('.sgp-filter').forEach(x=>x.classList.remove('active'));this.classList.add('active');document.querySelectorAll('.sgp-product').forEach(p=>p.classList.toggle('hidden',cat!=='all'&&p.dataset.cat!==cat));}));
            
            // Portfolio filter
            window.sgpFilterPortfolio=(cat,el)=>{document.querySelectorAll('.sgp-portfolio-tab').forEach(t=>t.classList.remove('active'));el.classList.add('active');document.querySelectorAll('.sgp-portfolio-item').forEach(i=>i.style.display=(cat==='All'||i.dataset.cat===cat)?'':'none');};

            // Product Modal
            window.sgpShowProduct=(key)=>{const p=products[key];if(!p)return;document.getElementById('prodTitle').textContent=p.name;let vars='<select id="pVar" onchange="sgpPriceUpd()" style="width:100%;padding:14px;background:var(--card2);border:1px solid var(--border);border-radius:12px;color:var(--text);font-size:14px;margin-bottom:14px">';(p.variations||[]).forEach((v,i)=>vars+=`<option value="${i}" data-price="${v.price}" data-sku="${v.sku||''}">${v.name} - R${v.price}</option>`);vars+='</select>';const brand=businessLogo?`<div style="position:absolute;top:8px;right:8px;width:36px;height:36px;border-radius:8px;overflow:hidden;border:1px solid rgba(255,255,255,.22)"><img src="${businessLogo}" style="width:100%;height:100%;object-fit:cover"></div>`:'';const img=p.image_url?`<div style="position:relative"><img src="${p.image_url}" alt="${p.name}" style="width:100%;height:120px;object-fit:cover;border-radius:16px;margin-bottom:16px">${brand}</div>`:`<div style="position:relative;height:120px;background:linear-gradient(135deg,#2a2a2a,#1a1a1a);border-radius:16px;display:flex;align-items:center;justify-content:center;font-size:50px;margin-bottom:16px">${categories[p.category]?.emoji||'📦'}${brand}</div>`;document.getElementById('prodBody').innerHTML=`${img}<p style="color:var(--text2);font-size:13px;margin-bottom:16px">${p.description||''}</p><label style="display:block;font-size:11px;color:var(--text2);margin-bottom:6px;text-transform:uppercase">Select Option</label>${vars}<label style="display:block;font-size:11px;color:var(--text2);margin-bottom:6px;text-transform:uppercase">Quantity</label><div style="display:flex;align-items:center;gap:16px;margin-bottom:20px"><button onclick="sgpQty(-1)" style="width:44px;height:44px;background:var(--card2);border:1px solid var(--border);border-radius:12px;color:var(--text);font-size:20px;cursor:pointer">−</button><span id="pQty" style="font-size:20px;font-weight:700;min-width:40px;text-align:center">1</span><button onclick="sgpQty(1)" style="width:44px;height:44px;background:var(--card2);border:1px solid var(--border);border-radius:12px;color:var(--text);font-size:20px;cursor:pointer">+</button></div><div style="background:var(--card2);padding:16px;border-radius:12px;display:flex;justify-content:space-between;align-items:center;margin-bottom:16px"><span>Total</span><span id="pTotal" style="font-size:24px;font-weight:800;color:var(--primary)">R${p.variations?.[0]?.price||0}</span></div><button class="sgp-btn sgp-btn-primary sgp-btn-block" onclick="sgpAddCart('${key}')">🛒 Add to Cart</button>`;window.curProd=key;sgpOpen('productModal');};
            window.sgpPriceUpd=()=>{const s=document.getElementById('pVar'),p=parseFloat(s.options[s.selectedIndex].dataset.price),q=parseInt(document.getElementById('pQty').textContent);document.getElementById('pTotal').textContent='R'+(p*q);};
            window.sgpQty=(d)=>{const e=document.getElementById('pQty');let q=parseInt(e.textContent)+d;if(q<1)q=1;e.textContent=q;sgpPriceUpd();};
            window.sgpAddCart=(key)=>{const p=products[key],s=document.getElementById('pVar'),v=p.variations[parseInt(s.value)],q=parseInt(document.getElementById('pQty').textContent);cart.push({id:Date.now(),key,name:p.name,variation:v.name,sku:v.sku||'',price:parseFloat(v.price||0),qty:q,quantity:q,image:p.image_url||''});saveCart();sgpClose('productModal');alert('✅ Added to cart!');};
            function saveCart(){localStorage.setItem('sgp_cart',JSON.stringify(cart));updateCartCount();}
            function updateCartCount(){document.getElementById('cartCount').textContent=cart.length;}

            // Cart
            window.sgpOpenCart=()=>{if(!cart.length){document.getElementById('cartBody').innerHTML='<div style="text-align:center;padding:40px;color:var(--text2)"><div style="font-size:50px;margin-bottom:16px">🛒</div><p>Your cart is empty</p></div>';sgpOpen('cartModal');return;}let h='',t=0;cart.forEach((i,x)=>{const p=products[i.key],ic=categories[p?.category]?.emoji||'📦',s=(parseFloat(i.price)||0)*(parseInt(i.qty)||1);t+=s;const thumb=i.image?`<img src="${i.image}" alt="" style="width:50px;height:50px;border-radius:12px;object-fit:cover">`:`<div style="width:50px;height:50px;background:var(--card2);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:24px">${ic}</div>`;h+=`<div style="display:flex;gap:12px;padding:14px 0;border-bottom:1px solid var(--border)">${thumb}<div style="flex:1"><div style="font-weight:600;font-size:13px">${i.name}</div><div style="font-size:11px;color:var(--text2)">${i.variation} × ${i.qty}${i.sku?` • ${i.sku}`:''}</div><div style="color:var(--primary);font-weight:700;margin-top:4px">R${s.toFixed(2)}</div></div><button onclick="sgpRemove(${x})" style="background:none;border:none;color:var(--danger);font-size:20px;cursor:pointer">×</button></div>`;});h+=`<div style="display:flex;justify-content:space-between;padding:20px 0;font-size:18px;font-weight:700"><span>Total</span><span style="color:var(--primary)">R${t.toFixed(2)}</span></div>`;if(!isLoggedIn){h+=`<div class="sgp-form-group"><label>Name</label><input type="text" id="guestName" placeholder="Your full name"></div><div class="sgp-form-group"><label>WhatsApp</label><input type="tel" id="guestPhone" placeholder="068..."></div><div class="sgp-form-group"><label>Email</label><input type="email" id="guestEmail" placeholder="you@example.com"></div>`;}h+=`<div class="sgp-form-group"><label>Upload your design files (optional)</label><input type="file" id="cartFiles" multiple></div><button class="sgp-btn sgp-btn-whatsapp sgp-btn-block" onclick="sgpSendCartWhatsApp()">💬 Send Order to WhatsApp</button><button class="sgp-btn sgp-btn-success sgp-btn-block" onclick="sgpCheckout()">✅ Checkout</button>`;document.getElementById('cartBody').innerHTML=h;sgpOpen('cartModal');};
            window.sgpRemove=(i)=>{cart.splice(i,1);saveCart();sgpOpenCart();};
            function sgpBuildCartSummary(){let total=0;const lines=cart.map((i,idx)=>{const q=parseInt(i.qty||i.quantity||1),price=parseFloat(i.price||0),line=price*q;total+=line;return `${idx+1}. ${i.name} - ${i.variation||''} x${q} = R${line.toFixed(2)}`;});return{lines,total};}
            window.sgpSendCartWhatsApp=()=>{if(!cart.length)return alert('Cart is empty');const guestName=(document.getElementById('guestName')?.value||'Client').trim()||'Client';const summary=sgpBuildCartSummary();const msg=`Hi ${businessName},\n\nI want to place this order:\n${summary.lines.join('\n')}\n\nTotal: R${summary.total.toFixed(2)}\n\nCustomer: ${guestName}\n\nBanking Details:\nBank: ${bankName}\nAccount Name: ${bankAccName}\nAccount Number: ${bankAccNo}\nBranch Code: ${bankBranch}\nReference: Pending Invoice`;window.open('https://wa.me/'+wa+'?text='+encodeURIComponent(msg),'_blank');};

            // Checkout
            window.sgpCheckout=()=>{let t=0;const items=cart.map(i=>{const q=parseInt(i.qty||i.quantity||1);t+=(parseFloat(i.price)||0)*q;return {...i,quantity:q};});const fd=new FormData();fd.append('action','sbha_create_invoice');fd.append('nonce',nonce);fd.append('items',JSON.stringify(items));fd.append('total',t);if(!isLoggedIn){const gn=(document.getElementById('guestName')?.value||'Client').trim()||'Client';let gp=(document.getElementById('guestPhone')?.value||'').trim();const ge=(document.getElementById('guestEmail')?.value||'').trim();if(!gp)gp='7'+String(Date.now()).slice(-9);fd.append('guest_name',gn);fd.append('guest_phone',gp);fd.append('guest_email',ge);}const files=document.getElementById('cartFiles')?.files||[];for(let i=0;i<files.length;i++){fd.append('order_files[]',files[i]);}requestJSON(fd,true).then(d=>{if(d.success){cart=[];saveCart();const inv=d.data.invoice_number||'';const waMsg=encodeURIComponent(`Hi ${businessName},\nI have placed order ${inv}.\nTotal: R${t.toFixed(2)}\nReference: ${inv}`);document.getElementById('cartBody').innerHTML=`<div style="text-align:center;padding:30px"><div style="font-size:60px;margin-bottom:16px">✅</div><h2 style="margin-bottom:8px">Invoice Created!</h2><p style="font-size:24px;color:var(--primary);font-weight:800;margin-bottom:16px">${inv}</p><p style="color:var(--text2);margin-bottom:20px">Total: R${t.toFixed(2)}</p><div style="background:var(--card2);padding:16px;border-radius:12px;text-align:left;font-size:12px;margin-bottom:20px"><strong>Pay via EFT:</strong><br>${bankName} • ${bankAccName}<br>Acc: ${bankAccNo}<br>Branch: ${bankBranch}<br>Ref: ${inv}</div><a class="sgp-btn sgp-btn-whatsapp sgp-btn-block" href="https://wa.me/${wa}?text=${waMsg}" target="_blank">💬 Send Order to WhatsApp</a>${isLoggedIn?`<button class="sgp-btn sgp-btn-primary sgp-btn-block" onclick="sgpUploadProof(${d.data.order_id},'${inv}')">📤 Upload Payment Proof</button>`:''}<button class="sgp-btn sgp-btn-outline sgp-btn-block" onclick="location.reload()">Done</button></div>`;}else alert(d.data||'Error');}).catch((e)=>alert(e.message||'Request failed.'));};

            // Upload Proof - FIXED FOR MOBILE
            window.sgpUploadProof=(id,num)=>{sgpClose('cartModal');document.getElementById('uploadBody').innerHTML=`<p style="margin-bottom:16px">Invoice: <strong>${num}</strong></p><div class="sgp-upload" onclick="document.getElementById('proofFile').click()"><input type="file" id="proofFile" accept="image/*,.pdf" capture="environment" onchange="sgpFileChosen()"><div class="sgp-upload-icon">📎</div><div class="sgp-upload-text">Tap to upload screenshot or PDF</div><div class="sgp-upload-name" id="proofName"></div></div><input type="hidden" id="proofId" value="${id}"><input type="hidden" id="proofNum" value="${num}"><button class="sgp-btn sgp-btn-success sgp-btn-block" style="margin-top:20px" onclick="sgpSubmitProof()">✅ Submit Proof</button>`;sgpOpen('uploadModal');};
            window.sgpFileChosen=()=>{const f=document.getElementById('proofFile').files[0];if(f)document.getElementById('proofName').textContent='✓ '+f.name;};
            window.sgpSubmitProof=()=>{const f=document.getElementById('proofFile').files[0],id=document.getElementById('proofId').value,num=document.getElementById('proofNum').value;if(!f)return alert('Select a file');const fd=new FormData();fd.append('action','sbha_upload_payment_proof');fd.append('nonce',nonce);fd.append('order_id',id);fd.append('invoice_number',num);fd.append('payment_proof',f);requestJSON(fd,true).then(d=>{if(d.success){document.getElementById('uploadBody').innerHTML=`<div style="text-align:center;padding:30px"><div style="font-size:60px;margin-bottom:16px">✅</div><h2>Uploaded!</h2><p style="color:var(--text2);margin:16px 0">Awaiting verification</p><button class="sgp-btn sgp-btn-primary sgp-btn-block" onclick="location.reload()">Done</button></div>`;}else alert(d.data||'Upload failed');}).catch(e=>alert('Error: '+e.message));};

            // Request Pay Link
            window.sgpRequestPayLink=()=>{if(!isLoggedIn){sgpShowAuth('login');return;}const pending=invoices.filter(i=>i.status==='pending'&&String(i.quote_number||'').startsWith('INV-'));if(!pending.length)return alert('No pending invoices');let h='<p style="margin-bottom:16px;color:var(--text2)">Select invoice:</p>';pending.forEach(i=>{h+=`<div style="display:flex;justify-content:space-between;align-items:center;padding:14px;background:var(--card2);border-radius:12px;margin-bottom:10px;cursor:pointer" onclick="sgpSendPayReq('${i.quote_number}',${i.total})"><strong>${i.quote_number}</strong><span style="color:var(--primary);font-weight:700">R${parseFloat(i.total).toFixed(2)}</span></div>`;});document.getElementById('quoteBody').innerHTML=h;sgpOpen('quoteModal');};
            window.sgpSendPayReq=(n,a)=>{window.open('https://wa.me/'+wa+'?text='+encodeURIComponent(`Hi Switch Graphics! Please send me an online payment link for invoice ${n} (R${parseFloat(a).toFixed(2)}). Thank you!`),'_blank');sgpClose('quoteModal');};

            // AI Chat
            window.sgpSend=(msg)=>{if(!msg.trim())return;addMsg(msg,'user');pushChat('user',msg);syncChatHistory();document.getElementById('sgpInput').value='';const tid='t'+Date.now();addMsg('<span class="sgp-loading"></span>','ai',tid);requestJSON({action:'sbha_ai_chat',nonce,message:msg,context:JSON.stringify(aiCtx)}).then(d=>{document.getElementById(tid)?.remove();if(d.success){aiCtx=d.data.context||{};let h=(d.data.message||'').replace(/\*\*(.*?)\*\*/g,'<strong>$1</strong>');if(d.data.buttons?.length){h+='<div class="sgp-quick">';d.data.buttons.forEach(b=>h+=`<button class="sgp-quick-btn" onclick="sgpSend('${(b.value||'').replace(/'/g,"\\'")}')">${b.text}</button>`);h+='</div>';}addMsg(h,'ai');pushChat('ai',d.data.message||'');syncChatHistory();if(d.data.show_quote_form)sgpShowQuoteForm(d.data.quote_data);}else{addMsg('Sorry, I could not process that. Please try again.','ai');}}).catch((e)=>{document.getElementById(tid)?.remove();addMsg(e.message||'Network error. Please try again.','ai');});};
            window.sgpSendInput=()=>{const i=document.getElementById('sgpInput');if(i.value.trim())sgpSend(i.value);};
            document.getElementById('sgpInput').addEventListener('keypress',e=>{if(e.key==='Enter')sgpSendInput();});
            function addMsg(c,t,id){const ch=document.getElementById('sgpChat'),d=document.createElement('div');d.className='sgp-msg sgp-msg-'+t;if(id)d.id=id;d.innerHTML=c;ch.appendChild(d);ch.scrollTop=ch.scrollHeight;}

            // Quote Form
            window.sgpShowQuoteForm=(data)=>{data=data||{};const defaultDoc=(data.preferred_document==='invoice')?'invoice':'quote';window.quoteDocType=defaultDoc;let h='<div style="background:rgba(0,200,83,0.1);border:1px solid var(--success);border-radius:12px;padding:16px;margin-bottom:20px">';let t=0;if(data.items)data.items.forEach(i=>{const qty=parseInt(i.quantity||i.qty||1),unit=parseFloat(i.unit_price||i.price||0),design=parseFloat(i.design_fee||0),del=parseFloat(i.delivery_fee||0),s=(unit*qty)+design+del;t+=s;h+=`<div style="padding:8px 0;border-bottom:1px solid rgba(0,200,83,0.2)"><div style="display:flex;justify-content:space-between"><span>${i.product_name||''} ${i.variant_name?`- ${i.variant_name}`:''}</span><span>R${s.toFixed(2)}</span></div><div style="font-size:11px;color:var(--text2)">Qty ${qty}${i.variant_sku?` • SKU ${i.variant_sku}`:''}${design?` • Design +R${design}`:''}${del?` • Delivery +R${del}`:''}</div></div>`;});t=parseFloat(data.estimate_total||t||0);h+=`<div style="display:flex;justify-content:space-between;padding:12px 0;font-weight:700;font-size:16px"><span>Estimated Total</span><span>R${t.toFixed(2)}</span></div></div>`;if(data.event_date||data.delivery_location||data.special_notes){h+=`<div style="background:var(--card2);border-radius:12px;padding:12px;margin-bottom:14px;font-size:12px;color:var(--text2)">${data.event_date?`Needed by: ${data.event_date}<br>`:''}${data.delivery_location?`Delivery: ${data.delivery_location}<br>`:''}${data.special_notes?`Notes: ${data.special_notes}`:''}</div>`;}if(!isLoggedIn){h+=`<div class="sgp-form-group"><label>Name (optional)</label><input type="text" id="qName" placeholder="Client"></div><div class="sgp-form-group"><label>WhatsApp (optional)</label><input type="tel" id="qPhone" placeholder="Auto-generated if blank"></div><div class="sgp-form-group"><label>Email (optional)</label><input type="email" id="qEmail"></div><div class="sgp-form-group"><label>Create password (optional, for account)</label><input type="password" id="qPass"></div>`;}else{const dn=`${currentCustomer.first_name||''} ${currentCustomer.last_name||''}`.trim();h+=`<div style="background:var(--card2);border-radius:12px;padding:12px;margin-bottom:12px;font-size:12px">Submitting as <strong>${dn||'Client'}</strong>${currentCustomer.cell_number?` • ${currentCustomer.cell_number}`:''}</div>`;}h+=`<div class="sgp-form-group"><label>Upload your file/design brief (optional, multiple files)</label><input type="file" id="qFiles" multiple></div><div style="display:flex;gap:8px"><button class="sgp-btn ${defaultDoc==='invoice'?'sgp-btn-success':'sgp-btn-outline'}" style="flex:1" onclick="sgpSubmitQuote('invoice')">🧾 Create Invoice</button><button class="sgp-btn ${defaultDoc==='quote'?'sgp-btn-success':'sgp-btn-outline'}" style="flex:1" onclick="sgpSubmitQuote('quote')">📋 Request Quote</button></div>`;window.quoteData=data;document.getElementById('quoteBody').innerHTML=h;sgpOpen('quoteModal');};
            window.sgpSubmitQuote=(docType='quote')=>{const defaultName=`${currentCustomer.first_name||''} ${currentCustomer.last_name||''}`.trim(),defaultPhone=currentCustomer.cell_number||currentCustomer.whatsapp_number||'',defaultEmail=currentCustomer.email||'';const name=(document.getElementById('qName')?.value||defaultName||'Client').trim()||'Client',phone=(document.getElementById('qPhone')?.value||defaultPhone||'').trim(),email=(document.getElementById('qEmail')?.value||defaultEmail||'').trim(),pass=(document.getElementById('qPass')?.value||'').trim();const fd=new FormData();fd.append('action','sbha_submit_quote');fd.append('nonce',nonce);fd.append('name',name);fd.append('phone',phone);fd.append('email',email);fd.append('password',pass);fd.append('document_type',docType);fd.append('quote_data',JSON.stringify(window.quoteData||{}));fd.append('transcript',compileTranscript());const files=document.getElementById('qFiles')?.files||[];for(let i=0;i<files.length;i++){fd.append('quote_files[]',files[i]);}requestJSON(fd,true).then(d=>{if(d.success){syncChatHistory();const ref=d.data.document_number||d.data.quote_number||'';const heading=docType==='invoice'?'Invoice Created!':'Quote Submitted!';let note=docType==='invoice'?'Please pay using the reference and upload proof if you are logged in.':'We have your full brief and transcript. We will review and confirm final pricing.';if(d.data.email_admin_sent===false||d.data.email_customer_sent===false)note+=' Email delivery is pending server mail setup.';const docLink=d.data.document_url?`<a class="sgp-btn sgp-btn-outline sgp-btn-block" target="_blank" href="${d.data.document_url}">📄 Open Document</a>`:'';document.getElementById('quoteBody').innerHTML=`<div style="text-align:center;padding:30px"><div style="font-size:60px;margin-bottom:16px">✅</div><h2>${heading}</h2><p style="font-size:24px;color:var(--primary);font-weight:800;margin:16px 0">${ref}</p><p style="color:var(--text2)">${note}</p>${docLink}<button class="sgp-btn sgp-btn-primary sgp-btn-block" style="margin-top:20px" onclick="location.reload()">Done</button></div>`;}else alert(d.data||'Error');}).catch((e)=>alert(e.message||'Request failed.'));};

            // Track
            window.sgpTrack=()=>{const n=document.getElementById('trackNum').value.trim();if(!n)return alert('Enter invoice number');requestJSON({action:'sbha_track_order',nonce,invoice:n}).then(d=>{if(d.success){const o=d.data||{},status=(o.status||'pending'),st=o.status_label||((status==='pending'&&o.payment_proof)?'Verifying':status.charAt(0).toUpperCase()+status.slice(1)),ref=o.invoice_number||o.quote_number||n,totalRaw=String(o.total||'0').replace(/[^0-9.]/g,''),totalNum=parseFloat(totalRaw||'0');document.getElementById('trackResult').innerHTML=`<div class="sgp-card" style="margin-top:16px"><div style="display:flex;justify-content:space-between;align-items:center"><h3>${ref}</h3><span class="sgp-status sgp-status-${status}">${st}</span></div><p style="color:var(--text2);margin-top:8px">Total: R${totalNum.toFixed(2)}</p>${o.description?`<p style="color:var(--text2);margin-top:8px">${o.description}</p>`:''}<div class="sgp-doc-actions"><button class="sgp-btn sgp-btn-outline sgp-btn-sm" onclick="sgpViewDoc('${ref}')">View</button><button class="sgp-btn sgp-btn-outline sgp-btn-sm" onclick="sgpDownloadDoc('${ref}')">Download</button></div></div>`;}else document.getElementById('trackResult').innerHTML='<p style="text-align:center;padding:20px;color:var(--text2)">Invoice not found</p>';}).catch((e)=>{document.getElementById('trackResult').innerHTML=`<p style="text-align:center;padding:20px;color:var(--text2)">${e.message||'Request failed'}</p>`;});};

            // Super Admin
            window.sgpAdminUpdateStatus=(id)=>{const s=document.getElementById(`adm_st_${id}`);if(!s)return;const status=s.value;requestJSON({action:'sbha_super_admin_update_quote_status',nonce,quote_id:id,status}).then(d=>{if(d.success){alert('Status updated');}else{alert(d.data||'Update failed');}}).catch((e)=>alert(e.message||'Network error'));};
            window.sgpAdminUpdateDocument=(id)=>{const s=document.getElementById(`adm_st_${id}`),t=document.getElementById(`adm_total_${id}`);if(!s||!t)return;requestJSON({action:'sbha_super_admin_update_document',nonce,quote_id:id,status:s.value,total:t.value}).then(d=>{if(d.success){alert('Document updated');}else alert(d.data||'Update failed');}).catch((e)=>alert(e.message||'Network error'));};
            window.sgpAdminLoadVariations=()=>{const productSel=document.getElementById('admProductSelect'),variationSel=document.getElementById('admVariationSelect'),priceInput=document.getElementById('admVariationPrice');if(!productSel||!variationSel)return;const key=productSel.value,p=products[key];variationSel.innerHTML='';if(!p||!Array.isArray(p.variations))return;p.variations.forEach((v,idx)=>{const opt=document.createElement('option');opt.value=idx;opt.textContent=`${v.name} (${v.sku||'NO-SKU'})`;opt.dataset.price=v.price;variationSel.appendChild(opt);});if(variationSel.options.length){priceInput.value=variationSel.options[0].dataset.price||'';}variationSel.onchange=()=>{priceInput.value=variationSel.options[variationSel.selectedIndex]?.dataset.price||'';};};
            window.sgpAdminSaveVariation=()=>{const productSel=document.getElementById('admProductSelect'),variationSel=document.getElementById('admVariationSelect'),priceInput=document.getElementById('admVariationPrice');if(!productSel||!variationSel||!priceInput)return;const product_key=productSel.value,variation_index=parseInt(variationSel.value),price=parseFloat(priceInput.value||0);if(!product_key||Number.isNaN(variation_index)||price<=0)return alert('Select product, variation and valid price.');requestJSON({action:'sbha_super_admin_update_product_variation',nonce,product_key,variation_index,price}).then(d=>{if(d.success){if(products[product_key]?.variations?.[variation_index])products[product_key].variations[variation_index].price=price;alert('Price updated');}else alert(d.data||'Update failed');}).catch((e)=>alert(e.message||'Network error'));};
            window.sgpAdminSaveBranding=()=>{const payload={action:'sbha_super_admin_save_branding',nonce,business_name:document.getElementById('admBizName')?.value||'',business_reg_number:document.getElementById('admBizReg')?.value||'',business_csd_number:document.getElementById('admBizCsd')?.value||'',business_logo:document.getElementById('admBizLogo')?.value||'',bank_name:document.getElementById('admBankName')?.value||'',bank_account_name:document.getElementById('admBankAccName')?.value||'',bank_account_number:document.getElementById('admBankAccNo')?.value||'',bank_branch_code:document.getElementById('admBankBranch')?.value||'',quote_template_url:document.getElementById('admQuoteTemplate')?.value||'',invoice_template_url:document.getElementById('admInvoiceTemplate')?.value||''};requestJSON(payload).then(d=>{if(d.success){alert('Branding saved. Reloading...');location.reload();}else alert(d.data||'Save failed');}).catch((e)=>alert(e.message||'Network error'));};
            (function sgpAdminInit(){const productSel=document.getElementById('admProductSelect');if(!productSel)return;const keys=Object.keys(products).sort((a,b)=>(products[a]?.name||'').localeCompare(products[b]?.name||''));keys.forEach(k=>{const opt=document.createElement('option');opt.value=k;opt.textContent=products[k]?.name||k;productSel.appendChild(opt);});window.sgpAdminLoadVariations();})();

            // WhatsApp
            window.sgpSendWA=()=>{const t=document.getElementById('waText').value.trim();if(!t)return alert('Describe what you need');window.open('https://wa.me/'+wa+'?text='+encodeURIComponent('Hi Switch Graphics!\n\n'+t),'_blank');};

            // Auth
            window.sgpShowAuth=(tab)=>{document.getElementById('authTitle').textContent=tab==='login'?'Login':'Register';document.getElementById('authBody').innerHTML=tab==='login'?`<div class="sgp-form-group"><label>WhatsApp Number</label><input type="tel" id="lPhone"></div><div class="sgp-form-group"><label>Password</label><input type="password" id="lPass"></div><button class="sgp-btn sgp-btn-primary sgp-btn-block" onclick="sgpLogin()">Login</button><p style="text-align:center;margin-top:16px;font-size:13px"><span style="color:var(--primary);cursor:pointer" onclick="sgpShowAuth('register')">Create account</span></p>`:`<div class="sgp-form-group"><label>Name *</label><input type="text" id="rName"></div><div class="sgp-form-group"><label>WhatsApp *</label><input type="tel" id="rPhone"></div><div class="sgp-form-group"><label>Email *</label><input type="email" id="rEmail"></div><div class="sgp-form-group"><label>Password *</label><input type="password" id="rPass"></div><button class="sgp-btn sgp-btn-primary sgp-btn-block" onclick="sgpRegister()">Register</button><p style="text-align:center;margin-top:16px;font-size:13px"><span style="color:var(--primary);cursor:pointer" onclick="sgpShowAuth('login')">Already have account?</span></p>`;sgpOpen('authModal');};
            window.sgpLogin=()=>{const p=document.getElementById('lPhone').value,w=document.getElementById('lPass').value;if(!p||!w)return alert('Enter phone and password');requestJSON({action:'sbha_login',phone:p,password:w}).then(d=>{if(d.success)location.reload();else alert(d.data||'Failed');}).catch((e)=>alert(e.message||'Request failed'));};
            window.sgpRegister=()=>{const n=document.getElementById('rName').value,p=document.getElementById('rPhone').value,e=document.getElementById('rEmail').value,w=document.getElementById('rPass').value;if(!n||!p||!e||!w)return alert('Fill required fields');requestJSON({action:'sbha_register',name:n,phone:p,email:e,password:w}).then(d=>{if(d.success)location.reload();else alert(d.data||'Failed');}).catch((e)=>alert(e.message||'Request failed'));};

            // Close modals on bg click
            ['productModal','cartModal','authModal','uploadModal','quoteModal'].forEach(id=>document.getElementById(id)?.addEventListener('click',e=>{if(e.target.id===id)sgpClose(id);}));
        })();
        </script>
        <?php
        return ob_get_clean();
    }

    private function get_logged_in_customer() {
        global $wpdb;
        $token = $_COOKIE['sbha_token'] ?? '';
        if (!$token) return null;
        $session = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}sbha_sessions WHERE session_token=%s AND expires_at>NOW()", $token));
        if (!$session) return null;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}sbha_customers WHERE id=%d AND status='active'", $session->customer_id), ARRAY_A);
    }
    
    private function get_portfolio_categories($items) {
        $cats = array();
        foreach ($items as $item) {
            if (!empty($item['category']) && !in_array($item['category'], $cats)) {
                $cats[] = $item['category'];
            }
        }
        return $cats ?: array('Brand Design');
    }
    
    private function get_default_portfolio() {
        return array(
            array('title' => 'Gadla Supermarket', 'category' => 'Brand Design', 'image' => ''),
            array('title' => 'Yanga Innovations', 'category' => 'Brand Design', 'image' => ''),
            array('title' => 'Greencor Group', 'category' => 'Brand Design', 'image' => ''),
            array('title' => 'Modern Logo', 'category' => 'Logo Design', 'image' => ''),
            array('title' => 'Tech Startup', 'category' => 'Logo Design', 'image' => ''),
            array('title' => 'E-Commerce Site', 'category' => 'Websites', 'image' => ''),
            array('title' => 'Corporate Cards', 'category' => 'Business Cards', 'image' => ''),
            array('title' => 'Event Flyers', 'category' => 'Flyers & Posters', 'image' => ''),
            array('title' => 'Vehicle Wrap', 'category' => 'Vehicle Branding', 'image' => ''),
        );
    }
}
