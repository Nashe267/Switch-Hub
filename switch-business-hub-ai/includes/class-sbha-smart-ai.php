<?php
/**
 * Smart AI - guided quote assistant for print/signage/design.
 *
 * @package SwitchBusinessHub
 * @version 2.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class SBHA_Smart_AI {

    private $products = array();
    private $keyword_map = array();
    private $design_fee = 350.0;
    private $custom_hourly_rate = 350.0;
    private $delivery_local_fee = 160.0;
    private $delivery_regional_fee = 245.0;
    private $delivery_national_fee = 403.0;

    public function __construct() {
        require_once plugin_dir_path(__FILE__) . 'class-sbha-products.php';
        $this->products = SBHA_Products::get_all();
        $this->keyword_map = $this->build_keyword_map();
        $this->load_fee_defaults();
    }

    public function process($message, $context = array()) {
        $ctx = $this->build_context($context);
        $original_message = trim((string) $message);
        $msg = strtolower(trim(wp_strip_all_tags($message)));

        if ($msg === '') {
            return $this->format_response(
                "Please tell me what you need (for example: wedding welcome board, business cards, flyer printing).",
                $ctx,
                $this->default_quick_buttons()
            );
        }

        if ($this->is_reset_command($msg)) {
            return $this->greeting($this->build_context(array()));
        }

        if ($this->is_finalize_command($msg) && !empty($ctx['cart'])) {
            return $this->finalize_quote($ctx);
        }

        switch ($ctx['state']) {
            case 'pick_variant':
                return $this->handle_variant_selection($msg, $ctx);
            case 'pick_quantity':
                return $this->handle_quantity($msg, $ctx);
            case 'pick_design':
                return $this->handle_design_choice($original_message, $ctx);
            case 'pick_delivery':
                return $this->handle_delivery_choice($original_message, $ctx);
            case 'collect_delivery_location':
                return $this->handle_delivery_location($original_message, $ctx);
            case 'collect_due_date':
                return $this->handle_due_date($original_message, $ctx);
            case 'collect_notes':
                return $this->handle_notes($original_message, $ctx);
            case 'ask_more':
                return $this->handle_more_items($msg, $ctx);
            case 'confirm_custom_item':
                return $this->handle_custom_confirmation($msg, $ctx);
            case 'detect_product':
            default:
                return $this->handle_product_detection($msg, $original_message, $ctx);
        }
    }

    private function build_context($context) {
        $defaults = array(
            'state' => 'detect_product',
            'cart' => array(),
            'current' => array(),
            'event_type' => '',
            'event_date' => '',
            'delivery_location' => '',
            'special_notes' => '',
            'has_custom_item' => false
        );

        $ctx = wp_parse_args(is_array($context) ? $context : array(), $defaults);

        if (!is_array($ctx['cart'])) {
            $ctx['cart'] = array();
        }
        if (!is_array($ctx['current'])) {
            $ctx['current'] = array();
        }

        return $ctx;
    }

    private function greeting($ctx) {
        $ctx['state'] = 'detect_product';
        return $this->format_response(
            "Hi, I can guide your quote step-by-step for print, signage, design and web. Tell me what you need and I will suggest sizes/materials with estimated prices (for example wedding welcome boards in A1 Correx, Forex or Perspex).",
            $ctx,
            $this->default_quick_buttons()
        );
    }

    private function default_quick_buttons() {
        return array(
            array('text' => 'Wedding welcome board', 'value' => 'wedding welcome board'),
            array('text' => 'Business cards', 'value' => 'business cards'),
            array('text' => 'Flyers', 'value' => 'flyers'),
            array('text' => 'Website', 'value' => 'website'),
            array('text' => 'Custom job estimate', 'value' => 'custom job')
        );
    }

    private function handle_product_detection($msg, $original, $ctx) {
        $product_key = $this->match_product_key($msg);

        if (!$product_key) {
            $suggestions = $this->suggest_products($msg);
            $ctx['state'] = 'confirm_custom_item';
            $ctx['current'] = array(
                'custom_brief' => $original,
                'estimated_hours' => $this->estimate_custom_hours($msg),
                'product_name' => 'Custom Job',
                'variant_name' => 'Custom production/design work',
                'variant_sku' => 'CJOB-HR'
            );

            $help = "I could not match that to an exact catalog item yet. I can still estimate it as a custom job at R" .
                number_format($this->custom_hourly_rate, 0) . "/hour and include it in your quote.";
            $help .= "\n(Indexed price references include Vistaprinters, Printulu, TheMediaMafia and ImpressWeb sources in your catalog.)";

            if (!empty($suggestions)) {
                $help .= "\n\nClosest catalog matches:\n";
                foreach ($suggestions as $name) {
                    $help .= "• {$name}\n";
                }
            }

            $help .= "\n\nAdd this as a custom quote line?";

            return $this->format_response(
                $help,
                $ctx,
                array(
                    array('text' => 'Yes, add custom item', 'value' => 'yes add it'),
                    array('text' => 'Show available products', 'value' => 'show products')
                )
            );
        }

        $product = $this->products[$product_key];
        $ctx['current'] = array(
            'product_key' => $product_key,
            'product_name' => $product['name'],
            'product_category' => $product['category'] ?? '',
            'is_digital' => $this->is_digital_product($product),
            'is_design_service' => !empty($product['is_design_service'])
        );
        $ctx['state'] = 'pick_variant';

        return $this->present_variants($product, $ctx);
    }

    private function present_variants($product, $ctx) {
        $text = "**" . ($product['name'] ?? 'Product') . "**\n";
        if (!empty($product['description'])) {
            $text .= $product['description'] . "\n";
        }
        $text .= "\nPlease choose a size/option:\n";

        $buttons = array();
        $variations = $product['variations'] ?? array();
        foreach ($variations as $idx => $variation) {
            $line_no = $idx + 1;
            $price = number_format(floatval($variation['price'] ?? 0), 2);
            $sku = !empty($variation['sku']) ? " | SKU: {$variation['sku']}" : '';
            $text .= "{$line_no}. {$variation['name']} - R{$price}{$sku}\n";
            if ($idx < 5) {
                $buttons[] = array(
                    'text' => "{$line_no}. R" . number_format(floatval($variation['price'] ?? 0), 0),
                    'value' => (string) $line_no
                );
            }
        }

        if (empty($variations)) {
            $text .= "No variants loaded for this product. I can add it as a custom item estimate.";
            $ctx['state'] = 'confirm_custom_item';
        }

        return $this->format_response($text, $ctx, $buttons);
    }

    private function handle_variant_selection($msg, $ctx) {
        $product_key = $ctx['current']['product_key'] ?? '';
        if (empty($product_key) || empty($this->products[$product_key])) {
            return $this->greeting($ctx);
        }

        $product = $this->products[$product_key];
        $variations = $product['variations'] ?? array();
        if (empty($variations)) {
            $ctx['state'] = 'confirm_custom_item';
            return $this->format_response(
                "I could not find variants for this product. Add it as a custom estimate?",
                $ctx,
                array(
                    array('text' => 'Yes', 'value' => 'yes'),
                    array('text' => 'Start over', 'value' => 'reset')
                )
            );
        }

        $selected_index = $this->match_variant_index($msg, $variations);
        if ($selected_index === null) {
            return $this->format_response(
                "Please select the option number (for example 1, 2, 3) or type the option name.",
                $ctx
            );
        }

        $variant = $variations[$selected_index];
        $ctx['current']['variation_idx'] = $selected_index;
        $ctx['current']['variant_name'] = $variant['name'] ?? '';
        $ctx['current']['variant_sku'] = $variant['sku'] ?? '';
        $ctx['current']['unit_price'] = floatval($variant['price'] ?? 0);
        $ctx['state'] = 'pick_quantity';

        return $this->format_response(
            "Great choice: **{$ctx['current']['variant_name']}** (R" .
            number_format($ctx['current']['unit_price'], 2) .
            ").\nHow many do you need?",
            $ctx,
            array(
                array('text' => '1', 'value' => '1'),
                array('text' => '2', 'value' => '2'),
                array('text' => '5', 'value' => '5'),
                array('text' => '10', 'value' => '10')
            )
        );
    }

    private function handle_quantity($msg, $ctx) {
        $qty = $this->extract_number($msg);
        if ($qty < 1) {
            return $this->format_response("Please send a valid quantity (1 or more).", $ctx);
        }

        $ctx['current']['quantity'] = $qty;
        $ctx['state'] = 'pick_design';

        return $this->format_response(
            "Perfect. Do you need design service, or will you provide your own print-ready file?",
            $ctx,
            array(
                array('text' => 'Need design service', 'value' => 'need design'),
                array('text' => 'I have my own file', 'value' => 'own file')
            )
        );
    }

    private function handle_design_choice($message, $ctx) {
        $msg = strtolower($message);
        $needs_design = (strpos($msg, 'design') !== false && strpos($msg, 'own') === false) || strpos($msg, 'need') !== false;
        if (strpos($msg, 'own file') !== false || strpos($msg, 'print-ready') !== false || strpos($msg, 'my file') !== false) {
            $needs_design = false;
        }

        $ctx['current']['needs_design'] = $needs_design ? 1 : 0;
        $ctx['current']['design_details'] = $needs_design ? 'Design service requested' : 'Client will supply print-ready file';
        $ctx['current']['design_fee'] = $needs_design ? $this->design_fee : 0.0;
        $ctx['current']['file_upload_note'] = $needs_design
            ? 'Design brief/images can be uploaded when sending quote.'
            : 'Client file upload required before production.';

        if (!empty($ctx['current']['is_digital']) || !empty($ctx['current']['is_design_service'])) {
            $ctx['current']['delivery_method'] = 'Digital delivery';
            $ctx['current']['delivery_fee'] = 0.0;
            $ctx['state'] = 'collect_due_date';

            return $this->format_response(
                "Noted. When do you need this completed?",
                $ctx,
                array(
                    array('text' => 'Within 24-48 hours', 'value' => 'within 2 days'),
                    array('text' => 'This week', 'value' => 'this week'),
                    array('text' => 'Specific date', 'value' => 'i will provide a date')
                )
            );
        }

        $ctx['state'] = 'pick_delivery';
        return $this->format_response(
            "How should we handle delivery?",
            $ctx,
            array(
                array('text' => 'Collection (R0)', 'value' => 'collection'),
                array('text' => 'Local delivery (from R160)', 'value' => 'local delivery'),
                array('text' => 'Regional delivery', 'value' => 'regional delivery')
            )
        );
    }

    private function handle_delivery_choice($message, $ctx) {
        $msg = strtolower($message);

        if (strpos($msg, 'collect') !== false) {
            $ctx['current']['delivery_method'] = 'Collection from Switch Graphics';
            $ctx['current']['delivery_fee'] = 0.0;
            $ctx['state'] = 'collect_due_date';
            return $this->format_response("Great. When do you need this ready?", $ctx);
        }

        if (strpos($msg, 'regional') !== false) {
            $ctx['current']['delivery_method'] = 'Regional delivery';
            $ctx['current']['delivery_fee'] = $this->delivery_regional_fee;
            $ctx['state'] = 'collect_delivery_location';
            return $this->format_response("Please share the full delivery area/address.", $ctx);
        }

        if (strpos($msg, 'national') !== false || strpos($msg, 'courier') !== false) {
            $ctx['current']['delivery_method'] = 'National courier delivery';
            $ctx['current']['delivery_fee'] = $this->delivery_national_fee;
            $ctx['state'] = 'collect_delivery_location';
            return $this->format_response("Please share full delivery address and postcode.", $ctx);
        }

        if (strpos($msg, 'local') !== false || strpos($msg, 'delivery') !== false || strpos($msg, 'deliver') !== false) {
            $ctx['current']['delivery_method'] = 'Local delivery';
            $ctx['current']['delivery_fee'] = $this->delivery_local_fee;
            $ctx['state'] = 'collect_delivery_location';
            return $this->format_response("Please share your delivery address.", $ctx);
        }

        return $this->format_response(
            "Please choose collection or delivery.",
            $ctx,
            array(
                array('text' => 'Collection', 'value' => 'collection'),
                array('text' => 'Local delivery', 'value' => 'local delivery')
            )
        );
    }

    private function handle_delivery_location($message, $ctx) {
        $address = trim($message);
        if ($address === '') {
            return $this->format_response("Please enter a delivery address or area.", $ctx);
        }

        $ctx['current']['delivery_location'] = $address;
        $ctx['delivery_location'] = $address;

        // Promote to regional charge if non-local location is obvious.
        if (!empty($ctx['current']['delivery_method']) && strpos(strtolower($ctx['current']['delivery_method']), 'local') !== false) {
            $addr = strtolower($address);
            if (strpos($addr, 'newcastle') === false) {
                $ctx['current']['delivery_method'] = 'Regional delivery';
                $ctx['current']['delivery_fee'] = $this->delivery_regional_fee;
            }
        }

        $ctx['state'] = 'collect_due_date';
        return $this->format_response("Thanks. When do you need this job completed?", $ctx);
    }

    private function handle_due_date($message, $ctx) {
        $value = trim($message);
        if ($value === '') {
            return $this->format_response("Please share your preferred due date/timeframe.", $ctx);
        }

        $ctx['current']['due_date'] = $value;
        if (empty($ctx['event_date'])) {
            $ctx['event_date'] = $value;
        }

        // Wedding/event context
        if (
            empty($ctx['event_type'])
            && (
                strpos(strtolower($ctx['current']['product_name'] ?? ''), 'wedding') !== false
                || ($ctx['current']['product_category'] ?? '') === 'wedding'
            )
        ) {
            $ctx['event_type'] = 'Wedding';
        }

        $ctx['state'] = 'collect_notes';
        return $this->format_response(
            "Any extra notes? (names on artwork, preferred colors, material preference, special instructions). Type \"none\" if no notes.",
            $ctx
        );
    }

    private function handle_notes($message, $ctx) {
        $note = trim($message);
        if ($note !== '' && strtolower($note) !== 'none' && strtolower($note) !== 'no') {
            $ctx['current']['notes'] = $note;
            $ctx['special_notes'] = trim(($ctx['special_notes'] ? $ctx['special_notes'] . ' | ' : '') . $note);
        }

        $ctx['cart'][] = $this->build_cart_item($ctx['current']);
        $line_total = $this->calculate_item_total($ctx['current']);

        $ctx['current'] = array();
        $ctx['state'] = 'ask_more';

        return $this->format_response(
            "Added to quote. Current line estimate: R" . number_format($line_total, 2) .
            ".\nNeed another product/service?",
            $ctx,
            array(
                array('text' => 'Add another item', 'value' => 'yes add more'),
                array('text' => 'Finish quote', 'value' => 'done')
            )
        );
    }

    private function handle_more_items($msg, $ctx) {
        $candidate = $this->match_product_key($msg);
        if (!empty($candidate)) {
            $ctx['state'] = 'detect_product';
            return $this->handle_product_detection($msg, $msg, $ctx);
        }

        if ($this->is_yes($msg) || strpos($msg, 'add') !== false || strpos($msg, 'another') !== false) {
            $ctx['state'] = 'detect_product';
            return $this->format_response(
                "Great, what else should I add?",
                $ctx,
                $this->default_quick_buttons()
            );
        }

        return $this->finalize_quote($ctx);
    }

    private function handle_custom_confirmation($msg, $ctx) {
        if (strpos($msg, 'show products') !== false || strpos($msg, 'catalog') !== false) {
            $ctx['state'] = 'detect_product';
            return $this->greeting($ctx);
        }

        if ($this->is_yes($msg) || strpos($msg, 'add') !== false) {
            $hours = max(1, intval($ctx['current']['estimated_hours'] ?? 1));
            $ctx['current']['quantity'] = $hours;
            $ctx['current']['unit_price'] = $this->custom_hourly_rate;
            $ctx['current']['needs_design'] = 0;
            $ctx['current']['design_fee'] = 0;
            $ctx['current']['delivery_fee'] = 0;
            $ctx['current']['delivery_method'] = 'To be confirmed';
            $ctx['current']['notes'] = 'Custom request: ' . ($ctx['current']['custom_brief'] ?? '');
            $ctx['current']['variant_name'] = "Custom work ({$hours} hour estimate)";
            $ctx['has_custom_item'] = true;
            $ctx['state'] = 'collect_due_date';

            return $this->format_response(
                "Done. I added a custom estimate at R" . number_format($this->custom_hourly_rate, 0) .
                "/hour (" . $hours . " hour estimate). When do you need this?",
                $ctx
            );
        }

        $ctx['state'] = 'detect_product';
        return $this->format_response(
            "No problem. Tell me the product/service you want and I will match it.",
            $ctx,
            $this->default_quick_buttons()
        );
    }

    private function finalize_quote($ctx) {
        if (empty($ctx['cart'])) {
            return $this->greeting($ctx);
        }

        $summary_lines = array();
        $total = 0.0;
        $delivery_needed = 0;
        $delivery_locations = array();
        $needs_design = 0;
        $design_details = array();

        foreach ($ctx['cart'] as $item) {
            $line_total = $this->calculate_item_total($item);
            $total += $line_total;

            $summary_lines[] = sprintf(
                "• %s | %s | Qty %d | R%s",
                $item['product_name'],
                $item['variant_name'],
                intval($item['quantity']),
                number_format($line_total, 2)
            );

            if (!empty($item['delivery_fee'])) {
                $delivery_needed = 1;
            }
            if (!empty($item['delivery_location'])) {
                $delivery_locations[] = $item['delivery_location'];
            }
            if (!empty($item['needs_design'])) {
                $needs_design = 1;
                $design_details[] = "{$item['product_name']}: design service requested";
            }
        }

        $ctx['state'] = 'done';

        $message = "**Quote estimate summary**\n\n" .
            implode("\n", $summary_lines) .
            "\n\n**Estimated total: R" . number_format($total, 2) . "**\n" .
            "This is an estimate from current catalog pricing (with markup and service assumptions). Final amount is confirmed after file/artwork review.";

        $quote_data = array(
            'items' => $ctx['cart'],
            'estimate_total' => $total,
            'needs_design' => $needs_design,
            'design_details' => implode(' | ', $design_details),
            'delivery_needed' => $delivery_needed,
            'delivery_location' => implode(' | ', array_unique($delivery_locations)),
            'event_type' => $ctx['event_type'] ?? '',
            'event_date' => $ctx['event_date'] ?? '',
            'special_notes' => $ctx['special_notes'] ?? ''
        );

        return $this->format_response($message, $ctx, array(), true, $quote_data);
    }

    private function build_cart_item($current) {
        $quantity = max(1, intval($current['quantity'] ?? 1));
        $unit_price = floatval($current['unit_price'] ?? 0);

        return array(
            'product_key' => $current['product_key'] ?? '',
            'product_name' => $current['product_name'] ?? 'Custom Item',
            'variant_name' => $current['variant_name'] ?? 'Standard',
            'variant_sku' => $current['variant_sku'] ?? '',
            'quantity' => $quantity,
            'unit_price' => $unit_price,
            'needs_design' => !empty($current['needs_design']) ? 1 : 0,
            'design_fee' => floatval($current['design_fee'] ?? 0),
            'design_details' => $current['design_details'] ?? '',
            'delivery_method' => $current['delivery_method'] ?? '',
            'delivery_fee' => floatval($current['delivery_fee'] ?? 0),
            'delivery_location' => $current['delivery_location'] ?? '',
            'due_date' => $current['due_date'] ?? '',
            'notes' => $current['notes'] ?? '',
            'file_upload_note' => $current['file_upload_note'] ?? ''
        );
    }

    private function calculate_item_total($item) {
        $subtotal = floatval($item['unit_price'] ?? 0) * max(1, intval($item['quantity'] ?? 1));
        $design_fee = floatval($item['design_fee'] ?? 0);
        $delivery_fee = floatval($item['delivery_fee'] ?? 0);
        return $subtotal + $design_fee + $delivery_fee;
    }

    private function match_product_key($msg) {
        foreach ($this->keyword_map as $keyword => $product_key) {
            if (strpos($msg, $keyword) !== false && isset($this->products[$product_key])) {
                return $product_key;
            }
        }

        // Fallback search by product name tokens.
        $best_key = '';
        $best_score = 0;
        foreach ($this->products as $key => $product) {
            $name = strtolower($product['name'] ?? '');
            if ($name === '') {
                continue;
            }

            $score = 0;
            $tokens = preg_split('/\s+/', preg_replace('/[^a-z0-9 ]/', ' ', $name));
            foreach ($tokens as $token) {
                $token = trim($token);
                if ($token === '' || strlen($token) < 3) {
                    continue;
                }
                if (strpos($msg, $token) !== false) {
                    $score++;
                }
            }

            if ($score > $best_score) {
                $best_score = $score;
                $best_key = $key;
            }
        }

        return $best_score > 0 ? $best_key : '';
    }

    private function suggest_products($msg) {
        $results = array();
        foreach ($this->products as $product) {
            $name = strtolower($product['name'] ?? '');
            if ($name && (strpos($name, $msg) !== false || strpos($msg, substr($name, 0, 4)) !== false)) {
                $results[] = $product['name'];
            }
            if (count($results) >= 3) {
                break;
            }
        }
        return $results;
    }

    private function match_variant_index($msg, $variations) {
        if (preg_match('/^(\d+)/', $msg, $matches)) {
            $index = intval($matches[1]) - 1;
            if (isset($variations[$index])) {
                return $index;
            }
        }

        foreach ($variations as $idx => $variation) {
            $name = strtolower($variation['name'] ?? '');
            if ($name !== '' && (strpos($msg, $name) !== false || strpos($name, $msg) !== false)) {
                return $idx;
            }
        }

        return null;
    }

    private function is_digital_product($product) {
        $category = strtolower($product['category'] ?? '');
        if ($category === 'websites' || $category === 'design') {
            return true;
        }
        if (!empty($product['product_type']) && $product['product_type'] === 'digital') {
            return true;
        }
        if (!empty($product['is_design_service'])) {
            return true;
        }
        return false;
    }

    private function extract_number($msg) {
        if (preg_match('/(\d+)/', $msg, $matches)) {
            return intval($matches[1]);
        }
        return 0;
    }

    private function is_finalize_command($msg) {
        return (bool) preg_match('/\b(done|finish|final|submit|checkout|no more|quote now)\b/i', $msg);
    }

    private function is_reset_command($msg) {
        return (bool) preg_match('/\b(reset|start over|clear|new quote)\b/i', $msg);
    }

    private function is_yes($msg) {
        return (bool) preg_match('/\b(yes|yep|yeah|sure|okay|ok)\b/i', $msg);
    }

    private function estimate_custom_hours($msg) {
        $hours = 1;
        if (preg_match('/\b(web|website|ecommerce|app)\b/', $msg)) {
            $hours = 8;
        } elseif (preg_match('/\b(vehicle|wrap|branding)\b/', $msg)) {
            $hours = 4;
        } elseif (preg_match('/\b(sign|board|banner|wedding)\b/', $msg)) {
            $hours = 2;
        }
        return $hours;
    }

    private function load_fee_defaults() {
        if (!empty($this->products['design_service']['variations'][0]['price'])) {
            $this->design_fee = floatval($this->products['design_service']['variations'][0]['price']);
        }

        if (!empty($this->products['custom_jobs_hourly']['variations'][0]['price'])) {
            $this->custom_hourly_rate = floatval($this->products['custom_jobs_hourly']['variations'][0]['price']);
        }

        if (!empty($this->products['delivery']['variations'])) {
            $delivery_vars = $this->products['delivery']['variations'];
            $this->delivery_local_fee = floatval($delivery_vars[0]['price'] ?? $this->delivery_local_fee);
            $this->delivery_regional_fee = floatval($delivery_vars[1]['price'] ?? $this->delivery_regional_fee);
            $this->delivery_national_fee = floatval($delivery_vars[2]['price'] ?? $this->delivery_national_fee);
        }
    }

    private function build_keyword_map() {
        return array(
            'wedding welcome board' => 'wedding_welcome_boards',
            'welcome board' => 'wedding_welcome_boards',
            'welcome sign' => 'wedding_welcome_boards',
            'seating chart' => 'wedding_seating_charts',
            'perspex' => 'wedding_welcome_boards',
            'correx' => 'correx_boards',
            'forex' => 'correx_boards',
            'business card' => 'standard_business_cards',
            'cards' => 'standard_business_cards',
            'flyer' => 'a5_flyers',
            'leaflet' => 'a5_flyers',
            'brochure' => 'brochures',
            'letterhead' => 'letterheads',
            'compliment slip' => 'compliment_slips',
            'postcard' => 'postcards',
            'banner' => 'pvc_banners',
            'pull up banner' => 'pullup_banners',
            'fabric banner' => 'fabric_banners',
            'sticker' => 'custom_stickers',
            'label' => 'custom_stickers',
            'ncr' => 'ncr_books',
            'invoice book' => 'ncr_books',
            'vehicle decal' => 'vehicle_decals',
            'car branding' => 'vehicle_decals',
            'logo' => 'design_logo',
            'brand package' => 'design_brand',
            'social media' => 'design_social',
            'website' => 'website_starter',
            'web design' => 'website_business',
            'custom job' => 'custom_jobs_hourly',
            'delivery' => 'delivery'
        );
    }

    private function format_response($message, $context, $buttons = array(), $show_quote_form = false, $quote_data = null) {
        return array(
            'message' => $message,
            'buttons' => $buttons,
            'context' => $context,
            'show_quote_form' => $show_quote_form,
            'quote_data' => $quote_data
        );
    }
}
