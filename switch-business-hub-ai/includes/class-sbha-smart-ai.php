<?php
/**
 * Switch Graphics AI v3.0 - Conversational Print & Design Expert
 * 
 * A smooth, conversational AI that:
 * 1. Greets and asks what the customer needs
 * 2. Understands print/design industry context
 * 3. Suggests appropriate products with sizes/materials/options
 * 4. Asks if they need design service or will provide their own file
 * 5. Gathers all details step by step (not rushing)
 * 6. Shows estimated pricing at each step
 * 7. Builds a detailed quote with all info pre-filled
 * 8. Works for logged-in AND non-logged-in users
 * 
 * @package SwitchBusinessHub
 * @version 3.0.0
 */

if (!defined('ABSPATH')) exit;

class SBHA_Smart_AI {
    
    private $products;
    private $categories;
    
    public function __construct() {
        require_once plugin_dir_path(__FILE__) . 'class-sbha-products.php';
        $this->products = SBHA_Products::get_all();
        $this->categories = SBHA_Products::get_categories();
    }
    
    public function process($message, $context = array()) {
        $ctx = wp_parse_args($context, array(
            'state' => 'greeting',
            'conversation' => array(),
            'current_product' => null,
            'current_variation' => null,
            'current_price' => 0,
            'current_qty' => 1,
            'needs_design' => null,
            'has_own_file' => null,
            'design_brief' => '',
            'purpose' => '',
            'is_digital' => false,
            'delivery' => '',
            'delivery_address' => '',
            'special_notes' => '',
            'event_date' => '',
            'cart' => array(),
            'matched_products' => array(),
        ));
        
        $ctx['conversation'][] = array('role' => 'user', 'text' => $message);
        
        $msg = strtolower(trim($message));
        $result = $this->handle($msg, $message, $ctx);
        
        $ctx['conversation'][] = array('role' => 'ai', 'text' => strip_tags($result['message']));
        $result['context'] = $ctx;
        
        return array(
            'message' => $result['message'],
            'buttons' => $result['buttons'] ?? array(),
            'context' => $result['context'],
            'show_quote_form' => $result['show_quote_form'] ?? false,
            'quote_data' => $result['quote_data'] ?? null,
        );
    }
    
    private function handle($msg, $original, &$ctx) {
        if (preg_match('/^(done|finalize|finish|submit|complete|no more|that\'?s?\s*all|nothing else|nope|no thanks)$/i', $msg)) {
            if (!empty($ctx['cart'])) {
                return $this->finalize_quote($ctx);
            }
        }
        
        if (preg_match('/(track|where|status).*(order|invoice|delivery)/i', $msg)) {
            return array(
                'message' => "To track your order, go to the **Track** tab at the bottom and enter your invoice number (starts with SBH). If you don't have it yet, feel free to WhatsApp us at **068 147 4232** and we'll check for you!",
                'buttons' => array(),
            );
        }
        
        if (preg_match('/(hours|open|when|time|close)/i', $msg) && preg_match('/(shop|office|store|work)/i', $msg)) {
            return array(
                'message' => "We're at **16 Harding Street, Newcastle, 2940**.\n\nMon-Fri: 8:00 - 17:00\nSat: 9:00 - 13:00\nSun: Closed\n\nYou can also WhatsApp us anytime at **068 147 4232**!",
                'buttons' => array(
                    array('text' => '📍 Get Directions', 'value' => 'directions'),
                    array('text' => '💬 WhatsApp Us', 'value' => 'whatsapp'),
                ),
            );
        }
        
        switch ($ctx['state']) {
            case 'greeting':
                return $this->handle_greeting($msg, $ctx);
                
            case 'exploring':
                return $this->handle_exploring($msg, $ctx);
                
            case 'pick_product':
                return $this->handle_product_selection($msg, $ctx);
                
            case 'pick_variation':
                return $this->handle_variation_selection($msg, $ctx);
                
            case 'ask_quantity':
                return $this->handle_quantity($msg, $ctx);
                
            case 'ask_design':
                return $this->handle_design_question($msg, $ctx);
                
            case 'get_design_brief':
                return $this->handle_design_brief($original, $ctx);
                
            case 'ask_purpose':
                return $this->handle_purpose($original, $ctx);
                
            case 'ask_event_date':
                return $this->handle_event_date($original, $ctx);
                
            case 'ask_delivery':
                return $this->handle_delivery($msg, $ctx);
                
            case 'get_address':
                return $this->handle_address($original, $ctx);
                
            case 'ask_notes':
                return $this->handle_notes($original, $ctx);
                
            case 'ask_more':
                return $this->handle_ask_more($msg, $ctx);
                
            case 'done':
                if (!empty($ctx['cart'])) {
                    return $this->finalize_quote($ctx);
                }
                return $this->greeting($ctx);
                
            default:
                return $this->handle_greeting($msg, $ctx);
        }
    }
    
