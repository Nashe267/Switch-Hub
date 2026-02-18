<?php
/**
 * Switch Graphics Customer Portal v3.0
 * PREMIUM DARK THEME - FULL FEATURED APP
 * 
 * @package SwitchBusinessHub
 * @version 3.0.0
 */

if (!defined('ABSPATH')) exit;

class SBHA_Shortcodes {

    public function __construct() {
        add_shortcode('switch_hub', array($this, 'render'));
    }

    public function render($atts) {
        global $wpdb;

        $ajax = admin_url('admin-ajax.php');
        $nonce = wp_create_nonce('sbha_nonce');

        $customer = $this->get_logged_in_customer();
        $logged_in = !empty($customer);

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-sbha-products.php';
        $products = SBHA_Products::get_all();
        $categories = SBHA_Products::get_categories();
        
        $portfolio_items = get_option('sbha_portfolio_items', $this->get_default_portfolio());
        $portfolio_cats = array_merge(array('All'), $this->get_portfolio_categories($portfolio_items));
        
        $customer_orders = array();
        $customer_quotes = array();
        $notifications = array();
        if ($logged_in) {
            $customer_orders = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}sbha_quotes WHERE customer_id = %d ORDER BY created_at DESC LIMIT 50",
                $customer['id']
            ));
            foreach ($customer_orders as $o) {
                if ($o->status === 'ready') {
                    $notifications[] = array('type' => 'success', 'msg' => "{$o->quote_number} is ready for collection!", 'time' => $o->updated_at ?? $o->created_at);
                } elseif ($o->status === 'processing') {
                    $notifications[] = array('type' => 'info', 'msg' => "{$o->quote_number} is being processed", 'time' => $o->updated_at ?? $o->created_at);
                } elseif ($o->status === 'quoted') {
                    $notifications[] = array('type' => 'info', 'msg' => "Quote {$o->quote_number} has been reviewed — check your dashboard!", 'time' => $o->updated_at ?? $o->created_at);
                }
            }
        }
        
        $wa = '27681474232';
        $wa_display = '068 147 4232';
        $email = 'tinashe@switchgraphics.co.za';
        $biz_name = 'Switch Graphics (Pty) Ltd';
        $reg_number = get_option('sbha_reg_number', '');
        $csd_number = get_option('sbha_csd_number', '');

        ob_start();
        ?>
        <style>
        :root{--bg:#0f0f0f;--card:#1a1a1a;--card2:#242424;--primary:#FF6600;--primary-glow:rgba(255,102,0,0.3);--text:#ffffff;--text2:#b0b0b0;--border:#333;--success:#00C853;--info:#2196F3;--warning:#FFC107;--danger:#FF5252;--radius:16px;--shadow:0 4px 20px rgba(0,0,0,0.4)}
        .sgp{font-family:'Inter',-apple-system,BlinkMacSystemFont,sans-serif;background:var(--bg);min-height:100vh;color:var(--text);padding-bottom:80px;max-width:600px;margin:0 auto}
        .sgp *{box-sizing:border-box;margin:0;padding:0}
        .sgp a{color:var(--primary);text-decoration:none}
        
        .sgp-header{background:linear-gradient(135deg,#1a1a1a,#2a2a2a);padding:16px 20px;border-bottom:1px solid var(--border)}
        .sgp-header-top{display:flex;justify-content:space-between;align-items:center}
        .sgp-logo{display:flex;align-items:center;gap:12px}
        .sgp-logo-icon{width:44px;height:44px;background:var(--primary);border-radius:12px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:16px;color:#fff}
        .sgp-logo-text h1{font-size:15px;font-weight:700;color:var(--primary)}
        .sgp-logo-text p{font-size:9px;color:var(--text2);letter-spacing:0.5px}
        .sgp-logo-text .sgp-reg{font-size:8px;color:var(--text2);opacity:0.7}
        .sgp-header-actions{display:flex;gap:8px;align-items:center}
        .sgp-icon-btn{width:38px;height:38px;border-radius:10px;background:var(--card2);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;color:var(--text);cursor:pointer;position:relative;font-size:16px}
        .sgp-badge{position:absolute;top:-4px;right:-4px;background:var(--primary);color:#fff;font-size:9px;min-width:16px;height:16px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-weight:700}
        
        .sgp-user-bar{display:flex;justify-content:space-between;align-items:center;padding:12px 20px;background:linear-gradient(135deg,var(--primary),#ff8533);margin:12px 16px;border-radius:var(--radius)}
        .sgp-user-bar h2{font-size:16px;font-weight:700}
        .sgp-user-bar p{font-size:11px;opacity:0.9}
        .sgp-user-bar .sgp-logout{background:rgba(0,0,0,0.2);border:none;color:#fff;padding:6px 12px;border-radius:8px;font-size:10px;cursor:pointer;font-weight:600}
        
        .sgp-auth-bar{display:flex;gap:8px;padding:12px 0 0}
        .sgp-auth-bar .sgp-btn{flex:1;font-size:12px;padding:10px}
        
        .sgp-ai{margin:12px 16px;background:var(--card);border-radius:var(--radius);border:1px solid var(--border);overflow:hidden}
        .sgp-ai-header{padding:14px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:12px}
        .sgp-ai-avatar{width:36px;height:36px;background:linear-gradient(135deg,var(--primary),#ff8533);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:18px}
        .sgp-ai-header h3{font-size:13px;color:var(--primary)}
        .sgp-ai-header p{font-size:10px;color:var(--text2)}
        .sgp-chat{padding:12px 16px;max-height:350px;overflow-y:auto;scroll-behavior:smooth}
        .sgp-msg{margin-bottom:10px;max-width:88%}
        .sgp-msg-ai{background:var(--card2);padding:10px 14px;border-radius:14px 14px 14px 4px;font-size:12px;line-height:1.7;white-space:pre-line}
        .sgp-msg-ai strong{color:var(--primary)}
        .sgp-msg-user{background:var(--primary);margin-left:auto;padding:8px 14px;border-radius:14px 14px 4px 14px;font-size:12px}
        .sgp-quick{display:flex;flex-wrap:wrap;gap:6px;margin-top:10px}
        .sgp-quick-btn{background:var(--card);border:1px solid var(--border);color:var(--text);padding:7px 12px;border-radius:20px;font-size:10px;font-weight:600;cursor:pointer;transition:all 0.2s}
        .sgp-quick-btn:hover{border-color:var(--primary);color:var(--primary)}
        .sgp-ai-input{padding:10px 14px;border-top:1px solid var(--border);display:flex;gap:8px}
        .sgp-ai-input input{flex:1;background:var(--card2);border:1px solid var(--border);border-radius:25px;padding:10px 16px;color:var(--text);font-size:12px;outline:none}
        .sgp-ai-input input:focus{border-color:var(--primary)}
        .sgp-ai-input input::placeholder{color:#666}
        .sgp-ai-input button{width:40px;height:40px;background:var(--primary);border:none;border-radius:50%;color:#fff;font-size:16px;cursor:pointer}
        .sgp-ai-input .sgp-attach-btn{width:40px;height:40px;background:var(--card2);border:1px solid var(--border);border-radius:50%;color:var(--text2);font-size:16px;cursor:pointer}

        .sgp-btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:11px 20px;border:none;border-radius:12px;font-size:12px;font-weight:600;cursor:pointer;transition:all 0.2s}
        .sgp-btn-primary{background:var(--primary);color:#fff}
        .sgp-btn-primary:hover{box-shadow:0 0 20px var(--primary-glow)}
        .sgp-btn-outline{background:transparent;border:2px solid var(--border);color:var(--text)}
        .sgp-btn-success{background:var(--success);color:#fff}
        .sgp-btn-whatsapp{background:#25D366;color:#fff}
        .sgp-btn-block{width:100%;margin-top:10px}
        .sgp-btn-sm{padding:5px 10px;font-size:10px;border-radius:8px}

        .sgp-nav{position:fixed;bottom:0;left:50%;transform:translateX(-50%);width:100%;max-width:600px;background:var(--card);border-top:1px solid var(--border);display:flex;justify-content:space-around;padding:6px 0 10px;z-index:100}
        .sgp-nav-btn{display:flex;flex-direction:column;align-items:center;gap:2px;background:none;border:none;color:var(--text2);font-size:9px;cursor:pointer;padding:6px 10px;border-radius:10px;transition:all 0.2s}
        .sgp-nav-btn svg{width:20px;height:20px}
        .sgp-nav-btn.active{color:var(--primary);background:rgba(255,102,0,0.1)}

        .sgp-panel{display:none;padding:12px 16px}
        .sgp-panel.active{display:block}
        .sgp-panel-title{font-size:18px;font-weight:700;margin-bottom:14px;color:var(--primary)}

        .sgp-card{background:var(--card);border-radius:var(--radius);border:1px solid var(--border);padding:14px;margin-bottom:12px}
        .sgp-card-title{font-size:13px;font-weight:700;margin-bottom:10px;display:flex;align-items:center;gap:8px}

        .sgp-order{padding:12px 0;border-bottom:1px solid var(--border)}
        .sgp-order:last-child{border:none}
        .sgp-order-top{display:flex;justify-content:space-between;align-items:center}
        .sgp-order-num{font-weight:700;font-size:13px}
        .sgp-order-meta{font-size:10px;color:var(--text2);margin-top:2px}
        .sgp-order-items{font-size:10px;color:var(--text2);margin-top:6px;padding:8px;background:var(--card2);border-radius:8px}
        .sgp-status{padding:3px 8px;border-radius:20px;font-size:9px;font-weight:700;text-transform:uppercase}
        .sgp-status-pending{background:rgba(255,193,7,0.2);color:var(--warning)}
        .sgp-status-verifying{background:rgba(33,150,243,0.2);color:var(--info)}
        .sgp-status-processing{background:rgba(33,150,243,0.2);color:var(--info)}
        .sgp-status-quoted{background:rgba(156,39,176,0.2);color:#CE93D8}
        .sgp-status-ready{background:rgba(0,200,83,0.2);color:var(--success)}
        .sgp-status-completed{background:rgba(0,200,83,0.2);color:var(--success)}
        .sgp-status-cancelled{background:rgba(255,82,82,0.2);color:var(--danger)}

        .sgp-search{margin-bottom:12px}
        .sgp-search input{width:100%;background:var(--card);border:1px solid var(--border);border-radius:12px;padding:12px 16px 12px 44px;color:var(--text);font-size:13px;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23666'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:14px center;background-size:18px;outline:none}
        .sgp-search input:focus{border-color:var(--primary)}
        .sgp-search input::placeholder{color:#666}
        .sgp-filters{display:flex;overflow-x:auto;gap:6px;padding-bottom:10px;margin-bottom:12px;-webkit-overflow-scrolling:touch}
        .sgp-filters::-webkit-scrollbar{display:none}
        .sgp-filter{flex-shrink:0;padding:8px 14px;background:var(--card);border:1px solid var(--border);border-radius:25px;font-size:11px;font-weight:500;cursor:pointer;white-space:nowrap;color:var(--text);transition:all 0.2s}
        .sgp-filter.active{background:var(--primary);border-color:var(--primary)}
        .sgp-products{display:grid;grid-template-columns:repeat(2,1fr);gap:10px}
        .sgp-product{background:var(--card);border-radius:var(--radius);overflow:hidden;border:1px solid var(--border);cursor:pointer;transition:all 0.2s}
        .sgp-product:hover{border-color:var(--primary);transform:translateY(-2px)}
        .sgp-product.hidden{display:none}
        .sgp-product-img{height:90px;background:linear-gradient(135deg,#2a2a2a,#1a1a1a);display:flex;align-items:center;justify-content:center;font-size:36px;overflow:hidden;position:relative}
        .sgp-product-img img{width:100%;height:100%;object-fit:cover}
        .sgp-product-info{padding:10px}
        .sgp-product-name{font-size:11px;font-weight:600;margin-bottom:3px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
        .sgp-product-price{font-size:15px;font-weight:800;color:var(--primary)}
        .sgp-product-price span{font-size:9px;font-weight:400;color:var(--text2)}

        .sgp-portfolio-tabs{display:flex;overflow-x:auto;gap:6px;margin-bottom:16px;-webkit-overflow-scrolling:touch}
        .sgp-portfolio-tabs::-webkit-scrollbar{display:none}
        .sgp-portfolio-tab{flex-shrink:0;padding:8px 16px;background:var(--card);border:1px solid var(--border);border-radius:25px;font-size:11px;font-weight:600;cursor:pointer;color:var(--text);transition:all 0.2s}
        .sgp-portfolio-tab.active{background:var(--primary);border-color:var(--primary)}
        .sgp-portfolio-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:10px}
        .sgp-portfolio-item{background:var(--card);border-radius:var(--radius);overflow:hidden;border:1px solid var(--border)}
        .sgp-portfolio-item-img{height:130px;background:var(--card2);overflow:hidden}
        .sgp-portfolio-item-img img{width:100%;height:100%;object-fit:cover}
        .sgp-portfolio-item-img .placeholder{width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:40px;background:linear-gradient(135deg,#2a2a2a,#1a1a1a)}
        .sgp-portfolio-item-info{padding:10px}
        .sgp-portfolio-item-info h3{font-size:12px;font-weight:600;margin-bottom:2px}
        .sgp-portfolio-item-info span{font-size:10px;color:var(--text2)}

        .sgp-contact-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px}
        .sgp-contact-card{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:16px;background:var(--card);border-radius:var(--radius);border:1px solid var(--border);text-decoration:none;color:var(--text);text-align:center;min-height:100px;transition:all 0.2s}
        .sgp-contact-card:hover{border-color:var(--primary)}
        .sgp-contact-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;margin-bottom:8px}
        .sgp-contact-icon.call{background:rgba(33,150,243,0.2)}
        .sgp-contact-icon.wa{background:rgba(37,211,102,0.2)}
        .sgp-contact-icon.mail{background:rgba(233,30,99,0.2)}
        .sgp-contact-icon.loc{background:rgba(255,152,0,0.2)}
        .sgp-contact-label{font-size:9px;color:var(--text2);margin-bottom:2px;text-transform:uppercase}
        .sgp-contact-value{font-weight:600;font-size:11px}

        .sgp-bank{background:linear-gradient(135deg,#1e2a3a,#162030);border-radius:var(--radius);padding:16px;margin-bottom:14px;border:1px solid rgba(33,150,243,0.3)}
        .sgp-bank h4{color:#64B5F6;font-size:13px;margin-bottom:12px;display:flex;align-items:center;gap:8px}
        .sgp-bank-row{display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid rgba(255,255,255,0.08);font-size:12px}
        .sgp-bank-row:last-child{border:none}
        .sgp-bank-row span:first-child{color:#90CAF9}
        .sgp-bank-row span:last-child{font-weight:600;color:#fff}

        .sgp-modal{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.85);z-index:1000;align-items:flex-end;justify-content:center}
        .sgp-modal.show{display:flex}
        .sgp-modal-content{background:var(--card);border-radius:20px 20px 0 0;padding:20px;width:100%;max-width:500px;max-height:90vh;overflow-y:auto}
        .sgp-modal-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px}
        .sgp-modal-header h2{font-size:16px;color:var(--primary)}
        .sgp-modal-close{width:32px;height:32px;background:var(--card2);border:none;border-radius:50%;font-size:18px;cursor:pointer;color:var(--text);display:flex;align-items:center;justify-content:center}

        .sgp-form-group{margin-bottom:12px}
        .sgp-form-group label{display:block;font-size:10px;font-weight:600;color:var(--text2);margin-bottom:4px;text-transform:uppercase}
        .sgp-form-group input,.sgp-form-group textarea,.sgp-form-group select{width:100%;padding:12px 14px;background:var(--card2);border:1px solid var(--border);border-radius:10px;color:var(--text);font-size:13px;outline:none}
        .sgp-form-group input:focus,.sgp-form-group textarea:focus{border-color:var(--primary)}

        .sgp-upload{border:2px dashed var(--border);border-radius:var(--radius);padding:24px;text-align:center;cursor:pointer;transition:all 0.2s}
        .sgp-upload:hover{border-color:var(--primary)}
        .sgp-upload input{display:none}
        .sgp-upload-icon{font-size:32px;margin-bottom:8px}
        .sgp-upload-text{font-size:11px;color:var(--text2)}
        .sgp-upload-name{color:var(--success);margin-top:6px;font-size:11px}

        .sgp-notif-panel{position:fixed;top:0;right:-100%;width:100%;max-width:320px;height:100%;background:var(--card);z-index:1001;transition:right 0.3s;overflow-y:auto}
        .sgp-notif-panel.open{right:0}
        .sgp-notif-header{padding:16px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center}
        .sgp-notif-item{padding:12px 16px;border-bottom:1px solid var(--border);display:flex;gap:10px}
        .sgp-notif-icon{width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0}
        .sgp-notif-icon.success{background:rgba(0,200,83,0.2)}
        .sgp-notif-icon.info{background:rgba(33,150,243,0.2)}
        .sgp-notif-content p{font-size:12px;margin-bottom:2px}
        .sgp-notif-content span{font-size:10px;color:var(--text2)}

        .sgp-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000}
        .sgp-overlay.show{display:block}

        .sgp-loading{display:inline-block;width:16px;height:16px;border:2px solid rgba(255,255,255,0.3);border-radius:50%;border-top-color:#fff;animation:sgp-spin 0.8s linear infinite}
        @keyframes sgp-spin{to{transform:rotate(360deg)}}

        .sgp-tab-bar{display:flex;gap:0;margin-bottom:14px;background:var(--card);border-radius:12px;border:1px solid var(--border);overflow:hidden}
        .sgp-tab{flex:1;padding:10px;text-align:center;font-size:11px;font-weight:600;cursor:pointer;transition:all 0.2s;color:var(--text2);border:none;background:none}
        .sgp-tab.active{background:var(--primary);color:#fff}

        .sgp-empty{text-align:center;padding:30px;color:var(--text2)}
        .sgp-empty-icon{font-size:40px;margin-bottom:10px}
        .sgp-empty p{font-size:12px}
        </style>

        <div class="sgp">
            <header class="sgp-header">
                <div class="sgp-header-top">
                    <div class="sgp-logo">
                        <div class="sgp-logo-icon">SG</div>
                        <div class="sgp-logo-text">
                            <h1><?php echo esc_html($biz_name); ?></h1>
                            <p>Design | Innovation & Identity</p>
                            <?php if ($reg_number || $csd_number): ?>
                            <span class="sgp-reg">
                                <?php if ($reg_number): ?>Reg: <?php echo esc_html($reg_number); ?><?php endif; ?>
                                <?php if ($reg_number && $csd_number): ?> | <?php endif; ?>
                                <?php if ($csd_number): ?>CSD: <?php echo esc_html($csd_number); ?><?php endif; ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="sgp-header-actions">
                        <?php if ($logged_in): ?>
                        <button class="sgp-icon-btn" onclick="sgpOpenNotifications()" title="Notifications">
                            🔔
                            <?php if (count($notifications) > 0): ?><span class="sgp-badge"><?php echo count($notifications); ?></span><?php endif; ?>
                        </button>
                        <?php endif; ?>
                        <button class="sgp-icon-btn" onclick="sgpOpenCart()" title="Cart">
                            🛒
                            <span class="sgp-badge" id="cartCount">0</span>
                        </button>
                    </div>
                </div>
                <?php if (!$logged_in): ?>
                <div class="sgp-auth-bar">
                    <button class="sgp-btn sgp-btn-primary" onclick="sgpShowAuth('login')">Login</button>
                    <button class="sgp-btn sgp-btn-outline" onclick="sgpShowAuth('register')">Register</button>
                </div>
                <?php endif; ?>
            </header>

            <?php if ($logged_in): ?>
            <div class="sgp-user-bar">
                <div>
                    <h2>Hi <?php echo esc_html($customer['first_name']); ?>!</h2>
                    <p>What would you like to create today?</p>
                </div>
                <button class="sgp-logout" onclick="sgpLogout()">Logout</button>
            </div>
            <?php endif; ?>

            <!-- AI Chat - ALWAYS VISIBLE -->
            <div class="sgp-ai">
                <div class="sgp-ai-header">
                    <div class="sgp-ai-avatar">🤖</div>
                    <div>
                        <h3>Get a Custom Quote</h3>
                        <p>Chat with our AI assistant for instant pricing</p>
                    </div>
                </div>
                <div class="sgp-chat" id="sgpChat">
                    <div class="sgp-msg sgp-msg-ai">
                        Hi there! Welcome to <strong>Switch Graphics</strong>. I'm here to help you find exactly what you need and get you a quote.

What are you looking for today? You can tell me in your own words, or pick a category below:
                        <div class="sgp-quick">
                            <button class="sgp-quick-btn" onclick="sgpSend('I need business cards')">💳 Business Cards</button>
                            <button class="sgp-quick-btn" onclick="sgpSend('I need flyers')">📄 Flyers</button>
                            <button class="sgp-quick-btn" onclick="sgpSend('I need signage')">🪧 Signage</button>
                            <button class="sgp-quick-btn" onclick="sgpSend('I need banners')">🎪 Banners</button>
                            <button class="sgp-quick-btn" onclick="sgpSend('I need wedding items')">💒 Wedding</button>
                            <button class="sgp-quick-btn" onclick="sgpSend('I need t-shirts printed')">👕 Clothing</button>
                            <button class="sgp-quick-btn" onclick="sgpSend('I need a design')">🎨 Design</button>
                            <button class="sgp-quick-btn" onclick="sgpSend('I need a website')">🌐 Website</button>
                        </div>
                    </div>
                </div>
                <div class="sgp-ai-input">
                    <input type="text" id="sgpInput" placeholder="Describe what you need..." autocomplete="off">
                    <button onclick="sgpSendInput()">➤</button>
                </div>
            </div>

            <!-- PANELS -->

            <?php if ($logged_in): ?>
            <!-- Dashboard -->
            <div class="sgp-panel active" id="panelHome">
                <h2 class="sgp-panel-title">My Dashboard</h2>
                
                <div class="sgp-tab-bar">
                    <button class="sgp-tab active" onclick="sgpDashTab('orders',this)">Orders & Invoices</button>
                    <button class="sgp-tab" onclick="sgpDashTab('quotes',this)">Quotes</button>
                </div>
                
                <div id="dashOrders">
                    <div class="sgp-card">
                        <?php 
                        $invoices = array_filter((array)$customer_orders, function($o) { return ($o->quote_type ?? '') === 'invoice'; });
                        if ($invoices): foreach ($invoices as $o): 
                            $has_proof = !empty($o->payment_proof);
                            $st_class = 'pending'; $st_text = 'Awaiting Payment';
                            if ($has_proof && $o->status === 'pending') { $st_class = 'verifying'; $st_text = 'Verifying'; }
                            elseif ($o->status === 'processing') { $st_class = 'processing'; $st_text = 'Processing'; }
                            elseif ($o->status === 'ready') { $st_class = 'ready'; $st_text = 'Ready'; }
                            elseif ($o->status === 'completed') { $st_class = 'completed'; $st_text = 'Completed'; }
                            elseif ($o->status === 'cancelled') { $st_class = 'cancelled'; $st_text = 'Cancelled'; }
                            $items = json_decode($o->items ?? '[]', true);
                            $items_text = '';
                            if ($items) { foreach (array_slice($items, 0, 3) as $it) { $items_text .= ($it['variation'] ?? $it['variant_name'] ?? $it['name'] ?? '') . ', '; } $items_text = rtrim($items_text, ', '); }
                        ?>
                        <div class="sgp-order">
                            <div class="sgp-order-top">
                                <div>
                                    <div class="sgp-order-num"><?php echo esc_html($o->quote_number); ?></div>
                                    <div class="sgp-order-meta"><?php echo date('d M Y', strtotime($o->created_at)); ?> • R<?php echo number_format($o->total, 2); ?></div>
                                </div>
                                <div style="text-align:right">
                                    <span class="sgp-status sgp-status-<?php echo $st_class; ?>"><?php echo $st_text; ?></span>
                                </div>
                            </div>
                            <?php if ($items_text): ?>
                            <div class="sgp-order-items"><?php echo esc_html($items_text); ?></div>
                            <?php endif; ?>
                            <?php if (!$has_proof && $o->status === 'pending'): ?>
                            <button class="sgp-btn sgp-btn-primary sgp-btn-sm" style="margin-top:8px" onclick="sgpUploadProof(<?php echo $o->id; ?>,'<?php echo esc_js($o->quote_number); ?>')">📤 Upload Payment Proof</button>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; else: ?>
                        <div class="sgp-empty"><div class="sgp-empty-icon">📦</div><p>No orders yet. Start shopping!</p></div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div id="dashQuotes" style="display:none">
                    <div class="sgp-card">
                        <?php 
                        $quotes_only = array_filter((array)$customer_orders, function($o) { return ($o->quote_type ?? '') !== 'invoice'; });
                        if ($quotes_only): foreach ($quotes_only as $o): 
                            $st_class = $o->status; $st_text = ucfirst($o->status);
                            if ($o->status === 'pending') { $st_text = 'Under Review'; }
                            elseif ($o->status === 'quoted') { $st_text = 'Quote Ready'; }
                            $items = json_decode($o->items ?? '[]', true);
                            $items_text = '';
                            if ($items) { foreach (array_slice($items, 0, 3) as $it) { $items_text .= ($it['variant_name'] ?? $it['product_name'] ?? '') . ', '; } $items_text = rtrim($items_text, ', '); }
                        ?>
                        <div class="sgp-order">
                            <div class="sgp-order-top">
                                <div>
                                    <div class="sgp-order-num"><?php echo esc_html($o->quote_number); ?></div>
                                    <div class="sgp-order-meta"><?php echo date('d M Y', strtotime($o->created_at)); ?> • Est. R<?php echo number_format($o->total, 2); ?></div>
                                </div>
                                <span class="sgp-status sgp-status-<?php echo $st_class; ?>"><?php echo $st_text; ?></span>
                            </div>
                            <?php if ($items_text): ?>
                            <div class="sgp-order-items"><?php echo esc_html($items_text); ?></div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; else: ?>
                        <div class="sgp-empty"><div class="sgp-empty-icon">📋</div><p>No quotes yet. Chat with our AI to get started!</p></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Shop -->
            <div class="sgp-panel <?php echo !$logged_in ? 'active' : ''; ?>" id="panelShop">
                <h2 class="sgp-panel-title">Shop</h2>
                
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
                        $img = $product['image'] ?? '';
                        $custom_imgs = get_option('sbha_product_images', array());
                        if (!empty($custom_imgs[$key])) $img = $custom_imgs[$key];
                    ?>
                    <div class="sgp-product" data-cat="<?php echo esc_attr($product['category']); ?>" data-name="<?php echo esc_attr(strtolower($product['name'].' '.($product['description']??''))); ?>" onclick="sgpShowProduct('<?php echo esc_js($key); ?>')">
                        <div class="sgp-product-img">
                            <?php if ($img): ?>
                            <img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($product['name']); ?>" loading="lazy">
                            <?php else: ?>
                            <?php echo $icon; ?>
                            <?php endif; ?>
                        </div>
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
                <h2 class="sgp-panel-title">Our Work</h2>
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
                <h2 class="sgp-panel-title">Track Order</h2>
                <div class="sgp-card">
                    <div class="sgp-form-group">
                        <label>Invoice Number</label>
                        <input type="text" id="trackNum" placeholder="e.g. SBH0001 or INV-SBH0001">
                    </div>
                    <button class="sgp-btn sgp-btn-primary sgp-btn-block" onclick="sgpTrack()">🔍 Track</button>
                    <div id="trackResult"></div>
                </div>
            </div>

            <!-- Contact -->
            <div class="sgp-panel" id="panelContact">
                <h2 class="sgp-panel-title">Contact Us</h2>
                
                <div class="sgp-contact-grid">
                    <a href="tel:+27681474232" class="sgp-contact-card">
                        <div class="sgp-contact-icon call">📞</div>
                        <span class="sgp-contact-label">Call Us</span>
                        <span class="sgp-contact-value"><?php echo $wa_display; ?></span>
                    </a>
                    <a href="https://wa.me/<?php echo $wa; ?>" target="_blank" class="sgp-contact-card">
                        <div class="sgp-contact-icon wa">💬</div>
                        <span class="sgp-contact-label">WhatsApp</span>
                        <span class="sgp-contact-value"><?php echo $wa_display; ?></span>
                    </a>
                    <a href="mailto:<?php echo $email; ?>" class="sgp-contact-card">
                        <div class="sgp-contact-icon mail">✉️</div>
                        <span class="sgp-contact-label">Email</span>
                        <span class="sgp-contact-value" style="font-size:9px"><?php echo $email; ?></span>
                    </a>
                    <a href="https://maps.google.com/?q=16+Harding+Street+Newcastle+2940" target="_blank" class="sgp-contact-card">
                        <div class="sgp-contact-icon loc">📍</div>
                        <span class="sgp-contact-label">Visit Us</span>
                        <span class="sgp-contact-value">16 Harding St</span>
                    </a>
                </div>
                
                <div class="sgp-bank">
                    <h4>🏦 Banking Details</h4>
                    <div class="sgp-bank-row"><span>Bank</span><span>FNB / RMB</span></div>
                    <div class="sgp-bank-row"><span>Account Name</span><span>Switch Graphics (Pty) Ltd</span></div>
                    <div class="sgp-bank-row"><span>Account Number</span><span>630 842 187 18</span></div>
                    <div class="sgp-bank-row"><span>Branch Code</span><span>250 655</span></div>
                    <div class="sgp-bank-row"><span>Reference</span><span>Your Invoice No.</span></div>
                </div>
                
                <a href="https://wa.me/<?php echo $wa; ?>?text=<?php echo urlencode('Hi Switch Graphics! I need help with my order.'); ?>" target="_blank" class="sgp-btn sgp-btn-whatsapp sgp-btn-block">💬 Chat on WhatsApp</a>
                <button class="sgp-btn sgp-btn-outline sgp-btn-block" onclick="sgpRequestPayLink()" style="margin-top:8px">💳 Request Payment Link</button>
            </div>

            <!-- Nav -->
            <nav class="sgp-nav">
                <?php if ($logged_in): ?>
                <button class="sgp-nav-btn active" onclick="sgpNav('panelHome',this)"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>Home</button>
                <?php endif; ?>
                <button class="sgp-nav-btn <?php echo !$logged_in?'active':''; ?>" onclick="sgpNav('panelShop',this)"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>Shop</button>
                <button class="sgp-nav-btn" onclick="sgpNav('panelPortfolio',this)"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>Portfolio</button>
                <button class="sgp-nav-btn" onclick="sgpNav('panelTrack',this)"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>Track</button>
                <button class="sgp-nav-btn" onclick="sgpNav('panelContact',this)"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>Contact</button>
            </nav>

            <!-- Notifications Panel -->
            <div class="sgp-overlay" id="overlay" onclick="sgpCloseNotifications()"></div>
            <div class="sgp-notif-panel" id="notifPanel">
                <div class="sgp-notif-header">
                    <h3 style="color:var(--primary)">Notifications</h3>
                    <button class="sgp-modal-close" onclick="sgpCloseNotifications()">×</button>
                </div>
                <?php if ($notifications): foreach ($notifications as $n): ?>
                <div class="sgp-notif-item">
                    <div class="sgp-notif-icon <?php echo $n['type']; ?>"><?php echo $n['type'] === 'success' ? '✅' : 'ℹ️'; ?></div>
                    <div class="sgp-notif-content"><p><?php echo esc_html($n['msg']); ?></p><span><?php echo date('d M H:i', strtotime($n['time'])); ?></span></div>
                </div>
                <?php endforeach; else: ?>
                <div style="padding:30px;text-align:center;color:var(--text2)">No new notifications</div>
                <?php endif; ?>
            </div>

            <!-- Modals -->
            <div class="sgp-modal" id="productModal"><div class="sgp-modal-content"><div class="sgp-modal-header"><h2 id="prodTitle">Product</h2><button class="sgp-modal-close" onclick="sgpClose('productModal')">×</button></div><div id="prodBody"></div></div></div>
            <div class="sgp-modal" id="cartModal"><div class="sgp-modal-content"><div class="sgp-modal-header"><h2>🛒 Cart</h2><button class="sgp-modal-close" onclick="sgpClose('cartModal')">×</button></div><div id="cartBody"></div></div></div>
            <div class="sgp-modal" id="authModal"><div class="sgp-modal-content"><div class="sgp-modal-header"><h2 id="authTitle">Login</h2><button class="sgp-modal-close" onclick="sgpClose('authModal')">×</button></div><div id="authBody"></div></div></div>
            <div class="sgp-modal" id="uploadModal"><div class="sgp-modal-content"><div class="sgp-modal-header"><h2>Upload Proof</h2><button class="sgp-modal-close" onclick="sgpClose('uploadModal')">×</button></div><div id="uploadBody"></div></div></div>
            <div class="sgp-modal" id="quoteModal"><div class="sgp-modal-content"><div class="sgp-modal-header"><h2>Submit Quote</h2><button class="sgp-modal-close" onclick="sgpClose('quoteModal')">×</button></div><div id="quoteBody"></div></div></div>
        </div>

        <script>
        (function(){
            const ajax='<?php echo esc_js($ajax); ?>',nonce='<?php echo esc_js($nonce); ?>',isLoggedIn=<?php echo $logged_in?'true':'false'; ?>,products=<?php echo json_encode($products); ?>,categories=<?php echo json_encode($categories); ?>,wa='<?php echo $wa; ?>';
            let cart=JSON.parse(localStorage.getItem('sgp_cart')||'[]'),aiCtx={};
            const productImages=<?php echo json_encode(get_option('sbha_product_images', array())); ?>;
            updateCartCount();

            window.sgpNav=(id,btn)=>{document.querySelectorAll('.sgp-panel').forEach(p=>p.classList.remove('active'));document.querySelectorAll('.sgp-nav-btn').forEach(b=>b.classList.remove('active'));document.getElementById(id)?.classList.add('active');if(btn)btn.classList.add('active');};
            window.sgpClose=(id)=>document.getElementById(id)?.classList.remove('show');
            window.sgpOpen=(id)=>document.getElementById(id)?.classList.add('show');
            window.sgpOpenNotifications=()=>{document.getElementById('overlay')?.classList.add('show');document.getElementById('notifPanel')?.classList.add('open');};
            window.sgpCloseNotifications=()=>{document.getElementById('overlay')?.classList.remove('show');document.getElementById('notifPanel')?.classList.remove('open');};

            window.sgpDashTab=(tab,el)=>{document.querySelectorAll('.sgp-tab').forEach(t=>t.classList.remove('active'));el.classList.add('active');document.getElementById('dashOrders').style.display=tab==='orders'?'':'none';document.getElementById('dashQuotes').style.display=tab==='quotes'?'':'none';};

            window.sgpSearch=(q)=>{q=q.toLowerCase();document.querySelectorAll('.sgp-product').forEach(p=>p.classList.toggle('hidden',q&&!p.dataset.name.includes(q)));};
            document.querySelectorAll('.sgp-filter').forEach(b=>b.addEventListener('click',function(){const cat=this.dataset.cat;document.querySelectorAll('.sgp-filter').forEach(x=>x.classList.remove('active'));this.classList.add('active');document.querySelectorAll('.sgp-product').forEach(p=>p.classList.toggle('hidden',cat!=='all'&&p.dataset.cat!==cat));}));
            window.sgpFilterPortfolio=(cat,el)=>{document.querySelectorAll('.sgp-portfolio-tab').forEach(t=>t.classList.remove('active'));el.classList.add('active');document.querySelectorAll('.sgp-portfolio-item').forEach(i=>i.style.display=(cat==='All'||i.dataset.cat===cat)?'':'none');};

            window.sgpShowProduct=(key)=>{const p=products[key];if(!p)return;document.getElementById('prodTitle').textContent=p.name;
                const img=productImages[key]||'';
                const icon=categories[p.category]?.emoji||'📦';
                const imgHtml=img?`<img src="${img}" style="width:100%;height:100%;object-fit:cover">`:icon;
                let h=`<div style="height:140px;background:linear-gradient(135deg,#2a2a2a,#1a1a1a);border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:50px;margin-bottom:14px;overflow:hidden">${imgHtml}</div>`;
                h+=`<p style="color:var(--text2);font-size:12px;margin-bottom:14px;line-height:1.6">${p.description||''}</p>`;
                h+=`<label style="display:block;font-size:10px;color:var(--text2);margin-bottom:4px;text-transform:uppercase;font-weight:600">Select Option</label>`;
                h+=`<select id="pVar" onchange="sgpPriceUpd()" style="width:100%;padding:12px;background:var(--card2);border:1px solid var(--border);border-radius:10px;color:var(--text);font-size:13px;margin-bottom:12px">`;
                p.variations.forEach((v,i)=>h+=`<option value="${i}" data-price="${v.price}">${v.name} - R${v.price}</option>`);
                h+=`</select>`;
                h+=`<label style="display:block;font-size:10px;color:var(--text2);margin-bottom:4px;text-transform:uppercase;font-weight:600">Quantity</label>`;
                h+=`<div style="display:flex;align-items:center;gap:14px;margin-bottom:16px"><button onclick="sgpQty(-1)" style="width:40px;height:40px;background:var(--card2);border:1px solid var(--border);border-radius:10px;color:var(--text);font-size:18px;cursor:pointer">−</button><span id="pQty" style="font-size:18px;font-weight:700;min-width:36px;text-align:center">1</span><button onclick="sgpQty(1)" style="width:40px;height:40px;background:var(--card2);border:1px solid var(--border);border-radius:10px;color:var(--text);font-size:18px;cursor:pointer">+</button></div>`;
                h+=`<div style="background:var(--card2);padding:14px;border-radius:10px;display:flex;justify-content:space-between;align-items:center;margin-bottom:14px"><span style="font-size:12px">Total</span><span id="pTotal" style="font-size:22px;font-weight:800;color:var(--primary)">R${p.variations[0]?.price||0}</span></div>`;
                h+=`<div class="sgp-upload" onclick="document.getElementById('shopFile').click()" style="margin-bottom:12px"><input type="file" id="shopFile" multiple onchange="sgpShopFileChosen()"><div class="sgp-upload-icon">📎</div><div class="sgp-upload-text">Upload your design file (optional)</div><div class="sgp-upload-name" id="shopFileName"></div></div>`;
                h+=`<button class="sgp-btn sgp-btn-primary sgp-btn-block" onclick="sgpAddCart('${key}')">🛒 Add to Cart</button>`;
                h+=`<button class="sgp-btn sgp-btn-outline sgp-btn-block" onclick="sgpSend('${p.name.replace(/'/g,"\\'")}');sgpClose('productModal')">💬 Ask AI about this</button>`;
                document.getElementById('prodBody').innerHTML=h;window.curProd=key;sgpOpen('productModal');
            };
            window.sgpShopFileChosen=()=>{const f=document.getElementById('shopFile');if(f.files.length){let n=[];for(let i=0;i<f.files.length;i++)n.push(f.files[i].name);document.getElementById('shopFileName').textContent='✓ '+n.join(', ');}};
            window.sgpPriceUpd=()=>{const s=document.getElementById('pVar'),p=parseFloat(s.options[s.selectedIndex].dataset.price),q=parseInt(document.getElementById('pQty').textContent);document.getElementById('pTotal').textContent='R'+(p*q);};
            window.sgpQty=(d)=>{const e=document.getElementById('pQty');let q=parseInt(e.textContent)+d;if(q<1)q=1;e.textContent=q;sgpPriceUpd();};
            window.sgpAddCart=(key)=>{const p=products[key],s=document.getElementById('pVar'),v=p.variations[parseInt(s.value)],q=parseInt(document.getElementById('pQty').textContent);cart.push({id:Date.now(),key,name:p.name,variation:v.name,sku:v.sku||'',price:v.price,qty:q});saveCart();sgpClose('productModal');showToast('Added to cart!');};
            function saveCart(){localStorage.setItem('sgp_cart',JSON.stringify(cart));updateCartCount();}
            function updateCartCount(){const el=document.getElementById('cartCount');if(el)el.textContent=cart.length;}
            function showToast(msg){const t=document.createElement('div');t.style.cssText='position:fixed;top:20px;left:50%;transform:translateX(-50%);background:var(--success);color:#fff;padding:10px 20px;border-radius:10px;font-size:13px;font-weight:600;z-index:9999;animation:sgp-spin 0.3s ease';t.textContent=msg;document.body.appendChild(t);setTimeout(()=>t.remove(),2500);}

            window.sgpOpenCart=()=>{if(!cart.length){document.getElementById('cartBody').innerHTML='<div class="sgp-empty"><div class="sgp-empty-icon">🛒</div><p>Your cart is empty</p></div>';sgpOpen('cartModal');return;}let h='',t=0;cart.forEach((i,x)=>{const p=products[i.key],ic=categories[p?.category]?.emoji||'📦',s=i.price*i.qty;t+=s;h+=`<div style="display:flex;gap:10px;padding:12px 0;border-bottom:1px solid var(--border)"><div style="width:44px;height:44px;background:var(--card2);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px">${ic}</div><div style="flex:1"><div style="font-weight:600;font-size:12px">${i.name}</div><div style="font-size:10px;color:var(--text2)">${i.variation} × ${i.qty}</div><div style="color:var(--primary);font-weight:700;margin-top:2px;font-size:13px">R${s}</div></div><button onclick="sgpRemove(${x})" style="background:none;border:none;color:var(--danger);font-size:18px;cursor:pointer">×</button></div>`;});h+=`<div style="display:flex;justify-content:space-between;padding:16px 0;font-size:16px;font-weight:700"><span>Total</span><span style="color:var(--primary)">R${t}</span></div>`;h+=`<button class="sgp-btn sgp-btn-success sgp-btn-block" onclick="sgpCheckout()">✅ Checkout & Generate Invoice</button>`;document.getElementById('cartBody').innerHTML=h;sgpOpen('cartModal');};
            window.sgpRemove=(i)=>{cart.splice(i,1);saveCart();sgpOpenCart();};

            window.sgpCheckout=()=>{if(!isLoggedIn){sgpClose('cartModal');sgpShowAuth('register');return alert('Please create an account or login to checkout.');}let t=0;const items=cart.map(i=>{t+=i.price*i.qty;return i;});fetch(ajax,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:new URLSearchParams({action:'sbha_create_invoice',nonce,items:JSON.stringify(items),total:t})}).then(r=>r.json()).then(d=>{if(d.success){cart=[];saveCart();document.getElementById('cartBody').innerHTML=`<div style="text-align:center;padding:24px"><div style="font-size:50px;margin-bottom:12px">✅</div><h2 style="margin-bottom:6px;font-size:16px">Invoice Created!</h2><p style="font-size:22px;color:var(--primary);font-weight:800;margin-bottom:12px">${d.data.invoice_number}</p><p style="color:var(--text2);margin-bottom:16px;font-size:12px">Total: R${t.toFixed(2)}</p><div style="background:var(--card2);padding:14px;border-radius:10px;text-align:left;font-size:11px;margin-bottom:16px;line-height:1.8"><strong>Pay via EFT:</strong><br>FNB • Switch Graphics (Pty) Ltd<br>Acc: 630 842 187 18 • Branch: 250 655<br>Ref: <strong>${d.data.invoice_number}</strong></div><button class="sgp-btn sgp-btn-primary sgp-btn-block" onclick="sgpUploadProof(${d.data.order_id},'${d.data.invoice_number}')">📤 Upload Payment Proof</button><a href="https://wa.me/${wa}?text=${encodeURIComponent('Hi! I just placed order '+d.data.invoice_number+'. Please send me a payment link.')}" target="_blank" class="sgp-btn sgp-btn-whatsapp sgp-btn-block">💬 Request Payment Link on WhatsApp</a><button class="sgp-btn sgp-btn-outline sgp-btn-block" onclick="location.reload()">Done</button></div>`;}else alert(d.data||'Error');});};

            window.sgpUploadProof=(id,num)=>{sgpClose('cartModal');document.getElementById('uploadBody').innerHTML=`<p style="margin-bottom:14px;font-size:12px">Invoice: <strong style="color:var(--primary)">${num}</strong></p><div class="sgp-upload" onclick="document.getElementById('proofFile').click()"><input type="file" id="proofFile" accept="image/*,.pdf" capture="environment" onchange="sgpFileChosen()"><div class="sgp-upload-icon">📎</div><div class="sgp-upload-text">Tap to upload screenshot or PDF</div><div class="sgp-upload-name" id="proofName"></div></div><input type="hidden" id="proofId" value="${id}"><input type="hidden" id="proofNum" value="${num}"><button class="sgp-btn sgp-btn-success sgp-btn-block" style="margin-top:16px" onclick="sgpSubmitProof()">✅ Submit Proof</button>`;sgpOpen('uploadModal');};
            window.sgpFileChosen=()=>{const f=document.getElementById('proofFile').files[0];if(f)document.getElementById('proofName').textContent='✓ '+f.name;};
            window.sgpSubmitProof=()=>{const f=document.getElementById('proofFile').files[0],id=document.getElementById('proofId').value,num=document.getElementById('proofNum').value;if(!f)return alert('Select a file');const fd=new FormData();fd.append('action','sbha_upload_payment_proof');fd.append('nonce',nonce);fd.append('order_id',id);fd.append('invoice_number',num);fd.append('payment_proof',f);fetch(ajax,{method:'POST',body:fd}).then(r=>r.json()).then(d=>{if(d.success){document.getElementById('uploadBody').innerHTML=`<div style="text-align:center;padding:24px"><div style="font-size:50px;margin-bottom:12px">✅</div><h2 style="font-size:16px">Uploaded!</h2><p style="color:var(--text2);margin:12px 0;font-size:12px">Awaiting verification</p><button class="sgp-btn sgp-btn-primary sgp-btn-block" onclick="location.reload()">Done</button></div>`;}else alert(d.data||'Upload failed');}).catch(e=>alert('Error: '+e.message));};

            window.sgpRequestPayLink=()=>{window.open('https://wa.me/'+wa+'?text='+encodeURIComponent('Hi Switch Graphics! Please send me an online payment link. Thank you!'),'_blank');};

            // AI Chat
            window.sgpSend=(msg)=>{if(!msg.trim())return;addMsg(msg,'user');document.getElementById('sgpInput').value='';const tid='t'+Date.now();addMsg('<span class="sgp-loading"></span>','ai',tid);fetch(ajax,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:new URLSearchParams({action:'sbha_ai_chat',nonce,message:msg,context:JSON.stringify(aiCtx)})}).then(r=>r.json()).then(d=>{document.getElementById(tid)?.remove();if(d.success){aiCtx=d.data.context||{};let h=d.data.message.replace(/\*\*(.*?)\*\*/g,'<strong>$1</strong>').replace(/_(.*?)_/g,'<em>$1</em>');if(d.data.buttons?.length){h+='<div class="sgp-quick">';d.data.buttons.forEach(b=>h+=`<button class="sgp-quick-btn" onclick="sgpSend('${b.value.replace(/'/g,"\\'")}')">${b.text}</button>`);h+='</div>';}addMsg(h,'ai');if(d.data.show_quote_form)showQuoteForm(d.data.quote_data);}}).catch(e=>{document.getElementById(tid)?.remove();addMsg('Sorry, something went wrong. Please try again.','ai');});};
            window.sgpSendInput=()=>{const i=document.getElementById('sgpInput');if(i.value.trim())sgpSend(i.value);};
            document.getElementById('sgpInput')?.addEventListener('keypress',e=>{if(e.key==='Enter')sgpSendInput();});
            function addMsg(c,t,id){const ch=document.getElementById('sgpChat');if(!ch)return;const d=document.createElement('div');d.className='sgp-msg sgp-msg-'+t;if(id)d.id=id;d.innerHTML=c;ch.appendChild(d);ch.scrollTop=ch.scrollHeight;}

            function showQuoteForm(data){
                let h='<div style="background:rgba(255,102,0,0.1);border:1px solid var(--primary);border-radius:12px;padding:14px;margin-bottom:16px">';
                let t=0;
                if(data.items)data.items.forEach(i=>{const s=i.item_total||(i.unit_price||0)*(i.quantity||1);t+=s;h+=`<div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid rgba(255,102,0,0.15);font-size:12px"><span>${i.variant_name||i.product_name||''}</span><span style="font-weight:700">R${s}</span></div>`;if(i.needs_design)h+=`<div style="font-size:10px;color:var(--primary);padding:2px 0">+ Design service included</div>`;});
                t=data.estimate_total||t;
                h+=`<div style="display:flex;justify-content:space-between;padding:10px 0;font-weight:700;font-size:14px"><span>Estimated Total</span><span style="color:var(--primary)">R${t}</span></div></div>`;
                h+=`<p style="font-size:10px;color:var(--text2);margin-bottom:14px">Final price confirmed after admin review. You can upload design files after submitting.</p>`;
                if(!isLoggedIn){
                    h+=`<div class="sgp-form-group"><label>Full Name *</label><input type="text" id="qName" placeholder="Your full name"></div>`;
                    h+=`<div class="sgp-form-group"><label>WhatsApp Number *</label><input type="tel" id="qPhone" placeholder="e.g. 068 123 4567"></div>`;
                    h+=`<div class="sgp-form-group"><label>Email *</label><input type="email" id="qEmail" placeholder="your@email.com"></div>`;
                    h+=`<div class="sgp-form-group"><label>Password * (to create your account)</label><input type="password" id="qPass" placeholder="Min 4 characters"></div>`;
                }
                h+=`<div class="sgp-upload" onclick="document.getElementById('quoteFile').click()" style="margin-bottom:12px"><input type="file" id="quoteFile" multiple onchange="sgpQuoteFileChosen()"><div class="sgp-upload-icon">📎</div><div class="sgp-upload-text">Upload design files (optional, any format)</div><div class="sgp-upload-name" id="quoteFileName"></div></div>`;
                h+=`<button class="sgp-btn sgp-btn-success sgp-btn-block" onclick="sgpSubmitQuote()">✅ Submit Quote Request</button>`;
                window.quoteData=data;
                document.getElementById('quoteBody').innerHTML=h;
                sgpOpen('quoteModal');
            }
            window.sgpQuoteFileChosen=()=>{const f=document.getElementById('quoteFile');if(f.files.length){let n=[];for(let i=0;i<f.files.length;i++)n.push(f.files[i].name);document.getElementById('quoteFileName').textContent='✓ '+n.join(', ');}};
            window.sgpSubmitQuote=()=>{
                const name=document.getElementById('qName')?.value||'',phone=document.getElementById('qPhone')?.value||'',email=document.getElementById('qEmail')?.value||'',pass=document.getElementById('qPass')?.value||'';
                if(!isLoggedIn&&(!name||!phone||!email||!pass))return alert('Please fill in all required fields (name, WhatsApp, email, password).');
                if(!isLoggedIn&&pass.length<4)return alert('Password must be at least 4 characters.');
                const chatHtml=document.getElementById('sgpChat')?.innerHTML||'';
                const fd=new FormData();
                fd.append('action','sbha_submit_quote');
                fd.append('nonce',nonce);
                fd.append('name',name);fd.append('phone',phone);fd.append('email',email);fd.append('password',pass);
                fd.append('quote_data',JSON.stringify(window.quoteData));
                fd.append('transcript',chatHtml);
                const qf=document.getElementById('quoteFile');
                if(qf&&qf.files.length){for(let i=0;i<qf.files.length;i++)fd.append('files[]',qf.files[i]);}
                fetch(ajax,{method:'POST',body:fd}).then(r=>r.json()).then(d=>{
                    if(d.success){
                        document.getElementById('quoteBody').innerHTML=`<div style="text-align:center;padding:24px"><div style="font-size:50px;margin-bottom:12px">✅</div><h2 style="font-size:16px">Quote Submitted!</h2><p style="font-size:22px;color:var(--primary);font-weight:800;margin:12px 0">${d.data.quote_number}</p><p style="color:var(--text2);font-size:12px;margin-bottom:16px">We'll review your request and WhatsApp you the final quote.</p>${d.data.account_created?'<p style="color:var(--success);font-size:11px;margin-bottom:12px">✅ Account created! You\'re now logged in.</p>':''}<button class="sgp-btn sgp-btn-primary sgp-btn-block" onclick="location.reload()">Done</button></div>`;
                    }else alert(d.data||'Error submitting quote');
                }).catch(e=>alert('Error: '+e.message));
            };

            window.sgpTrack=()=>{const n=document.getElementById('trackNum')?.value.trim();if(!n)return alert('Please enter your invoice number (starts with SBH)');fetch(ajax,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:new URLSearchParams({action:'sbha_track_order',nonce,invoice:n})}).then(r=>r.json()).then(d=>{const el=document.getElementById('trackResult');if(!el)return;if(d.success){const o=d.data;let st=o.status;if(o.status==='pending')st=o.payment_proof?'Verifying Payment':'Awaiting Payment';else st=o.status.charAt(0).toUpperCase()+o.status.slice(1);el.innerHTML=`<div class="sgp-card" style="margin-top:14px"><div style="display:flex;justify-content:space-between;align-items:center"><h3 style="font-size:14px;color:var(--primary)">${o.quote_number||o.invoice_number||''}</h3><span class="sgp-status sgp-status-${o.status}">${st}</span></div><p style="color:var(--text2);margin-top:6px;font-size:12px">${o.description||''}</p><p style="margin-top:6px;font-size:13px;font-weight:700">Total: ${o.total||''}</p><p style="color:var(--text2);font-size:10px;margin-top:4px">Date: ${o.date||''}</p></div>`;}else el.innerHTML='<p style="text-align:center;padding:16px;color:var(--text2);font-size:12px">Order not found. Please check your invoice number.</p>';});};

            // Auth
            window.sgpShowAuth=(tab)=>{
                document.getElementById('authTitle').textContent=tab==='login'?'Login':'Create Account';
                if(tab==='login'){
                    document.getElementById('authBody').innerHTML=`<div class="sgp-form-group"><label>WhatsApp Number</label><input type="tel" id="lPhone" placeholder="e.g. 068 123 4567"></div><div class="sgp-form-group"><label>Password</label><input type="password" id="lPass" placeholder="Your password"></div><button class="sgp-btn sgp-btn-primary sgp-btn-block" onclick="sgpLogin()">Login</button><p style="text-align:center;margin-top:14px;font-size:12px"><span style="color:var(--primary);cursor:pointer" onclick="sgpShowAuth('register')">Don't have an account? Register</span></p><p style="text-align:center;margin-top:8px;font-size:11px"><span style="color:var(--text2);cursor:pointer" onclick="sgpShowAuth('reset')">Forgot password?</span></p>`;
                } else if(tab==='reset'){
                    document.getElementById('authBody').innerHTML=`<div class="sgp-form-group"><label>WhatsApp Number</label><input type="tel" id="rpPhone" placeholder="e.g. 068 123 4567"></div><div class="sgp-form-group"><label>New Password</label><input type="password" id="rpPass" placeholder="Min 4 characters"></div><button class="sgp-btn sgp-btn-primary sgp-btn-block" onclick="sgpResetPwd()">Reset Password</button><p style="text-align:center;margin-top:14px;font-size:12px"><span style="color:var(--primary);cursor:pointer" onclick="sgpShowAuth('login')">Back to Login</span></p>`;
                } else {
                    document.getElementById('authBody').innerHTML=`<div class="sgp-form-group"><label>Full Name *</label><input type="text" id="rName" placeholder="Your full name"></div><div class="sgp-form-group"><label>WhatsApp Number *</label><input type="tel" id="rPhone" placeholder="e.g. 068 123 4567"></div><div class="sgp-form-group"><label>Email *</label><input type="email" id="rEmail" placeholder="your@email.com"></div><div class="sgp-form-group"><label>Password *</label><input type="password" id="rPass" placeholder="Min 4 characters"></div><button class="sgp-btn sgp-btn-primary sgp-btn-block" onclick="sgpRegister()">Create Account</button><p style="text-align:center;margin-top:14px;font-size:12px"><span style="color:var(--primary);cursor:pointer" onclick="sgpShowAuth('login')">Already have an account? Login</span></p>`;
                }
                sgpOpen('authModal');
            };
            window.sgpLogin=()=>{const p=document.getElementById('lPhone')?.value,w=document.getElementById('lPass')?.value;if(!p||!w)return alert('Enter your WhatsApp number and password');fetch(ajax,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:new URLSearchParams({action:'sbha_login',nonce,phone:p,password:w})}).then(r=>r.json()).then(d=>{if(d.success)location.reload();else alert(d.data||'Login failed');});};
            window.sgpRegister=()=>{const n=document.getElementById('rName')?.value,p=document.getElementById('rPhone')?.value,e=document.getElementById('rEmail')?.value,w=document.getElementById('rPass')?.value;if(!n||!p||!e||!w)return alert('Please fill in all fields (name, WhatsApp, email, password).');if(w.length<4)return alert('Password must be at least 4 characters.');fetch(ajax,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:new URLSearchParams({action:'sbha_register',nonce,name:n,phone:p,email:e,password:w})}).then(r=>r.json()).then(d=>{if(d.success)location.reload();else alert(d.data||'Registration failed');});};
            window.sgpResetPwd=()=>{const p=document.getElementById('rpPhone')?.value,w=document.getElementById('rpPass')?.value;if(!p||!w)return alert('Enter your phone and new password');fetch(ajax,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:new URLSearchParams({action:'sbha_reset_password',nonce,phone:p,password:w})}).then(r=>r.json()).then(d=>{if(d.success){alert('Password updated! You can now login.');sgpShowAuth('login');}else alert(d.data||'Failed');});};
            window.sgpLogout=()=>{fetch(ajax,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:new URLSearchParams({action:'sbha_logout',nonce})}).then(()=>location.reload());};

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
