<?php
/**
 * Switch Graphics Customer Portal v3.1
 * Fixed: No duplicate headers, AI in own tab, WhatsApp order, invoice view
 * 
 * @package SwitchBusinessHub
 * @version 3.1.0
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
        $notifications = array();
        if ($logged_in) {
            $customer_orders = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}sbha_quotes WHERE customer_id = %d ORDER BY created_at DESC LIMIT 50",
                $customer['id']
            ));
            foreach ($customer_orders as $o) {
                if (($o->status ?? '') === 'ready') {
                    $notifications[] = array('type' => 'success', 'msg' => "{$o->quote_number} is ready!", 'time' => $o->created_at);
                } elseif (($o->status ?? '') === 'processing') {
                    $notifications[] = array('type' => 'info', 'msg' => "{$o->quote_number} is being processed", 'time' => $o->created_at);
                }
            }
        }
        
        $wa = '27681474232';
        $wa_display = '068 147 4232';
        $email_addr = 'tinashe@switchgraphics.co.za';
        $biz_name = get_option('sbha_business_name', 'Switch Graphics (Pty) Ltd');
        $reg_number = get_option('sbha_reg_number', '');
        $csd_number = get_option('sbha_csd_number', '');
        $logo_url = get_option('sbha_logo_url', '');
        $product_images = get_option('sbha_product_images', array());

        ob_start();
        ?>
        <style>
        :root{--bg:#0f0f0f;--card:#1a1a1a;--card2:#242424;--primary:#FF6600;--primary-glow:rgba(255,102,0,0.3);--text:#fff;--text2:#aaa;--border:#333;--success:#00C853;--info:#2196F3;--warning:#FFC107;--danger:#FF5252;--radius:14px}
        .sgp{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:var(--bg);min-height:100vh;color:var(--text);max-width:600px;margin:0 auto;position:relative;padding-bottom:70px}
        .sgp *{box-sizing:border-box;margin:0;padding:0}
        .sgp a{color:var(--primary);text-decoration:none}

        /* HEADER - shows once at top */
        .sgp-hdr{background:#1a1a1a;padding:14px 16px;border-bottom:1px solid var(--border);position:sticky;top:0;z-index:50}
        .sgp-hdr-row{display:flex;justify-content:space-between;align-items:center}
        .sgp-logo{display:flex;align-items:center;gap:10px}
        .sgp-logo-img{width:40px;height:40px;border-radius:10px;overflow:hidden;background:var(--primary);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:14px;color:#fff}
        .sgp-logo-img img{width:100%;height:100%;object-fit:contain}
        .sgp-logo-txt h1{font-size:14px;font-weight:700;color:var(--primary)}
        .sgp-logo-txt small{font-size:8px;color:var(--text2);display:block}
        .sgp-hdr-btns{display:flex;gap:6px;align-items:center}
        .sgp-hdr-btns button{width:36px;height:36px;border-radius:10px;background:var(--card2);border:1px solid var(--border);color:var(--text);cursor:pointer;position:relative;font-size:15px;display:flex;align-items:center;justify-content:center}
        .sgp-bdg{position:absolute;top:-3px;right:-3px;background:var(--primary);color:#fff;font-size:8px;min-width:14px;height:14px;border-radius:7px;display:flex;align-items:center;justify-content:center;font-weight:700}
        .sgp-auth-row{display:flex;gap:6px;margin-top:10px}
        .sgp-auth-row .sgp-btn{flex:1;padding:8px;font-size:11px}
        .sgp-user-strip{display:flex;justify-content:space-between;align-items:center;padding:10px 16px;background:linear-gradient(135deg,var(--primary),#e85d00);margin:0;font-size:12px}
        .sgp-user-strip b{font-size:14px}
        .sgp-user-strip button{background:rgba(0,0,0,0.25);border:none;color:#fff;padding:5px 10px;border-radius:6px;font-size:10px;cursor:pointer;font-weight:600}

        /* BUTTONS */
        .sgp-btn{display:inline-flex;align-items:center;justify-content:center;gap:5px;padding:10px 18px;border:none;border-radius:10px;font-size:12px;font-weight:600;cursor:pointer;transition:all 0.15s}
        .sgp-btn-primary{background:var(--primary);color:#fff}
        .sgp-btn-outline{background:transparent;border:1.5px solid var(--border);color:var(--text)}
        .sgp-btn-success{background:var(--success);color:#fff}
        .sgp-btn-wa{background:#25D366;color:#fff}
        .sgp-btn-block{width:100%;margin-top:8px}
        .sgp-btn-sm{padding:5px 10px;font-size:10px;border-radius:6px}

        /* NAV */
        .sgp-nav{position:fixed;bottom:0;left:50%;transform:translateX(-50%);width:100%;max-width:600px;background:#1a1a1a;border-top:1px solid var(--border);display:flex;justify-content:space-around;padding:4px 0 8px;z-index:100}
        .sgp-nav button{display:flex;flex-direction:column;align-items:center;gap:1px;background:none;border:none;color:var(--text2);font-size:9px;cursor:pointer;padding:4px 8px;border-radius:8px}
        .sgp-nav button svg{width:20px;height:20px}
        .sgp-nav button.on{color:var(--primary);background:rgba(255,102,0,0.1)}

        /* PANELS */
        .sgp-pnl{display:none;padding:12px 16px}
        .sgp-pnl.on{display:block}
        .sgp-pnl h2{font-size:16px;font-weight:700;margin-bottom:12px;color:var(--primary)}

        /* CARDS */
        .sgp-card{background:var(--card);border-radius:var(--radius);border:1px solid var(--border);padding:12px;margin-bottom:10px}
        
        /* ORDERS */
        .sgp-ord{padding:10px 0;border-bottom:1px solid var(--border)}
        .sgp-ord:last-child{border:none}
        .sgp-ord-top{display:flex;justify-content:space-between;align-items:center}
        .sgp-ord-num{font-weight:700;font-size:13px}
        .sgp-ord-meta{font-size:10px;color:var(--text2);margin-top:2px}
        .sgp-ord-items{font-size:10px;color:var(--text2);margin-top:4px;padding:6px;background:var(--card2);border-radius:6px}
        .sgp-st{padding:3px 8px;border-radius:20px;font-size:8px;font-weight:700;text-transform:uppercase}
        .sgp-st-pending{background:rgba(255,193,7,0.2);color:var(--warning)}
        .sgp-st-processing{background:rgba(33,150,243,0.2);color:var(--info)}
        .sgp-st-ready,.sgp-st-completed{background:rgba(0,200,83,0.2);color:var(--success)}
        .sgp-st-cancelled{background:rgba(255,82,82,0.2);color:var(--danger)}
        .sgp-st-quoted{background:rgba(156,39,176,0.2);color:#CE93D8}
        .sgp-ord-btns{display:flex;gap:6px;margin-top:6px;flex-wrap:wrap}

        /* AI CHAT */
        .sgp-chat-box{background:var(--card);border-radius:var(--radius);border:1px solid var(--border);overflow:hidden;margin-bottom:12px}
        .sgp-chat-hdr{padding:12px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px}
        .sgp-chat-av{width:32px;height:32px;background:linear-gradient(135deg,var(--primary),#e85d00);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:16px}
        .sgp-chat-hdr h3{font-size:12px;color:var(--primary)}
        .sgp-chat-hdr small{font-size:9px;color:var(--text2);display:block}
        .sgp-chat-msgs{padding:10px;max-height:380px;overflow-y:auto;scroll-behavior:smooth}
        .sgp-m{margin-bottom:8px;max-width:90%}
        .sgp-m-ai{background:var(--card2);padding:10px 12px;border-radius:12px 12px 12px 2px;font-size:11px;line-height:1.7;white-space:pre-line}
        .sgp-m-ai strong{color:var(--primary)}
        .sgp-m-user{background:var(--primary);margin-left:auto;padding:8px 12px;border-radius:12px 12px 2px 12px;font-size:11px}
        .sgp-qbtns{display:flex;flex-wrap:wrap;gap:5px;margin-top:8px}
        .sgp-qbtn{background:var(--card);border:1px solid var(--border);color:var(--text);padding:6px 10px;border-radius:20px;font-size:10px;font-weight:600;cursor:pointer}
        .sgp-qbtn:hover{border-color:var(--primary);color:var(--primary)}
        .sgp-chat-in{padding:8px;border-top:1px solid var(--border);display:flex;gap:6px}
        .sgp-chat-in input{flex:1;background:var(--card2);border:1px solid var(--border);border-radius:20px;padding:9px 14px;color:var(--text);font-size:11px;outline:none}
        .sgp-chat-in input:focus{border-color:var(--primary)}
        .sgp-chat-in input::placeholder{color:#555}
        .sgp-chat-in button{width:36px;height:36px;background:var(--primary);border:none;border-radius:50%;color:#fff;font-size:14px;cursor:pointer}

        /* SHOP */
        .sgp-search input{width:100%;background:var(--card);border:1px solid var(--border);border-radius:10px;padding:10px 14px 10px 38px;color:var(--text);font-size:12px;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23555'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:12px center;background-size:16px;outline:none;margin-bottom:10px}
        .sgp-filters{display:flex;overflow-x:auto;gap:5px;padding-bottom:8px;margin-bottom:10px;-webkit-overflow-scrolling:touch}
        .sgp-filters::-webkit-scrollbar{display:none}
        .sgp-filt{flex-shrink:0;padding:6px 12px;background:var(--card);border:1px solid var(--border);border-radius:20px;font-size:10px;font-weight:500;cursor:pointer;white-space:nowrap;color:var(--text)}
        .sgp-filt.on{background:var(--primary);border-color:var(--primary)}
        .sgp-prods{display:grid;grid-template-columns:repeat(2,1fr);gap:8px}
        .sgp-prod{background:var(--card);border-radius:var(--radius);overflow:hidden;border:1px solid var(--border);cursor:pointer;transition:all 0.15s}
        .sgp-prod:hover{border-color:var(--primary)}
        .sgp-prod.hid{display:none}
        .sgp-prod-img{height:80px;background:linear-gradient(135deg,#252525,#1a1a1a);display:flex;align-items:center;justify-content:center;font-size:30px;overflow:hidden}
        .sgp-prod-img img{width:100%;height:100%;object-fit:cover}
        .sgp-prod-info{padding:8px}
        .sgp-prod-name{font-size:10px;font-weight:600;margin-bottom:2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
        .sgp-prod-price{font-size:14px;font-weight:800;color:var(--primary)}
        .sgp-prod-price span{font-size:8px;font-weight:400;color:var(--text2)}

        /* CONTACT */
        .sgp-contact-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:12px}
        .sgp-contact-card{display:flex;flex-direction:column;align-items:center;padding:14px;background:var(--card);border-radius:var(--radius);border:1px solid var(--border);text-decoration:none;color:var(--text);text-align:center}
        .sgp-cc-icon{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;margin-bottom:6px}
        .sgp-cc-icon.call{background:rgba(33,150,243,0.2)}.sgp-cc-icon.wa{background:rgba(37,211,102,0.2)}.sgp-cc-icon.mail{background:rgba(233,30,99,0.2)}.sgp-cc-icon.loc{background:rgba(255,152,0,0.2)}
        .sgp-cc-lbl{font-size:8px;color:var(--text2);text-transform:uppercase}.sgp-cc-val{font-weight:600;font-size:11px}
        .sgp-bank{background:linear-gradient(135deg,#1e2a3a,#162030);border-radius:var(--radius);padding:14px;margin-bottom:12px;border:1px solid rgba(33,150,243,0.3)}
        .sgp-bank h4{color:#64B5F6;font-size:12px;margin-bottom:10px}
        .sgp-bank-row{display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid rgba(255,255,255,0.06);font-size:11px}
        .sgp-bank-row:last-child{border:none}
        .sgp-bank-row span:first-child{color:#90CAF9}.sgp-bank-row span:last-child{font-weight:600}

        /* PORTFOLIO */
        .sgp-port-tabs{display:flex;overflow-x:auto;gap:5px;margin-bottom:14px;-webkit-overflow-scrolling:touch}.sgp-port-tabs::-webkit-scrollbar{display:none}
        .sgp-port-tab{flex-shrink:0;padding:6px 14px;background:var(--card);border:1px solid var(--border);border-radius:20px;font-size:10px;font-weight:600;cursor:pointer;color:var(--text)}
        .sgp-port-tab.on{background:var(--primary);border-color:var(--primary)}
        .sgp-port-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:8px}
        .sgp-port-item{background:var(--card);border-radius:var(--radius);overflow:hidden;border:1px solid var(--border)}
        .sgp-port-item-img{height:110px;background:var(--card2);overflow:hidden}
        .sgp-port-item-img img{width:100%;height:100%;object-fit:cover}
        .sgp-port-item-img .ph{width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:34px;background:linear-gradient(135deg,#252525,#1a1a1a)}
        .sgp-port-item-info{padding:8px}
        .sgp-port-item-info h3{font-size:11px;font-weight:600}.sgp-port-item-info span{font-size:9px;color:var(--text2)}

        /* MODALS */
        .sgp-modal{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.85);z-index:1000;align-items:flex-end;justify-content:center}
        .sgp-modal.show{display:flex}
        .sgp-modal-c{background:var(--card);border-radius:18px 18px 0 0;padding:18px;width:100%;max-width:500px;max-height:90vh;overflow-y:auto}
        .sgp-modal-h{display:flex;justify-content:space-between;align-items:center;margin-bottom:14px}
        .sgp-modal-h h2{font-size:15px;color:var(--primary)}
        .sgp-modal-x{width:30px;height:30px;background:var(--card2);border:none;border-radius:50%;font-size:16px;cursor:pointer;color:var(--text);display:flex;align-items:center;justify-content:center}

        .sgp-form-g{margin-bottom:10px}
        .sgp-form-g label{display:block;font-size:9px;font-weight:600;color:var(--text2);margin-bottom:3px;text-transform:uppercase}
        .sgp-form-g input,.sgp-form-g textarea,.sgp-form-g select{width:100%;padding:10px 12px;background:var(--card2);border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:12px;outline:none}
        .sgp-form-g input:focus{border-color:var(--primary)}

        .sgp-upload{border:2px dashed var(--border);border-radius:var(--radius);padding:20px;text-align:center;cursor:pointer}
        .sgp-upload:hover{border-color:var(--primary)}
        .sgp-upload input{display:none}
        .sgp-upload-name{color:var(--success);margin-top:6px;font-size:10px}

        .sgp-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:999}.sgp-overlay.show{display:block}
        .sgp-notif-p{position:fixed;top:0;right:-100%;width:100%;max-width:300px;height:100%;background:var(--card);z-index:1001;transition:right 0.3s;overflow-y:auto}
        .sgp-notif-p.open{right:0}

        .sgp-ld{display:inline-block;width:14px;height:14px;border:2px solid rgba(255,255,255,0.3);border-radius:50%;border-top-color:#fff;animation:sp 0.8s linear infinite}
        @keyframes sp{to{transform:rotate(360deg)}}
        .sgp-empty{text-align:center;padding:24px;color:var(--text2);font-size:11px}
        .sgp-empty-i{font-size:36px;margin-bottom:8px}
        .sgp-tabs{display:flex;background:var(--card);border-radius:10px;border:1px solid var(--border);overflow:hidden;margin-bottom:12px}
        .sgp-tab{flex:1;padding:8px;text-align:center;font-size:10px;font-weight:600;cursor:pointer;color:var(--text2);border:none;background:none}
        .sgp-tab.on{background:var(--primary);color:#fff}
        </style>

        <div class="sgp">
            <!-- HEADER (once) -->
            <div class="sgp-hdr">
                <div class="sgp-hdr-row">
                    <div class="sgp-logo">
                        <div class="sgp-logo-img">
                            <?php if ($logo_url): ?><img src="<?php echo esc_url($logo_url); ?>" alt="Logo"><?php else: ?>SG<?php endif; ?>
                        </div>
                        <div class="sgp-logo-txt">
                            <h1><?php echo esc_html($biz_name); ?></h1>
                            <?php if ($reg_number): ?><small>Reg: <?php echo esc_html($reg_number); ?><?php if ($csd_number): ?> | CSD: <?php echo esc_html($csd_number); ?><?php endif; ?></small><?php endif; ?>
                        </div>
                    </div>
                    <div class="sgp-hdr-btns">
                        <?php if ($logged_in): ?>
                        <button onclick="sgpNotif()" title="Notifications">🔔<?php if (count($notifications)): ?><span class="sgp-bdg"><?php echo count($notifications); ?></span><?php endif; ?></button>
                        <?php endif; ?>
                        <button onclick="sgpCart()" title="Cart">🛒<span class="sgp-bdg" id="cc">0</span></button>
                    </div>
                </div>
                <?php if (!$logged_in): ?>
                <div class="sgp-auth-row">
                    <button class="sgp-btn sgp-btn-primary" onclick="sgpAuth('login')">Login</button>
                    <button class="sgp-btn sgp-btn-outline" onclick="sgpAuth('register')">Register</button>
                </div>
                <?php endif; ?>
            </div>

            <?php if ($logged_in): ?>
            <div class="sgp-user-strip">
                <div><b>Hi <?php echo esc_html($customer['first_name']); ?>!</b><br><span style="font-size:10px;opacity:0.8">What would you like today?</span></div>
                <button onclick="sgpLogout()">Logout</button>
            </div>
            <?php endif; ?>

            <!-- ===== HOME PANEL ===== -->
            <?php if ($logged_in): ?>
            <div class="sgp-pnl on" id="pHome">
                <h2>My Orders</h2>
                <div class="sgp-tabs">
                    <button class="sgp-tab on" onclick="sgpDT('inv',this)">Invoices</button>
                    <button class="sgp-tab" onclick="sgpDT('qt',this)">Quotes</button>
                </div>
                <div id="dtInv">
                <?php 
                $all_orders = (array)$customer_orders;
                $invs = array_filter($all_orders, function($o){ return property_exists($o,'quote_type') && $o->quote_type==='invoice'; });
                if ($invs): foreach ($invs as $o):
                    $st = $o->status ?? 'pending';
                    $proof = !empty($o->payment_proof);
                    $stl = $st; $stx = ucfirst($st);
                    if ($st==='pending' && $proof) { $stl='processing'; $stx='Verifying'; }
                    elseif ($st==='pending') { $stx='Awaiting Payment'; }
                    $its = json_decode($o->items ?? '[]', true);
                    $itxt = ''; if ($its) foreach(array_slice($its,0,3) as $it) $itxt .= ($it['variation']??$it['variant_name']??$it['name']??'').', ';
                    $itxt = rtrim($itxt,', ');
                ?>
                <div class="sgp-card">
                    <div class="sgp-ord">
                        <div class="sgp-ord-top">
                            <div><div class="sgp-ord-num"><?php echo esc_html($o->quote_number); ?></div><div class="sgp-ord-meta"><?php echo date('d M Y', strtotime($o->created_at)); ?> • R<?php echo number_format($o->total,2); ?></div></div>
                            <span class="sgp-st sgp-st-<?php echo $stl; ?>"><?php echo $stx; ?></span>
                        </div>
                        <?php if ($itxt): ?><div class="sgp-ord-items"><?php echo esc_html($itxt); ?></div><?php endif; ?>
                        <div class="sgp-ord-btns">
                            <button class="sgp-btn sgp-btn-outline sgp-btn-sm" onclick="sgpViewDoc('<?php echo esc_js($o->quote_number); ?>',<?php echo (int)$o->total; ?>,'<?php echo esc_js($itxt); ?>')">📄 View</button>
                            <button class="sgp-btn sgp-btn-sm sgp-btn-wa" onclick="sgpWaOrder('<?php echo esc_js($o->quote_number); ?>',<?php echo (int)$o->total; ?>,'<?php echo esc_js($itxt); ?>')">💬 WhatsApp</button>
                            <?php if (!$proof && $st==='pending'): ?>
                            <button class="sgp-btn sgp-btn-primary sgp-btn-sm" onclick="sgpProof(<?php echo $o->id; ?>,'<?php echo esc_js($o->quote_number); ?>')">📤 Pay</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; else: ?>
                <div class="sgp-empty"><div class="sgp-empty-i">📦</div><p>No orders yet</p></div>
                <?php endif; ?>
                </div>
                <div id="dtQt" style="display:none">
                <?php 
                $qts = array_filter($all_orders, function($o){ return !property_exists($o,'quote_type') || $o->quote_type!=='invoice'; });
                if ($qts): foreach ($qts as $o):
                    $st = $o->status ?? 'pending';
                    $stx = $st==='pending'?'Under Review':ucfirst($st);
                    $its = json_decode($o->items ?? '[]', true);
                    $itxt = ''; if ($its) foreach(array_slice($its,0,3) as $it) $itxt .= ($it['variant_name']??$it['product_name']??'').', ';
                    $itxt = rtrim($itxt,', ');
                ?>
                <div class="sgp-card">
                    <div class="sgp-ord">
                        <div class="sgp-ord-top">
                            <div><div class="sgp-ord-num"><?php echo esc_html($o->quote_number); ?></div><div class="sgp-ord-meta"><?php echo date('d M Y', strtotime($o->created_at)); ?> • Est. R<?php echo number_format($o->total,2); ?></div></div>
                            <span class="sgp-st sgp-st-<?php echo $st; ?>"><?php echo $stx; ?></span>
                        </div>
                        <?php if ($itxt): ?><div class="sgp-ord-items"><?php echo esc_html($itxt); ?></div><?php endif; ?>
                        <div class="sgp-ord-btns">
                            <button class="sgp-btn sgp-btn-outline sgp-btn-sm" onclick="sgpViewDoc('<?php echo esc_js($o->quote_number); ?>',<?php echo (int)$o->total; ?>,'<?php echo esc_js($itxt); ?>')">📄 View</button>
                            <button class="sgp-btn sgp-btn-sm sgp-btn-wa" onclick="sgpWaOrder('<?php echo esc_js($o->quote_number); ?>',<?php echo (int)$o->total; ?>,'<?php echo esc_js($itxt); ?>')">💬 WhatsApp</button>
                        </div>
                    </div>
                </div>
                <?php endforeach; else: ?>
                <div class="sgp-empty"><div class="sgp-empty-i">📋</div><p>No quotes yet. Chat with AI to get started!</p></div>
                <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- ===== AI PANEL ===== -->
            <div class="sgp-pnl <?php echo !$logged_in?'on':''; ?>" id="pAI">
                <div class="sgp-chat-box">
                    <div class="sgp-chat-hdr">
                        <div class="sgp-chat-av">🤖</div>
                        <div><h3>AI Quote Assistant</h3><small>Get instant pricing</small></div>
                    </div>
                    <div class="sgp-chat-msgs" id="aiMsgs">
                        <div class="sgp-m sgp-m-ai">Hi! What do you need today? Pick a category or just tell me:
                            <div class="sgp-qbtns">
                                <button class="sgp-qbtn" onclick="sgpAI('I need business cards')">💳 Cards</button>
                                <button class="sgp-qbtn" onclick="sgpAI('I need flyers')">📄 Flyers</button>
                                <button class="sgp-qbtn" onclick="sgpAI('I need signage')">🪧 Signage</button>
                                <button class="sgp-qbtn" onclick="sgpAI('I need banners')">🎪 Banners</button>
                                <button class="sgp-qbtn" onclick="sgpAI('I need wedding items')">💒 Wedding</button>
                                <button class="sgp-qbtn" onclick="sgpAI('I need t-shirts')">👕 Apparel</button>
                                <button class="sgp-qbtn" onclick="sgpAI('I need a design')">🎨 Design</button>
                                <button class="sgp-qbtn" onclick="sgpAI('I need a website')">🌐 Website</button>
                            </div>
                        </div>
                    </div>
                    <div class="sgp-chat-in">
                        <input type="text" id="aiIn" placeholder="Type what you need..." autocomplete="off">
                        <button onclick="sgpAIgo()">➤</button>
                    </div>
                </div>
            </div>

            <!-- ===== SHOP PANEL ===== -->
            <div class="sgp-pnl" id="pShop">
                <h2>Shop</h2>
                <div class="sgp-search"><input type="text" id="ss" placeholder="Search products..." oninput="sgpSS(this.value)"></div>
                <div class="sgp-filters">
                    <button class="sgp-filt on" data-c="all">All</button>
                    <?php foreach ($categories as $k => $c): ?>
                    <button class="sgp-filt" data-c="<?php echo esc_attr($k); ?>"><?php echo esc_html($c['emoji'].' '.$c['name']); ?></button>
                    <?php endforeach; ?>
                </div>
                <div class="sgp-prods">
                    <?php foreach ($products as $key => $p): 
                        $min = SBHA_Products::get_min_price($p);
                        $ic = $categories[$p['category']]['emoji'] ?? '📦';
                        $img = $product_images[$key] ?? ($p['image'] ?? '');
                    ?>
                    <div class="sgp-prod" data-c="<?php echo esc_attr($p['category']); ?>" data-n="<?php echo esc_attr(strtolower($p['name'].' '.($p['description']??''))); ?>" onclick="sgpProd('<?php echo esc_js($key); ?>')">
                        <div class="sgp-prod-img"><?php if ($img): ?><img src="<?php echo esc_url($img); ?>" alt="" loading="lazy"><?php else: echo $ic; endif; ?></div>
                        <div class="sgp-prod-info"><div class="sgp-prod-name"><?php echo esc_html($p['name']); ?></div><div class="sgp-prod-price">R<?php echo number_format($min,0); ?> <span>from</span></div></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- ===== PORTFOLIO PANEL ===== -->
            <div class="sgp-pnl" id="pPort">
                <h2>Our Work</h2>
                <div class="sgp-port-tabs">
                    <?php foreach ($portfolio_cats as $i => $c): ?>
                    <button class="sgp-port-tab <?php echo $i===0?'on':''; ?>" onclick="sgpPF('<?php echo esc_js($c); ?>',this)"><?php echo esc_html($c); ?></button>
                    <?php endforeach; ?>
                </div>
                <div class="sgp-port-grid">
                    <?php foreach ($portfolio_items as $item): ?>
                    <div class="sgp-port-item" data-c="<?php echo esc_attr($item['category']); ?>">
                        <div class="sgp-port-item-img"><?php if (!empty($item['image'])): ?><img src="<?php echo esc_url($item['image']); ?>"><?php else: ?><div class="ph">🎨</div><?php endif; ?></div>
                        <div class="sgp-port-item-info"><h3><?php echo esc_html($item['title']); ?></h3><span><?php echo esc_html($item['category']); ?></span></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- ===== TRACK PANEL ===== -->
            <div class="sgp-pnl" id="pTrack">
                <h2>Track Order</h2>
                <div class="sgp-card">
                    <div class="sgp-form-g"><label>Invoice Number</label><input type="text" id="tNum" placeholder="e.g. SBH0001"></div>
                    <button class="sgp-btn sgp-btn-primary sgp-btn-block" onclick="sgpTrk()">🔍 Track</button>
                    <div id="tRes"></div>
                </div>
            </div>

            <!-- ===== CONTACT PANEL ===== -->
            <div class="sgp-pnl" id="pContact">
                <h2>Contact Us</h2>
                <div class="sgp-contact-grid">
                    <a href="tel:+<?php echo $wa; ?>" class="sgp-contact-card"><div class="sgp-cc-icon call">📞</div><span class="sgp-cc-lbl">Call</span><span class="sgp-cc-val"><?php echo $wa_display; ?></span></a>
                    <a href="https://wa.me/<?php echo $wa; ?>" target="_blank" class="sgp-contact-card"><div class="sgp-cc-icon wa">💬</div><span class="sgp-cc-lbl">WhatsApp</span><span class="sgp-cc-val"><?php echo $wa_display; ?></span></a>
                    <a href="mailto:<?php echo $email_addr; ?>" class="sgp-contact-card"><div class="sgp-cc-icon mail">✉️</div><span class="sgp-cc-lbl">Email</span><span class="sgp-cc-val" style="font-size:9px"><?php echo $email_addr; ?></span></a>
                    <a href="https://maps.google.com/?q=16+Harding+Street+Newcastle+2940" target="_blank" class="sgp-contact-card"><div class="sgp-cc-icon loc">📍</div><span class="sgp-cc-lbl">Visit</span><span class="sgp-cc-val">16 Harding St</span></a>
                </div>
                <div class="sgp-bank">
                    <h4>🏦 Banking Details</h4>
                    <div class="sgp-bank-row"><span>Bank</span><span>FNB / RMB</span></div>
                    <div class="sgp-bank-row"><span>Account</span><span><?php echo esc_html($biz_name); ?></span></div>
                    <div class="sgp-bank-row"><span>Number</span><span>630 842 187 18</span></div>
                    <div class="sgp-bank-row"><span>Branch</span><span>250 655</span></div>
                    <div class="sgp-bank-row"><span>Reference</span><span>Your Invoice No.</span></div>
                </div>
                <a href="https://wa.me/<?php echo $wa; ?>?text=<?php echo urlencode('Hi Switch Graphics!'); ?>" target="_blank" class="sgp-btn sgp-btn-wa sgp-btn-block">💬 Chat on WhatsApp</a>
            </div>

            <!-- NAV -->
            <nav class="sgp-nav">
                <?php if ($logged_in): ?>
                <button class="on" onclick="go('pHome',this)"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>Home</button>
                <?php endif; ?>
                <button <?php echo !$logged_in?'class="on"':''; ?> onclick="go('pAI',this)"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>AI</button>
                <button onclick="go('pShop',this)"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>Shop</button>
                <button onclick="go('pPort',this)"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>Portfolio</button>
                <button onclick="go('pTrack',this)"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>Track</button>
                <button onclick="go('pContact',this)"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>Contact</button>
            </nav>

            <!-- OVERLAY + NOTIFS -->
            <div class="sgp-overlay" id="ov" onclick="sgpNotifClose()"></div>
            <div class="sgp-notif-p" id="nP">
                <div style="padding:14px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center"><h3 style="color:var(--primary);font-size:14px">Notifications</h3><button class="sgp-modal-x" onclick="sgpNotifClose()">×</button></div>
                <?php if ($notifications): foreach ($notifications as $n): ?>
                <div style="padding:10px 14px;border-bottom:1px solid var(--border);display:flex;gap:8px"><div style="font-size:14px"><?php echo $n['type']==='success'?'✅':'ℹ️'; ?></div><div><p style="font-size:11px"><?php echo esc_html($n['msg']); ?></p><span style="font-size:9px;color:var(--text2)"><?php echo date('d M', strtotime($n['time'])); ?></span></div></div>
                <?php endforeach; else: ?>
                <div class="sgp-empty">No notifications</div>
                <?php endif; ?>
            </div>

            <!-- MODALS -->
            <div class="sgp-modal" id="mProd"><div class="sgp-modal-c"><div class="sgp-modal-h"><h2 id="mpT">Product</h2><button class="sgp-modal-x" onclick="cl('mProd')">×</button></div><div id="mpB"></div></div></div>
            <div class="sgp-modal" id="mCart"><div class="sgp-modal-c"><div class="sgp-modal-h"><h2>🛒 Cart</h2><button class="sgp-modal-x" onclick="cl('mCart')">×</button></div><div id="mcB"></div></div></div>
            <div class="sgp-modal" id="mAuth"><div class="sgp-modal-c"><div class="sgp-modal-h"><h2 id="maT">Login</h2><button class="sgp-modal-x" onclick="cl('mAuth')">×</button></div><div id="maB"></div></div></div>
            <div class="sgp-modal" id="mUp"><div class="sgp-modal-c"><div class="sgp-modal-h"><h2>Upload Proof</h2><button class="sgp-modal-x" onclick="cl('mUp')">×</button></div><div id="muB"></div></div></div>
            <div class="sgp-modal" id="mQuote"><div class="sgp-modal-c"><div class="sgp-modal-h"><h2>Submit Quote</h2><button class="sgp-modal-x" onclick="cl('mQuote')">×</button></div><div id="mqB"></div></div></div>
            <div class="sgp-modal" id="mView"><div class="sgp-modal-c"><div class="sgp-modal-h"><h2 id="mvT">Document</h2><button class="sgp-modal-x" onclick="cl('mView')">×</button></div><div id="mvB"></div></div></div>
        </div>

        <script>
        (function(){
        const A='<?php echo esc_js($ajax); ?>',N='<?php echo esc_js($nonce); ?>',LI=<?php echo $logged_in?'true':'false'; ?>,P=<?php echo json_encode($products); ?>,C=<?php echo json_encode($categories); ?>,W='<?php echo $wa; ?>',PI=<?php echo json_encode($product_images); ?>,BIZ='<?php echo esc_js($biz_name); ?>';
        let cart=JSON.parse(localStorage.getItem('sgp_cart')||'[]'),aiCtx={};
        updCC();

        function op(id){document.getElementById(id)?.classList.add('show')}
        function cl(id){document.getElementById(id)?.classList.remove('show')}
        window.cl=cl;
        function go(id,btn){document.querySelectorAll('.sgp-pnl').forEach(p=>p.classList.remove('on'));document.querySelectorAll('.sgp-nav button').forEach(b=>b.classList.remove('on'));document.getElementById(id)?.classList.add('on');if(btn)btn.classList.add('on');}
        window.go=go;
        function updCC(){const e=document.getElementById('cc');if(e)e.textContent=cart.length;}
        function post(d){return fetch(A,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:new URLSearchParams(d)}).then(r=>r.json());}

        // NAV
        window.sgpNotif=()=>{document.getElementById('ov')?.classList.add('show');document.getElementById('nP')?.classList.add('open');};
        window.sgpNotifClose=()=>{document.getElementById('ov')?.classList.remove('show');document.getElementById('nP')?.classList.remove('open');};
        window.sgpDT=(t,el)=>{document.querySelectorAll('.sgp-tab').forEach(x=>x.classList.remove('on'));el.classList.add('on');document.getElementById('dtInv').style.display=t==='inv'?'':'none';document.getElementById('dtQt').style.display=t==='qt'?'':'none';};

        // VIEW DOCUMENT
        window.sgpViewDoc=(num,total,items)=>{document.getElementById('mvT').textContent=num;document.getElementById('mvB').innerHTML=`<div style="border:1px solid var(--border);border-radius:12px;padding:16px;background:var(--card2)"><div style="text-align:center;margin-bottom:14px"><strong style="color:var(--primary);font-size:16px">${BIZ}</strong><br><span style="font-size:9px;color:var(--text2)">16 Harding St, Newcastle 2940 | ${W.replace('27','0')}</span></div><hr style="border-color:var(--border);margin:10px 0"><div style="display:flex;justify-content:space-between;font-size:11px;margin-bottom:10px"><span><strong>Invoice:</strong> ${num}</span><span>${new Date().toLocaleDateString('en-ZA')}</span></div><div style="font-size:11px;margin-bottom:12px"><strong>Items:</strong><br>${items||'As per discussion'}</div><div style="display:flex;justify-content:space-between;font-size:14px;font-weight:700;padding:10px 0;border-top:1px solid var(--border)"><span>Total</span><span style="color:var(--primary)">R${total}</span></div><hr style="border-color:var(--border);margin:10px 0"><div style="font-size:9px;color:var(--text2)"><strong>Banking:</strong> FNB | ${BIZ} | 630 842 187 18 | Branch 250 655 | Ref: ${num}</div></div><button class="sgp-btn sgp-btn-wa sgp-btn-block" onclick="sgpWaOrder('${num}',${total},'${(items||'').replace(/'/g,"\\'")}')">💬 Send to WhatsApp</button>`;op('mView');};
        window.sgpWaOrder=(num,total,items)=>{const msg=`Hi Switch Graphics!%0A%0AOrder: ${num}%0AItems: ${items||'As per discussion'}%0ATotal: R${total}%0A%0ABanking:%0AFNB | ${BIZ}%0AAcc: 630 842 187 18%0ABranch: 250 655%0ARef: ${num}%0A%0APlease confirm.`;window.open('https://wa.me/'+W+'?text='+msg,'_blank');};

        // SHOP
        window.sgpSS=(q)=>{q=q.toLowerCase();document.querySelectorAll('.sgp-prod').forEach(p=>p.classList.toggle('hid',q&&!p.dataset.n.includes(q)));};
        document.querySelectorAll('.sgp-filt').forEach(b=>b.addEventListener('click',function(){const c=this.dataset.c;document.querySelectorAll('.sgp-filt').forEach(x=>x.classList.remove('on'));this.classList.add('on');document.querySelectorAll('.sgp-prod').forEach(p=>p.classList.toggle('hid',c!=='all'&&p.dataset.c!==c));}));
        window.sgpPF=(c,el)=>{document.querySelectorAll('.sgp-port-tab').forEach(t=>t.classList.remove('on'));el.classList.add('on');document.querySelectorAll('.sgp-port-item').forEach(i=>i.style.display=(c==='All'||i.dataset.c===c)?'':'none');};

        // PRODUCT MODAL
        window.sgpProd=(k)=>{const p=P[k];if(!p)return;document.getElementById('mpT').textContent=p.name;const img=PI[k]||'',ic=C[p.category]?.emoji||'📦',ih=img?`<img src="${img}" style="width:100%;height:100%;object-fit:cover">`:ic;let h=`<div style="height:120px;background:#222;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:40px;margin-bottom:12px;overflow:hidden">${ih}</div>`;h+=`<p style="color:var(--text2);font-size:11px;margin-bottom:12px;line-height:1.5">${p.description||''}</p>`;h+=`<select id="pV" onchange="pUpd()" style="width:100%;padding:10px;background:var(--card2);border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:12px;margin-bottom:10px">`;p.variations.forEach((v,i)=>h+=`<option value="${i}" data-p="${v.price}">${v.name} - R${v.price}</option>`);h+=`</select>`;h+=`<div style="display:flex;align-items:center;gap:12px;margin-bottom:12px"><button onclick="pQ(-1)" style="width:36px;height:36px;background:var(--card2);border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:16px;cursor:pointer">−</button><span id="pQty" style="font-size:16px;font-weight:700;min-width:30px;text-align:center">1</span><button onclick="pQ(1)" style="width:36px;height:36px;background:var(--card2);border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:16px;cursor:pointer">+</button></div>`;h+=`<div style="background:var(--card2);padding:12px;border-radius:8px;display:flex;justify-content:space-between;align-items:center;margin-bottom:12px"><span style="font-size:11px">Total</span><span id="pTot" style="font-size:20px;font-weight:800;color:var(--primary)">R${p.variations[0]?.price||0}</span></div>`;h+=`<button class="sgp-btn sgp-btn-primary sgp-btn-block" onclick="addC('${k}')">🛒 Add to Cart</button>`;document.getElementById('mpB').innerHTML=h;window._pk=k;op('mProd');};
        window.pUpd=()=>{const s=document.getElementById('pV'),p=parseFloat(s.options[s.selectedIndex].dataset.p),q=parseInt(document.getElementById('pQty').textContent);document.getElementById('pTot').textContent='R'+(p*q);};
        window.pQ=(d)=>{const e=document.getElementById('pQty');let q=parseInt(e.textContent)+d;if(q<1)q=1;e.textContent=q;pUpd();};
        window.addC=(k)=>{const p=P[k],s=document.getElementById('pV'),v=p.variations[parseInt(s.value)],q=parseInt(document.getElementById('pQty').textContent);cart.push({id:Date.now(),key:k,name:p.name,variation:v.name,sku:v.sku||'',price:v.price,qty:q});localStorage.setItem('sgp_cart',JSON.stringify(cart));updCC();cl('mProd');};

        // CART
        window.sgpCart=()=>{if(!cart.length){document.getElementById('mcB').innerHTML='<div class="sgp-empty"><div class="sgp-empty-i">🛒</div><p>Cart empty</p></div>';op('mCart');return;}let h='',t=0;cart.forEach((i,x)=>{const ic=C[P[i.key]?.category]?.emoji||'📦',s=i.price*i.qty;t+=s;h+=`<div style="display:flex;gap:8px;padding:8px 0;border-bottom:1px solid var(--border)"><div style="width:36px;height:36px;background:var(--card2);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:16px">${ic}</div><div style="flex:1"><div style="font-weight:600;font-size:11px">${i.name}</div><div style="font-size:9px;color:var(--text2)">${i.variation} × ${i.qty}</div><div style="color:var(--primary);font-weight:700;font-size:12px">R${s}</div></div><button onclick="rmC(${x})" style="background:none;border:none;color:var(--danger);font-size:16px;cursor:pointer">×</button></div>`;});h+=`<div style="display:flex;justify-content:space-between;padding:12px 0;font-weight:700"><span>Total</span><span style="color:var(--primary);font-size:16px">R${t}</span></div>`;h+=`<button class="sgp-btn sgp-btn-success sgp-btn-block" onclick="checkout()">✅ Place Order</button>`;const waTxt=cart.map(i=>`${i.name} (${i.variation}) x${i.qty} = R${i.price*i.qty}`).join('%0A');h+=`<a href="https://wa.me/${W}?text=${encodeURIComponent('Hi Switch Graphics! I would like to order:%0A'+waTxt+'%0A%0ATotal: R'+t+'%0A%0ABanking:%0AFNB | '+BIZ+'%0AAcc: 630 842 187 18%0ABranch: 250 655')}" target="_blank" class="sgp-btn sgp-btn-wa sgp-btn-block">💬 Send Order on WhatsApp</a>`;document.getElementById('mcB').innerHTML=h;op('mCart');};
        window.rmC=(i)=>{cart.splice(i,1);localStorage.setItem('sgp_cart',JSON.stringify(cart));updCC();sgpCart();};
        window.checkout=()=>{if(!LI){cl('mCart');sgpAuth('register');return alert('Please login/register first');}let t=0;cart.forEach(i=>t+=i.price*i.qty);post({action:'sbha_create_invoice',nonce:N,items:JSON.stringify(cart),total:t}).then(d=>{if(d.success){const inv=d.data.invoice_number;const waTxt=cart.map(i=>`${i.name} (${i.variation}) x${i.qty} = R${i.price*i.qty}`).join('%0A');cart=[];localStorage.setItem('sgp_cart','[]');updCC();document.getElementById('mcB').innerHTML=`<div style="text-align:center;padding:20px"><div style="font-size:40px;margin-bottom:10px">✅</div><h2 style="font-size:14px">Order Placed!</h2><p style="font-size:20px;color:var(--primary);font-weight:800;margin:8px 0">${inv}</p><p style="color:var(--text2);font-size:11px;margin-bottom:12px">Total: R${t.toFixed(2)}</p><div style="background:var(--card2);padding:12px;border-radius:8px;font-size:10px;margin-bottom:12px;line-height:1.8;text-align:left"><strong>Pay via EFT:</strong><br>FNB • ${BIZ}<br>Acc: 630 842 187 18 • Branch: 250 655<br>Ref: <strong>${inv}</strong></div><a href="https://wa.me/${W}?text=${encodeURIComponent('Hi! Order '+inv+' placed.%0A'+waTxt+'%0ATotal: R'+t.toFixed(2)+'%0A%0ABanking:%0AFNB | '+BIZ+'%0AAcc: 630 842 187 18%0ABranch: 250 655%0ARef: '+inv)}" target="_blank" class="sgp-btn sgp-btn-wa sgp-btn-block">💬 Send Order to WhatsApp</a><button class="sgp-btn sgp-btn-primary sgp-btn-block" onclick="sgpProof(${d.data.order_id},'${inv}')">📤 Upload Payment Proof</button><button class="sgp-btn sgp-btn-outline sgp-btn-block" onclick="location.reload()">Done</button></div>`;}else alert(d.data||'Error');}).catch(()=>alert('Network error, please try again.'));};

        // PROOF
        window.sgpProof=(id,num)=>{cl('mCart');document.getElementById('muB').innerHTML=`<p style="font-size:11px;margin-bottom:12px">Invoice: <strong style="color:var(--primary)">${num}</strong></p><div class="sgp-upload" onclick="document.getElementById('pf').click()"><input type="file" id="pf" accept="image/*,.pdf" capture="environment" onchange="pfN()"><div style="font-size:28px;margin-bottom:6px">📎</div><div style="font-size:10px;color:var(--text2)">Tap to upload</div><div class="sgp-upload-name" id="pfN"></div></div><input type="hidden" id="pfId" value="${id}"><input type="hidden" id="pfNum" value="${num}"><button class="sgp-btn sgp-btn-success sgp-btn-block" style="margin-top:12px" onclick="pfS()">✅ Submit</button>`;op('mUp');};
        window.pfN=()=>{const f=document.getElementById('pf').files[0];if(f)document.getElementById('pfN').textContent='✓ '+f.name;};
        window.pfS=()=>{const f=document.getElementById('pf').files[0];if(!f)return alert('Select a file');const fd=new FormData();fd.append('action','sbha_upload_payment_proof');fd.append('nonce',N);fd.append('order_id',document.getElementById('pfId').value);fd.append('invoice_number',document.getElementById('pfNum').value);fd.append('payment_proof',f);fetch(A,{method:'POST',body:fd}).then(r=>r.json()).then(d=>{if(d.success)document.getElementById('muB').innerHTML='<div style="text-align:center;padding:20px"><div style="font-size:40px;margin-bottom:8px">✅</div><h2 style="font-size:14px">Uploaded!</h2><p style="color:var(--text2);font-size:11px;margin:8px 0">Verifying payment</p><button class="sgp-btn sgp-btn-primary sgp-btn-block" onclick="location.reload()">Done</button></div>';else alert(d.data||'Failed');}).catch(()=>alert('Upload error'));};

        // AI CHAT
        window.sgpAI=(msg)=>{if(!msg.trim())return;addM(msg,'user');document.getElementById('aiIn').value='';const tid='t'+Date.now();addM('<span class="sgp-ld"></span>','ai',tid);post({action:'sbha_ai_chat',nonce:N,message:msg,context:JSON.stringify(aiCtx)}).then(d=>{document.getElementById(tid)?.remove();if(d.success){aiCtx=d.data.context||{};let h=d.data.message.replace(/\*\*(.*?)\*\*/g,'<strong>$1</strong>').replace(/_(.*?)_/g,'<em>$1</em>');if(d.data.buttons?.length){h+='<div class="sgp-qbtns">';d.data.buttons.forEach(b=>h+=`<button class="sgp-qbtn" onclick="sgpAI('${b.value.replace(/'/g,"\\'")}')">${b.text}</button>`);h+='</div>';}addM(h,'ai');if(d.data.show_quote_form)showQF(d.data.quote_data);}}).catch(()=>{document.getElementById(tid)?.remove();addM('Something went wrong. Try again.','ai');});};
        window.sgpAIgo=()=>{const i=document.getElementById('aiIn');if(i.value.trim())sgpAI(i.value);};
        document.getElementById('aiIn')?.addEventListener('keypress',e=>{if(e.key==='Enter')sgpAIgo();});
        function addM(c,t,id){const ch=document.getElementById('aiMsgs');if(!ch)return;const d=document.createElement('div');d.className='sgp-m sgp-m-'+t;if(id)d.id=id;d.innerHTML=c;ch.appendChild(d);ch.scrollTop=ch.scrollHeight;}

        // QUOTE FORM
        function showQF(data){let h='<div style="background:rgba(255,102,0,0.1);border:1px solid var(--primary);border-radius:10px;padding:12px;margin-bottom:14px">';let t=0;if(data.items)data.items.forEach(i=>{const s=i.item_total||(i.unit_price||0)*(i.quantity||1);t+=s;h+=`<div style="display:flex;justify-content:space-between;padding:4px 0;font-size:11px;border-bottom:1px solid rgba(255,102,0,0.1)"><span>${i.variant_name||i.product_name||''}</span><span style="font-weight:700">R${s}</span></div>`;});t=data.estimate_total||t;h+=`<div style="display:flex;justify-content:space-between;padding:8px 0;font-weight:700;font-size:13px"><span>Estimated Total</span><span style="color:var(--primary)">R${t}</span></div></div>`;if(!LI){h+=`<div class="sgp-form-g"><label>Name *</label><input id="qN" placeholder="Full name"></div><div class="sgp-form-g"><label>WhatsApp *</label><input id="qP" type="tel" placeholder="068 123 4567"></div><div class="sgp-form-g"><label>Email *</label><input id="qE" type="email" placeholder="you@email.com"></div><div class="sgp-form-g"><label>Password *</label><input id="qW" type="password" placeholder="Min 4 chars"></div>`;}h+=`<button class="sgp-btn sgp-btn-success sgp-btn-block" onclick="subQ()">✅ Submit Quote</button>`;const waTxt=data.items?data.items.map(i=>`${i.variant_name||i.product_name} - R${i.item_total||(i.unit_price*i.quantity)}`).join('%0A'):'';h+=`<a href="https://wa.me/${W}?text=${encodeURIComponent('Hi! Quote request:%0A'+waTxt+'%0ATotal: R'+t+'%0A%0ABanking:%0AFNB | '+BIZ+'%0AAcc: 630 842 187 18%0ARef: Quote')}" target="_blank" class="sgp-btn sgp-btn-wa sgp-btn-block">💬 Send to WhatsApp</a>`;window._qd=data;document.getElementById('mqB').innerHTML=h;op('mQuote');}
        window.subQ=()=>{const n=document.getElementById('qN')?.value||'',p=document.getElementById('qP')?.value||'',e=document.getElementById('qE')?.value||'',w=document.getElementById('qW')?.value||'';if(!LI&&(!n||!p||!e||!w))return alert('Fill all fields');if(!LI&&w.length<4)return alert('Password: min 4 chars');const fd=new FormData();fd.append('action','sbha_submit_quote');fd.append('nonce',N);fd.append('name',n);fd.append('phone',p);fd.append('email',e);fd.append('password',w);fd.append('quote_data',JSON.stringify(window._qd));fd.append('transcript','');fetch(A,{method:'POST',body:fd}).then(r=>r.json()).then(d=>{if(d.success)document.getElementById('mqB').innerHTML=`<div style="text-align:center;padding:20px"><div style="font-size:40px;margin-bottom:8px">✅</div><h2 style="font-size:14px">Quote Submitted!</h2><p style="font-size:18px;color:var(--primary);font-weight:800;margin:8px 0">${d.data.quote_number}</p><p style="color:var(--text2);font-size:11px">We'll WhatsApp you the final quote.</p><button class="sgp-btn sgp-btn-primary sgp-btn-block" style="margin-top:12px" onclick="location.reload()">Done</button></div>`;else alert(d.data||'Error');}).catch(()=>alert('Network error. Please try again.'));};

        // TRACK
        window.sgpTrk=()=>{const n=document.getElementById('tNum')?.value.trim();if(!n)return alert('Enter invoice number');post({action:'sbha_track_order',nonce:N,invoice:n}).then(d=>{const el=document.getElementById('tRes');if(!el)return;if(d.success){const o=d.data;let st=o.status==='pending'?(o.payment_proof?'Verifying':'Awaiting Payment'):o.status.charAt(0).toUpperCase()+o.status.slice(1);el.innerHTML=`<div class="sgp-card" style="margin-top:10px"><div style="display:flex;justify-content:space-between"><h3 style="font-size:13px;color:var(--primary)">${o.quote_number||o.invoice_number}</h3><span class="sgp-st sgp-st-${o.status}">${st}</span></div><p style="color:var(--text2);font-size:11px;margin-top:4px">${o.description||''}</p><p style="font-weight:700;font-size:12px;margin-top:4px">Total: ${o.total||''}</p></div>`;}else el.innerHTML='<p style="text-align:center;padding:14px;color:var(--text2);font-size:11px">Not found</p>';});};

        // AUTH
        window.sgpAuth=(t)=>{document.getElementById('maT').textContent=t==='login'?'Login':'Create Account';let h='';if(t==='login')h=`<div class="sgp-form-g"><label>WhatsApp</label><input id="lP" type="tel" placeholder="068 123 4567"></div><div class="sgp-form-g"><label>Password</label><input id="lW" type="password" placeholder="Password"></div><button class="sgp-btn sgp-btn-primary sgp-btn-block" onclick="doLogin()">Login</button><p style="text-align:center;margin-top:12px;font-size:11px"><span style="color:var(--primary);cursor:pointer" onclick="sgpAuth('register')">Register</span> | <span style="color:var(--text2);cursor:pointer" onclick="sgpAuth('reset')">Forgot?</span></p>`;else if(t==='reset')h=`<div class="sgp-form-g"><label>WhatsApp</label><input id="rpP" type="tel" placeholder="068 123 4567"></div><div class="sgp-form-g"><label>New Password</label><input id="rpW" type="password" placeholder="Min 4 chars"></div><button class="sgp-btn sgp-btn-primary sgp-btn-block" onclick="doReset()">Reset</button><p style="text-align:center;margin-top:12px;font-size:11px"><span style="color:var(--primary);cursor:pointer" onclick="sgpAuth('login')">Back</span></p>`;else h=`<div class="sgp-form-g"><label>Name *</label><input id="rN" placeholder="Full name"></div><div class="sgp-form-g"><label>WhatsApp *</label><input id="rP" type="tel" placeholder="068 123 4567"></div><div class="sgp-form-g"><label>Email *</label><input id="rE" type="email" placeholder="you@email.com"></div><div class="sgp-form-g"><label>Password *</label><input id="rW" type="password" placeholder="Min 4 chars"></div><button class="sgp-btn sgp-btn-primary sgp-btn-block" onclick="doReg()">Register</button><p style="text-align:center;margin-top:12px;font-size:11px"><span style="color:var(--primary);cursor:pointer" onclick="sgpAuth('login')">Login</span></p>`;document.getElementById('maB').innerHTML=h;op('mAuth');};
        window.doLogin=()=>{const p=document.getElementById('lP')?.value,w=document.getElementById('lW')?.value;if(!p||!w)return alert('Fill both fields');post({action:'sbha_login',nonce:N,phone:p,password:w}).then(d=>{if(d.success)location.reload();else alert(d.data||'Failed');});};
        window.doReg=()=>{const n=document.getElementById('rN')?.value,p=document.getElementById('rP')?.value,e=document.getElementById('rE')?.value,w=document.getElementById('rW')?.value;if(!n||!p||!e||!w)return alert('Fill all fields');post({action:'sbha_register',nonce:N,name:n,phone:p,email:e,password:w}).then(d=>{if(d.success)location.reload();else alert(d.data||'Failed');});};
        window.doReset=()=>{const p=document.getElementById('rpP')?.value,w=document.getElementById('rpW')?.value;if(!p||!w)return alert('Fill both fields');post({action:'sbha_reset_password',nonce:N,phone:p,password:w}).then(d=>{if(d.success){alert('Updated! Login now.');sgpAuth('login');}else alert(d.data||'Failed');});};
        window.sgpLogout=()=>{post({action:'sbha_logout',nonce:N}).then(()=>location.reload());};

        ['mProd','mCart','mAuth','mUp','mQuote','mView'].forEach(id=>document.getElementById(id)?.addEventListener('click',e=>{if(e.target.id===id)cl(id);}));
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
            if (!empty($item['category']) && !in_array($item['category'], $cats)) $cats[] = $item['category'];
        }
        return $cats ?: array('Brand Design');
    }
    
    private function get_default_portfolio() {
        return array(
            array('title' => 'Gadla Supermarket', 'category' => 'Brand Design', 'image' => ''),
            array('title' => 'Yanga Innovations', 'category' => 'Brand Design', 'image' => ''),
            array('title' => 'Greencor Group', 'category' => 'Brand Design', 'image' => ''),
            array('title' => 'Modern Logo', 'category' => 'Logo Design', 'image' => ''),
            array('title' => 'E-Commerce Site', 'category' => 'Websites', 'image' => ''),
            array('title' => 'Corporate Cards', 'category' => 'Business Cards', 'image' => ''),
            array('title' => 'Event Flyers', 'category' => 'Flyers & Posters', 'image' => ''),
        );
    }
}