    private function greeting(&$ctx) {
        $ctx['state'] = 'greeting';
        return array(
            'message' => "Hi there! Welcome to **Switch Graphics**. I'm here to help you find exactly what you need and get you a quote.\n\nWhat are you looking for today? You can tell me in your own words, or pick a category below:",
            'buttons' => array(
                array('text' => '💳 Business Cards', 'value' => 'I need business cards'),
                array('text' => '📄 Flyers & Posters', 'value' => 'I need flyers'),
                array('text' => '🪧 Signage & Boards', 'value' => 'I need signage'),
                array('text' => '🎪 Banners', 'value' => 'I need banners'),
                array('text' => '💒 Wedding Items', 'value' => 'I need wedding items'),
                array('text' => '👕 Clothing', 'value' => 'I need t-shirts printed'),
                array('text' => '🎨 Design Only', 'value' => 'I need a design'),
                array('text' => '🌐 Website', 'value' => 'I need a website'),
            ),
        );
    }
    
    private function handle_greeting($msg, &$ctx) {
        $matches = $this->find_matching_products($msg);
        
        if (!empty($matches)) {
            $ctx['matched_products'] = $matches;
            
            if (count($matches) === 1) {
                $key = array_keys($matches)[0];
                return $this->show_product($key, $ctx);
            }
            
            return $this->show_product_options($matches, $msg, $ctx);
        }
        
        $category = $this->detect_category($msg);
        if ($category) {
            return $this->show_category_products($category, $ctx);
        }
        
        if (preg_match('/(hi|hello|hey|good\s*(morning|afternoon|evening)|sup|howzit)/i', $msg)) {
            return $this->greeting($ctx);
        }
        
        $ctx['state'] = 'exploring';
        return array(
            'message' => "I'd love to help! Could you tell me a bit more about what you're looking for?\n\nFor example:\n- \"I need business cards for my new company\"\n- \"I need a welcome board for a wedding\"\n- \"I want banners for my shop\"\n- \"I need t-shirts for an event\"\n\nOr pick a category:",
            'buttons' => array(
                array('text' => '🎨 Design', 'value' => 'I need design services'),
                array('text' => '🖨️ Printing', 'value' => 'I need printing'),
                array('text' => '🪧 Signage', 'value' => 'I need signage'),
                array('text' => '💒 Events', 'value' => 'I need event items'),
                array('text' => '👕 Apparel', 'value' => 'I need clothing printed'),
                array('text' => '🌐 Websites', 'value' => 'I need a website built'),
            ),
        );
    }
    
    private function handle_exploring($msg, &$ctx) {
        return $this->handle_greeting($msg, $ctx);
    }
    
    private function find_matching_products($msg) {
        $keyword_map = array(
            'business card' => array('standard_business_cards', 'premium_business_cards', 'spot_uv_cards'),
            'card' => array('standard_business_cards', 'premium_business_cards'),
            'flyer' => array('a5_flyers', 'a4_flyers', 'dl_flyers', 'a6_flyers'),
            'leaflet' => array('a5_flyers', 'dl_flyers'),
            'brochure' => array('folded_brochures'),
            'booklet' => array('booklets'),
            'catalogue' => array('booklets'),
            'catalog' => array('booklets'),
            'poster' => array('paper_posters', 'foam_board_posters'),
            'banner' => array('pvc_banners', 'pullup_banners', 'mesh_banners', 'x_banners'),
            'pull.?up' => array('pullup_banners'),
            'roll.?up' => array('pullup_banners'),
            'pvc banner' => array('pvc_banners'),
            'mesh' => array('mesh_banners'),
            'x.?banner' => array('x_banners'),
            'correx' => array('correx_boards'),
            'corflute' => array('correx_boards'),
            'perspex' => array('perspex_boards'),
            'acrylic' => array('perspex_boards'),
            'chromadek' => array('chromadek_signs'),
            'metal sign' => array('chromadek_signs'),
            'forex' => array('forex_pvc_board'),
            'pvc board' => array('forex_pvc_board'),
            'a.?frame' => array('aframe_signs'),
            'pavement sign' => array('aframe_signs'),
            'sign' => array('correx_boards', 'perspex_boards', 'chromadek_signs', 'forex_pvc_board', 'aframe_signs'),
            'signage' => array('correx_boards', 'perspex_boards', 'chromadek_signs', 'forex_pvc_board'),
            'board' => array('correx_boards', 'foam_board_posters', 'forex_pvc_board'),
            'welcome board' => array('welcome_boards'),
            'welcome' => array('welcome_boards'),
            'wedding' => array('welcome_boards', 'seating_charts', 'table_numbers', 'wedding_invitations'),
            'seating' => array('seating_charts'),
            'table.?plan' => array('seating_charts'),
            'table number' => array('table_numbers'),
            'invitation' => array('wedding_invitations'),
            'invite' => array('wedding_invitations'),
            'sticker' => array('custom_stickers'),
            'label' => array('product_labels', 'custom_stickers'),
            'product label' => array('product_labels'),
            't.?shirt' => array('tshirt_printing'),
            'tee' => array('tshirt_printing'),
            'golf shirt' => array('golf_shirts'),
            'hoodie' => array('hoodies'),
            'cap' => array('caps'),
            'hat' => array('caps'),
            'clothing' => array('tshirt_printing', 'golf_shirts', 'hoodies', 'caps'),
            'apparel' => array('tshirt_printing', 'golf_shirts', 'hoodies', 'caps'),
            'vehicle' => array('vehicle_decals', 'vehicle_magnets'),
            'car' => array('vehicle_decals', 'vehicle_magnets'),
            'magnet' => array('vehicle_magnets'),
            'decal' => array('vehicle_decals'),
            'wrap' => array('vehicle_decals'),
            'window' => array('window_vinyl'),
            'frost' => array('window_vinyl'),
            'mug' => array('branded_mugs'),
            'cup' => array('branded_mugs'),
            'pen' => array('branded_pens'),
            'calendar' => array('calendars'),
            'canvas' => array('canvas_prints'),
            'wall' => array('wall_decals'),
            'mural' => array('wall_decals'),
            'ncr' => array('ncr_books'),
            'invoice book' => array('ncr_books'),
            'receipt book' => array('ncr_books'),
            'duplicate' => array('ncr_books'),
            'triplicate' => array('ncr_books'),
            'notepad' => array('notepads'),
            'memo pad' => array('notepads'),
            'gazebo' => array('gazebos'),
            'flag' => array('feather_flags'),
            'teardrop' => array('feather_flags'),
            'feather' => array('feather_flags'),
            'tablecloth' => array('tablecloths'),
            'stamp' => array('rubber_stamps'),
            'rubber stamp' => array('rubber_stamps'),
            'logo' => array('logo_design'),
            'brand' => array('brand_identity', 'logo_design'),
            'social media' => array('social_media_design'),
            'facebook' => array('social_media_design'),
            'instagram' => array('social_media_design'),
            'website' => array('website_starter', 'website_business', 'website_ecommerce'),
            'web site' => array('website_starter', 'website_business'),
            'landing page' => array('website_landing'),
            'ecommerce' => array('website_ecommerce'),
            'online store' => array('website_ecommerce'),
            'online shop' => array('website_ecommerce'),
            'maintenance' => array('website_maintenance'),
            'design' => array('design_service', 'logo_design', 'social_media_design'),
            'graphic design' => array('design_service', 'custom_design_hourly'),
            'custom' => array('custom_design_hourly'),
            'delivery' => array('delivery'),
            'corporate gift' => array('branded_mugs', 'branded_pens', 'calendars'),
            'promo' => array('branded_mugs', 'branded_pens', 'caps'),
        );
        
        $found = array();
        foreach ($keyword_map as $keyword => $product_keys) {
            if (preg_match('/' . $keyword . '/i', $msg)) {
                foreach ($product_keys as $pk) {
                    if (isset($this->products[$pk]) && !isset($found[$pk])) {
                        $found[$pk] = $this->products[$pk];
                    }
                }
            }
        }
        
        return $found;
    }
    
    private function detect_category($msg) {
        $cat_keywords = array(
            'design' => array('design', 'logo', 'brand', 'graphic'),
            'business_cards' => array('business card', 'card'),
            'flyers' => array('flyer', 'leaflet', 'pamphlet'),
            'brochures' => array('brochure', 'booklet', 'catalogue'),
            'posters' => array('poster'),
            'banners' => array('banner'),
            'signage' => array('sign', 'signage', 'board'),
            'wedding' => array('wedding', 'event', 'welcome board', 'party'),
            'stickers' => array('sticker', 'label'),
            'apparel' => array('shirt', 'clothing', 'apparel', 'hoodie', 'cap', 'uniform'),
            'vehicle' => array('vehicle', 'car', 'bakkie', 'van', 'wrap'),
            'corporate' => array('mug', 'pen', 'gift', 'promo', 'calendar'),
            'canvas' => array('canvas', 'wall art', 'photo print'),
            'books' => array('ncr', 'invoice book', 'receipt', 'notepad'),
            'events' => array('gazebo', 'flag', 'exhibition', 'expo'),
            'websites' => array('website', 'web', 'online'),
            'services' => array('delivery', 'courier'),
        );
        
        foreach ($cat_keywords as $cat => $keywords) {
            foreach ($keywords as $kw) {
                if (strpos($msg, $kw) !== false) {
                    return $cat;
                }
            }
        }
        
        if (preg_match('/(print|printing)/i', $msg)) {
            return 'flyers';
        }
        
        return null;
    }
    
    private function show_category_products($category, &$ctx) {
        $cat_products = array();
        foreach ($this->products as $key => $p) {
            if (($p['category'] ?? '') === $category) {
                $cat_products[$key] = $p;
            }
        }
        
        if (empty($cat_products)) {
            $ctx['state'] = 'exploring';
            return array(
                'message' => "I don't have specific products in that category yet, but we can definitely help! Could you describe exactly what you need? I'll put together a custom quote for you.",
                'buttons' => array(),
            );
        }
        
        $cat_info = $this->categories[$category] ?? array('name' => ucfirst($category), 'emoji' => '📦');
        
        $text = "Great choice! Here's what we offer in **{$cat_info['emoji']} {$cat_info['name']}**:\n\n";
        
        $buttons = array();
        $i = 1;
        foreach ($cat_products as $key => $p) {
            $min = SBHA_Products::get_min_price($p);
            $text .= "{$i}. **{$p['name']}** — from R{$min}\n";
            if (count($buttons) < 5) {
                $buttons[] = array('text' => "{$i}. {$p['name']}", 'value' => $p['name']);
            }
            $i++;
        }
        
        $text .= "\nWhich one interests you? Just tell me the name or number.";
        
        $ctx['matched_products'] = $cat_products;
        $ctx['state'] = 'pick_product';
        
        return array(
            'message' => $text,
            'buttons' => $buttons,
        );
    }
    
    private function show_product_options($matches, $msg, &$ctx) {
        if (count($matches) <= 5) {
            $text = "I found a few options that might work:\n\n";
            $buttons = array();
            $i = 1;
            foreach ($matches as $key => $p) {
                $min = SBHA_Products::get_min_price($p);
                $text .= "{$i}. **{$p['name']}** — from R{$min}\n";
                $desc = $p['description'] ?? '';
                if ($desc) {
                    $text .= "   _{$desc}_\n";
                }
                $text .= "\n";
                if (count($buttons) < 5) {
                    $buttons[] = array('text' => "{$i}. {$p['name']}", 'value' => $p['name']);
                }
                $i++;
            }
            
            $text .= "Which one would you like? Tell me the number or name.";
            
            $ctx['matched_products'] = $matches;
            $ctx['state'] = 'pick_product';
            
            return array(
                'message' => $text,
                'buttons' => $buttons,
            );
        }
        
        $first_key = array_keys($matches)[0];
        return $this->show_product($first_key, $ctx);
    }
    
    private function show_product($key, &$ctx) {
        $product = $this->products[$key] ?? null;
        if (!$product) {
            return $this->greeting($ctx);
        }
        
        $is_digital = (
            !empty($product['is_design_service']) ||
            ($product['category'] ?? '') === 'design' ||
            ($product['category'] ?? '') === 'websites'
        );
        
        $ctx['current_product'] = $key;
        $ctx['product_name'] = $product['name'];
        $ctx['is_digital'] = $is_digital;
        $ctx['state'] = 'pick_variation';
        
        $text = "**{$product['name']}**\n";
        if (!empty($product['description'])) {
            $text .= "{$product['description']}\n";
        }
        $text .= "\n**Available options:**\n\n";
        
        $buttons = array();
        foreach ($product['variations'] as $i => $v) {
            $num = $i + 1;
            $text .= "{$num}. {$v['name']} — **R{$v['price']}**\n";
            if (count($buttons) < 5) {
                $label = strlen($v['name']) > 25 ? substr($v['name'], 0, 22) . '...' : $v['name'];
                $buttons[] = array('text' => "{$num}. R{$v['price']}", 'value' => (string)$num);
            }
        }
        
        $text .= "\nPick the option that suits you (number or name):";
        
        return array(
            'message' => $text,
            'buttons' => $buttons,
        );
    }
    
    private function handle_product_selection($msg, &$ctx) {
        $matched = $ctx['matched_products'] ?? array();
        
        if (preg_match('/^(\d+)/', $msg, $m)) {
            $idx = intval($m[1]) - 1;
            $keys = array_keys($matched);
            if (isset($keys[$idx])) {
                return $this->show_product($keys[$idx], $ctx);
            }
        }
        
        foreach ($matched as $key => $p) {
            if (stripos($msg, strtolower($p['name'])) !== false || 
                stripos(strtolower($p['name']), $msg) !== false) {
                return $this->show_product($key, $ctx);
            }
        }
        
        $new_matches = $this->find_matching_products($msg);
        if (!empty($new_matches)) {
            if (count($new_matches) === 1) {
                $key = array_keys($new_matches)[0];
                return $this->show_product($key, $ctx);
            }
            return $this->show_product_options($new_matches, $msg, $ctx);
        }
        
        return array(
            'message' => "I'm not sure which product you mean. Could you pick a number from the list above, or describe what you need differently?",
            'buttons' => array(),
        );
    }
    
    private function handle_variation_selection($msg, &$ctx) {
        $product = $this->products[$ctx['current_product']] ?? null;
        if (!$product) {
            return $this->greeting($ctx);
        }
        
        $selected = null;
        
        if (preg_match('/^(\d+)/', $msg, $m)) {
            $idx = intval($m[1]) - 1;
            if (isset($product['variations'][$idx])) {
                $selected = $product['variations'][$idx];
                $ctx['variation_idx'] = $idx;
            }
        }
        
        if (!$selected) {
            foreach ($product['variations'] as $i => $v) {
                $vname = strtolower($v['name']);
                if (stripos($msg, $vname) !== false || stripos($vname, $msg) !== false) {
                    $selected = $v;
                    $ctx['variation_idx'] = $i;
                    break;
                }
            }
        }
        
        if (!$selected) {
            $new_matches = $this->find_matching_products($msg);
            if (!empty($new_matches)) {
                if (count($new_matches) === 1) {
                    $key = array_keys($new_matches)[0];
                    return $this->show_product($key, $ctx);
                }
                return $this->show_product_options($new_matches, $msg, $ctx);
            }
            
            return array(
                'message' => "Please pick a number from the options above. For example, type **1** for the first option.",
                'buttons' => array(),
            );
        }
        
        $ctx['current_variation'] = $selected['name'];
        $ctx['current_price'] = $selected['price'];
        $ctx['current_sku'] = $selected['sku'] ?? '';
        
        if (preg_match('/^\d+\s/', $selected['name'])) {
            $ctx['current_qty'] = 1;
            return $this->after_variation_selected($ctx);
        }
        
        $ctx['state'] = 'ask_quantity';
        return array(
            'message' => "You selected: **{$selected['name']}** — R{$selected['price']}\n\nHow many do you need?",
            'buttons' => array(
                array('text' => '1', 'value' => '1'),
                array('text' => '2', 'value' => '2'),
                array('text' => '5', 'value' => '5'),
                array('text' => '10', 'value' => '10'),
            ),
        );
    }
    
    private function handle_quantity($msg, &$ctx) {
        preg_match('/(\d+)/', $msg, $m);
        $ctx['current_qty'] = isset($m[1]) ? max(1, intval($m[1])) : 1;
        return $this->after_variation_selected($ctx);
    }
    
    private function after_variation_selected(&$ctx) {
        $subtotal = $ctx['current_price'] * $ctx['current_qty'];
        $qty_text = $ctx['current_qty'] > 1 ? "{$ctx['current_qty']}x " : '';
        
        if ($ctx['is_digital']) {
            $ctx['delivery'] = 'Digital delivery';
            $ctx['state'] = 'ask_purpose';
            return array(
                'message' => "**{$qty_text}{$ctx['current_variation']}** — R{$subtotal}\n\nTell me a bit about your project so I can make sure we deliver exactly what you need:\n- What's it for? (business, event, personal?)\n- Any specific style, colours, or references?",
                'buttons' => array(),
            );
        }
        
        $ctx['state'] = 'ask_design';
        return array(
            'message' => "**{$qty_text}{$ctx['current_variation']}** — R{$subtotal}\n\nDo you have your own design file ready, or do you need us to design it for you?\n\n(Design service starts from **R350**)",
            'buttons' => array(
                array('text' => '📎 I have my own design', 'value' => 'I have my own design file'),
                array('text' => '🎨 I need design (R350+)', 'value' => 'I need you to design it'),
                array('text' => "🤔 I'm not sure yet", 'value' => 'not sure about design'),
            ),
        );
    }
    
    private function handle_design_question($msg, &$ctx) {
        if (preg_match('/(have|own|ready|upload|file|provide|send|my design)/i', $msg)) {
            $ctx['has_own_file'] = true;
            $ctx['needs_design'] = false;
            return $this->ask_purpose_or_delivery($ctx);
        }
        
        if (preg_match('/(need|want|design|create|make|please)/i', $msg) && 
            !preg_match('/(not sure|maybe|don\'t know)/i', $msg)) {
            $ctx['needs_design'] = true;
            $ctx['has_own_file'] = false;
            $ctx['state'] = 'get_design_brief';
            
            return array(
                'message' => "No problem! Our design service is **R350** for a standard design.\n\nPlease describe what you'd like:\n- What text/info should be on it?\n- Any colours or style preferences?\n- Do you have a logo? (you can upload files when submitting the quote)",
                'buttons' => array(),
            );
        }
        
        $ctx['needs_design'] = null;
        $ctx['has_own_file'] = null;
        return $this->ask_purpose_or_delivery($ctx);
    }
    
    private function handle_design_brief($original, &$ctx) {
        $ctx['design_brief'] = $original;
        return $this->ask_purpose_or_delivery($ctx);
    }
    
    private function ask_purpose_or_delivery(&$ctx) {
        if ($ctx['is_digital']) {
            return $this->proceed_to_delivery($ctx);
        }
        
        $ctx['state'] = 'ask_purpose';
        return array(
            'message' => "What's this for? (e.g., shop opening, wedding, corporate event, general marketing)\n\nThis helps me make sure everything is right for your needs.",
            'buttons' => array(
                array('text' => '🏢 Business/Marketing', 'value' => 'business marketing'),
                array('text' => '💒 Wedding/Event', 'value' => 'wedding event'),
                array('text' => '🎉 Party/Celebration', 'value' => 'party celebration'),
                array('text' => '📢 Promotion/Sale', 'value' => 'promotion sale'),
                array('text' => '⏭️ Skip', 'value' => 'skip'),
            ),
        );
    }
    
    private function handle_purpose($original, &$ctx) {
        if (strtolower(trim($original)) !== 'skip') {
            $ctx['purpose'] = $original;
        }
        
        if (preg_match('/(wedding|event|party|celebration|ceremony)/i', $original)) {
            $ctx['state'] = 'ask_event_date';
            return array(
                'message' => "When is the event? This helps us plan production time.",
                'buttons' => array(
                    array('text' => 'Within 1 week', 'value' => 'within 1 week'),
                    array('text' => 'Within 2 weeks', 'value' => 'within 2 weeks'),
                    array('text' => 'Within a month', 'value' => 'within a month'),
                    array('text' => 'No rush', 'value' => 'no rush'),
                ),
            );
        }
        
        return $this->proceed_to_delivery($ctx);
    }
    
    private function handle_event_date($original, &$ctx) {
        $ctx['event_date'] = $original;
        return $this->proceed_to_delivery($ctx);
    }
    
    private function proceed_to_delivery(&$ctx) {
        if ($ctx['is_digital']) {
            $ctx['delivery'] = 'Digital delivery (email)';
            return $this->ask_special_notes($ctx);
        }
        
        $ctx['state'] = 'ask_delivery';
        return array(
            'message' => "How would you like to receive your order?\n\n📍 Our shop: **16 Harding St, Newcastle, 2940**\n🚚 Delivery starts from **R160**",
            'buttons' => array(
                array('text' => '🏪 Collect from shop (Free)', 'value' => 'collect'),
                array('text' => '🚚 Deliver to me', 'value' => 'deliver to me'),
            ),
        );
    }
    
    private function handle_delivery($msg, &$ctx) {
        if (preg_match('/(deliver|ship|send|courier)/i', $msg)) {
            $ctx['state'] = 'get_address';
            return array(
                'message' => "Please share your delivery address:",
                'buttons' => array(),
            );
        }
        
        $ctx['delivery'] = 'Collection — 16 Harding St, Newcastle, 2940';
        return $this->ask_special_notes($ctx);
    }
    
    private function handle_address($original, &$ctx) {
        $ctx['delivery'] = 'Deliver to: ' . $original;
        $ctx['delivery_address'] = $original;
        return $this->ask_special_notes($ctx);
    }
    
    private function ask_special_notes(&$ctx) {
        $ctx['state'] = 'ask_notes';
        return array(
            'message' => "Anything else I should know about this order? (special instructions, preferred finish, urgency, etc.)\n\nOr say **no** to continue.",
            'buttons' => array(
                array('text' => '✅ No, that\'s everything', 'value' => 'no'),
                array('text' => '⚡ It\'s urgent', 'value' => 'This is urgent, I need it ASAP'),
            ),
        );
    }
    
    private function handle_notes($original, &$ctx) {
        $low = strtolower(trim($original));
        if ($low !== 'no' && $low !== 'none' && $low !== 'nope' && $low !== 'nothing' && $low !== 'skip') {
            $ctx['special_notes'] = $original;
        }
        return $this->add_to_cart($ctx);
    }
    
    private function add_to_cart(&$ctx) {
        $design_cost = 0;
        if ($ctx['needs_design'] === true) {
            $design_cost = 350;
        }
        
        $item_total = ($ctx['current_price'] * $ctx['current_qty']) + $design_cost;
        
        $ctx['cart'][] = array(
            'product_key' => $ctx['current_product'],
            'product_name' => $ctx['product_name'],
            'variant_name' => $ctx['current_variation'],
            'sku' => $ctx['current_sku'] ?? '',
            'quantity' => $ctx['current_qty'],
            'unit_price' => $ctx['current_price'],
            'needs_design' => $ctx['needs_design'] === true,
            'design_cost' => $design_cost,
            'design_brief' => $ctx['design_brief'] ?? '',
            'has_own_file' => $ctx['has_own_file'] === true,
            'purpose' => $ctx['purpose'] ?? '',
            'event_date' => $ctx['event_date'] ?? '',
            'delivery' => $ctx['delivery'] ?? '',
            'delivery_address' => $ctx['delivery_address'] ?? '',
            'special_notes' => $ctx['special_notes'] ?? '',
            'item_total' => $item_total,
        );
        
        $cart_total = 0;
        $summary = "";
        foreach ($ctx['cart'] as $ci) {
            $cart_total += $ci['item_total'];
            $qty_label = ($ci['quantity'] > 1) ? "{$ci['quantity']}× " : '';
            $summary .= "• {$qty_label}{$ci['variant_name']} — R{$ci['item_total']}";
            if ($ci['needs_design']) $summary .= " (incl. design)";
            $summary .= "\n";
        }
        
        $ctx['current_product'] = null;
        $ctx['current_variation'] = null;
        $ctx['current_sku'] = '';
        $ctx['design_brief'] = '';
        $ctx['purpose'] = '';
        $ctx['event_date'] = '';
        $ctx['special_notes'] = '';
        $ctx['delivery'] = '';
        $ctx['delivery_address'] = '';
        $ctx['needs_design'] = null;
        $ctx['has_own_file'] = null;
        $ctx['state'] = 'ask_more';
        
        return array(
            'message' => "Added to your quote!\n\n**Current Quote:**\n{$summary}\n**Estimated Total: R{$cart_total}**\n\nWould you like to add anything else, or are you ready to submit?",
            'buttons' => array(
                array('text' => '➕ Add more items', 'value' => 'add more'),
                array('text' => '✅ Submit quote', 'value' => 'done'),
            ),
        );
    }
    
    private function handle_ask_more($msg, &$ctx) {
        if (preg_match('/(yes|more|add|another|sure|browse)/i', $msg)) {
            $ctx['state'] = 'greeting';
            return array(
                'message' => "What else do you need? You can tell me or pick a category:",
                'buttons' => array(
                    array('text' => '💳 Business Cards', 'value' => 'I need business cards'),
                    array('text' => '📄 Flyers', 'value' => 'I need flyers'),
                    array('text' => '🪧 Signage', 'value' => 'I need signage'),
                    array('text' => '🎪 Banners', 'value' => 'I need banners'),
                    array('text' => '👕 Clothing', 'value' => 'I need t-shirts'),
                    array('text' => '🎨 Design', 'value' => 'I need design services'),
                ),
            );
        }
        
        return $this->finalize_quote($ctx);
    }
    
    private function finalize_quote(&$ctx) {
        if (empty($ctx['cart'])) {
            return $this->greeting($ctx);
        }
        
        $total = 0;
        $summary = "";
        $has_delivery = false;
        $delivery_info = '';
        
        foreach ($ctx['cart'] as $item) {
            $total += $item['item_total'];
            $qty_label = ($item['quantity'] > 1) ? "{$item['quantity']}× " : '';
            $summary .= "• {$qty_label}{$item['variant_name']} — R{$item['item_total']}";
            if ($item['needs_design']) $summary .= " _(incl. design R{$item['design_cost']})_";
            $summary .= "\n";
            if (!empty($item['delivery']) && !$has_delivery) {
                $has_delivery = true;
                $delivery_info = $item['delivery'];
            }
        }
        
        $ctx['state'] = 'done';
        
        $text = "**Quote Summary**\n\n{$summary}\n";
        if ($delivery_info) {
            $text .= "📦 {$delivery_info}\n";
        }
        $text .= "\n**Estimated Total: R{$total}**\n";
        $text .= "_Final price confirmed after admin review._\n\n";
        $text .= "Click below to submit your quote. We'll review it and get back to you!";
        
        return array(
            'message' => $text,
            'buttons' => array(),
            'show_quote_form' => true,
            'quote_data' => array(
                'items' => $ctx['cart'],
                'estimate_total' => $total,
                'delivery_info' => $delivery_info,
                'conversation' => $ctx['conversation'] ?? array(),
            ),
        );
    }
}
