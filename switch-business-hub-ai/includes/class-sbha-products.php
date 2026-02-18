<?php
/**
 * Switch Graphics Product Catalog v3.0
 * 
 * Comprehensive print & design product catalog
 * Sources: Vistaprint, Printulu, ImpressWeb, MediaMafia
 * Markup: 75% on cost price
 * Design service: R350 flat / R350 per hour custom
 * Delivery: from R160
 * 
 * @package SwitchBusinessHub
 * @version 3.0.0
 */

if (!defined('ABSPATH')) exit;

class SBHA_Products {
    
    public static function get_all() {
        $defaults = array(

            // ================================================================
            // DESIGN SERVICES
            // ================================================================
            'design_service' => array(
                'name' => 'Graphic Design Service',
                'category' => 'design',
                'description' => 'Professional graphic design for any project. Our designers create eye-catching visuals tailored to your brand.',
                'source' => 'Switch Graphics Internal',
                'source_url' => 'https://switchgraphics.co.za',
                'is_design_service' => true,
                'image' => '',
                'variations' => array(
                    array('name' => 'Single Design (flyer, poster, social post)', 'cost' => 200, 'price' => 350, 'sku' => 'DES-SINGLE'),
                    array('name' => 'Double-Sided Design (business card, brochure)', 'cost' => 230, 'price' => 400, 'sku' => 'DES-DOUBLE'),
                    array('name' => 'Complex Design (infographic, menu, catalog page)', 'cost' => 350, 'price' => 600, 'sku' => 'DES-COMPLEX'),
                ),
            ),
            'custom_design_hourly' => array(
                'name' => 'Custom Design Work (Hourly)',
                'category' => 'design',
                'description' => 'Bespoke design work charged per hour. Perfect for unique projects that need extra creative attention.',
                'source' => 'Switch Graphics Internal',
                'source_url' => 'https://switchgraphics.co.za',
                'is_design_service' => true,
                'image' => '',
                'variations' => array(
                    array('name' => '1 Hour Design Work', 'cost' => 200, 'price' => 350, 'sku' => 'DES-HR-1'),
                    array('name' => '2 Hours Design Work', 'cost' => 400, 'price' => 700, 'sku' => 'DES-HR-2'),
                    array('name' => '4 Hours Design Work (Half Day)', 'cost' => 800, 'price' => 1400, 'sku' => 'DES-HR-4'),
                    array('name' => '8 Hours Design Work (Full Day)', 'cost' => 1500, 'price' => 2625, 'sku' => 'DES-HR-8'),
                ),
            ),
            'logo_design' => array(
                'name' => 'Logo Design',
                'category' => 'design',
                'description' => 'Professional logo design that represents your brand. Includes multiple concepts, revisions, and all source files.',
                'source' => 'Switch Graphics Internal',
                'source_url' => 'https://switchgraphics.co.za',
                'is_design_service' => true,
                'image' => '',
                'variations' => array(
                    array('name' => 'Basic Logo (2 concepts, 2 revisions)', 'cost' => 500, 'price' => 875, 'sku' => 'LOGO-BASIC'),
                    array('name' => 'Standard Logo (4 concepts, 4 revisions)', 'cost' => 800, 'price' => 1400, 'sku' => 'LOGO-STD'),
                    array('name' => 'Premium Logo (6 concepts, unlimited revisions)', 'cost' => 1200, 'price' => 2100, 'sku' => 'LOGO-PREM'),
                    array('name' => 'Logo + Business Card Design', 'cost' => 1000, 'price' => 1750, 'sku' => 'LOGO-BC'),
                ),
            ),
            'brand_identity' => array(
                'name' => 'Brand Identity Package',
                'category' => 'design',
                'description' => 'Complete brand identity: logo, colour palette, typography, business card, letterhead, and brand guidelines document.',
                'source' => 'Switch Graphics Internal',
                'source_url' => 'https://switchgraphics.co.za',
                'is_design_service' => true,
                'image' => '',
                'variations' => array(
                    array('name' => 'Starter Brand (Logo + Card + Letterhead)', 'cost' => 1500, 'price' => 2625, 'sku' => 'BRAND-START'),
                    array('name' => 'Professional Brand (Full stationery set)', 'cost' => 2500, 'price' => 4375, 'sku' => 'BRAND-PRO'),
                    array('name' => 'Corporate Brand (Full identity + guidelines)', 'cost' => 4000, 'price' => 7000, 'sku' => 'BRAND-CORP'),
                ),
            ),
            'social_media_design' => array(
                'name' => 'Social Media Design',
                'category' => 'design',
                'description' => 'Scroll-stopping social media graphics for Facebook, Instagram, LinkedIn, and more. Sized perfectly for each platform.',
                'source' => 'Switch Graphics Internal',
                'source_url' => 'https://switchgraphics.co.za',
                'is_design_service' => true,
                'image' => '',
                'variations' => array(
                    array('name' => 'Single Post Design', 'cost' => 80, 'price' => 140, 'sku' => 'SOC-1'),
                    array('name' => '5 Post Package', 'cost' => 350, 'price' => 613, 'sku' => 'SOC-5'),
                    array('name' => '10 Post Package', 'cost' => 600, 'price' => 1050, 'sku' => 'SOC-10'),
                    array('name' => 'Monthly Package (20 posts + stories)', 'cost' => 1000, 'price' => 1750, 'sku' => 'SOC-MONTH'),
                    array('name' => 'Facebook/LinkedIn Cover Design', 'cost' => 120, 'price' => 210, 'sku' => 'SOC-COVER'),
                ),
            ),

            // ================================================================
            // BUSINESS CARDS (Vistaprint sourced, 75% markup)
            // ================================================================
            'standard_business_cards' => array(
                'name' => 'Standard Business Cards',
                'category' => 'business_cards',
                'description' => 'Premium 350gsm full-colour business cards. Crisp, professional finish that makes a lasting first impression.',
                'source' => 'Vistaprint',
                'source_url' => 'https://www.vistaprint.co.za/business-cards',
                'image' => '',
                'variations' => array(
                    array('name' => '100 Cards - Single Sided', 'cost' => 100, 'price' => 175, 'sku' => 'BC-100-SS'),
                    array('name' => '100 Cards - Double Sided', 'cost' => 130, 'price' => 228, 'sku' => 'BC-100-DS'),
                    array('name' => '250 Cards - Single Sided', 'cost' => 140, 'price' => 245, 'sku' => 'BC-250-SS'),
                    array('name' => '250 Cards - Double Sided', 'cost' => 170, 'price' => 298, 'sku' => 'BC-250-DS'),
                    array('name' => '500 Cards - Single Sided', 'cost' => 190, 'price' => 333, 'sku' => 'BC-500-SS'),
                    array('name' => '500 Cards - Double Sided', 'cost' => 230, 'price' => 403, 'sku' => 'BC-500-DS'),
                    array('name' => '1000 Cards - Double Sided', 'cost' => 340, 'price' => 595, 'sku' => 'BC-1000-DS'),
                    array('name' => '2000 Cards - Double Sided', 'cost' => 520, 'price' => 910, 'sku' => 'BC-2000-DS'),
                ),
            ),
            'premium_business_cards' => array(
                'name' => 'Premium Business Cards',
                'category' => 'business_cards',
                'description' => '400gsm luxury laminated cards. Matt or gloss lamination for a sophisticated, high-end feel.',
                'source' => 'Vistaprint',
                'source_url' => 'https://www.vistaprint.co.za/business-cards',
                'image' => '',
                'variations' => array(
                    array('name' => '100 Premium Cards (Matt Lam)', 'cost' => 160, 'price' => 280, 'sku' => 'PBC-100-M'),
                    array('name' => '100 Premium Cards (Gloss Lam)', 'cost' => 160, 'price' => 280, 'sku' => 'PBC-100-G'),
                    array('name' => '250 Premium Cards (Matt Lam)', 'cost' => 220, 'price' => 385, 'sku' => 'PBC-250-M'),
                    array('name' => '500 Premium Cards (Matt Lam)', 'cost' => 320, 'price' => 560, 'sku' => 'PBC-500-M'),
                    array('name' => '1000 Premium Cards', 'cost' => 480, 'price' => 840, 'sku' => 'PBC-1000'),
                ),
            ),
            'spot_uv_cards' => array(
                'name' => 'Spot UV Business Cards',
                'category' => 'business_cards',
                'description' => 'Luxury spot UV finish with selective gloss coating on matt lamination. The ultimate premium card.',
                'source' => 'Printulu',
                'source_url' => 'https://www.printulu.co.za',
                'image' => '',
                'variations' => array(
                    array('name' => '100 Spot UV Cards', 'cost' => 250, 'price' => 438, 'sku' => 'SUV-100'),
                    array('name' => '250 Spot UV Cards', 'cost' => 340, 'price' => 595, 'sku' => 'SUV-250'),
                    array('name' => '500 Spot UV Cards', 'cost' => 480, 'price' => 840, 'sku' => 'SUV-500'),
                ),
            ),

            // ================================================================
            // FLYERS & LEAFLETS
            // ================================================================
            'a5_flyers' => array(
                'name' => 'A5 Flyers (148×210mm)',
                'category' => 'flyers',
                'description' => 'Versatile A5 flyers perfect for promotions, events, menus, and handouts. Full-colour on 130gsm gloss.',
                'source' => 'Vistaprint',
                'source_url' => 'https://www.vistaprint.co.za/flyers',
                'image' => '',
                'variations' => array(
                    array('name' => '100 Flyers - Single Sided', 'cost' => 80, 'price' => 140, 'sku' => 'A5F-100-SS'),
                    array('name' => '100 Flyers - Double Sided', 'cost' => 110, 'price' => 193, 'sku' => 'A5F-100-DS'),
                    array('name' => '250 Flyers - Single Sided', 'cost' => 120, 'price' => 210, 'sku' => 'A5F-250-SS'),
                    array('name' => '250 Flyers - Double Sided', 'cost' => 160, 'price' => 280, 'sku' => 'A5F-250-DS'),
                    array('name' => '500 Flyers - Single Sided', 'cost' => 180, 'price' => 315, 'sku' => 'A5F-500-SS'),
                    array('name' => '500 Flyers - Double Sided', 'cost' => 240, 'price' => 420, 'sku' => 'A5F-500-DS'),
                    array('name' => '1000 Flyers - Single Sided', 'cost' => 280, 'price' => 490, 'sku' => 'A5F-1000-SS'),
                    array('name' => '1000 Flyers - Double Sided', 'cost' => 360, 'price' => 630, 'sku' => 'A5F-1000-DS'),
                    array('name' => '2500 Flyers - Double Sided', 'cost' => 580, 'price' => 1015, 'sku' => 'A5F-2500-DS'),
                    array('name' => '5000 Flyers - Double Sided', 'cost' => 900, 'price' => 1575, 'sku' => 'A5F-5000-DS'),
                ),
            ),
            'a4_flyers' => array(
                'name' => 'A4 Flyers (210×297mm)',
                'category' => 'flyers',
                'description' => 'Large format A4 flyers ideal for menus, pricelists, info sheets, and event programmes. Full-colour 130gsm.',
                'source' => 'Vistaprint',
                'source_url' => 'https://www.vistaprint.co.za/flyers',
                'image' => '',
                'variations' => array(
                    array('name' => '100 Flyers - Single Sided', 'cost' => 110, 'price' => 193, 'sku' => 'A4F-100-SS'),
                    array('name' => '100 Flyers - Double Sided', 'cost' => 150, 'price' => 263, 'sku' => 'A4F-100-DS'),
                    array('name' => '250 Flyers - Single Sided', 'cost' => 180, 'price' => 315, 'sku' => 'A4F-250-SS'),
                    array('name' => '250 Flyers - Double Sided', 'cost' => 240, 'price' => 420, 'sku' => 'A4F-250-DS'),
                    array('name' => '500 Flyers - Single Sided', 'cost' => 280, 'price' => 490, 'sku' => 'A4F-500-SS'),
                    array('name' => '500 Flyers - Double Sided', 'cost' => 360, 'price' => 630, 'sku' => 'A4F-500-DS'),
                    array('name' => '1000 Flyers - Double Sided', 'cost' => 520, 'price' => 910, 'sku' => 'A4F-1000-DS'),
                ),
            ),
            'dl_flyers' => array(
                'name' => 'DL Flyers / Rack Cards (99×210mm)',
                'category' => 'flyers',
                'description' => 'Slim DL flyers perfect for rack displays, letterbox drops, and info cards. Fits standard envelopes.',
                'source' => 'Vistaprint',
                'source_url' => 'https://www.vistaprint.co.za/flyers',
                'image' => '',
                'variations' => array(
                    array('name' => '100 DL Flyers - Single Sided', 'cost' => 70, 'price' => 123, 'sku' => 'DLF-100-SS'),
                    array('name' => '250 DL Flyers - Single Sided', 'cost' => 110, 'price' => 193, 'sku' => 'DLF-250-SS'),
                    array('name' => '500 DL Flyers - Single Sided', 'cost' => 170, 'price' => 298, 'sku' => 'DLF-500-SS'),
                    array('name' => '1000 DL Flyers - Single Sided', 'cost' => 250, 'price' => 438, 'sku' => 'DLF-1000-SS'),
                ),
            ),
            'a6_flyers' => array(
                'name' => 'A6 Flyers (105×148mm)',
                'category' => 'flyers',
                'description' => 'Compact A6 flyers — great for vouchers, invitations, and mini handouts.',
                'source' => 'Vistaprint',
                'source_url' => 'https://www.vistaprint.co.za/flyers',
                'image' => '',
                'variations' => array(
                    array('name' => '250 A6 Flyers - Single Sided', 'cost' => 90, 'price' => 158, 'sku' => 'A6F-250-SS'),
                    array('name' => '500 A6 Flyers - Single Sided', 'cost' => 130, 'price' => 228, 'sku' => 'A6F-500-SS'),
                    array('name' => '1000 A6 Flyers - Single Sided', 'cost' => 200, 'price' => 350, 'sku' => 'A6F-1000-SS'),
                    array('name' => '2500 A6 Flyers - Double Sided', 'cost' => 400, 'price' => 700, 'sku' => 'A6F-2500-DS'),
                ),
            ),

            // ================================================================
            // BROCHURES & BOOKLETS
            // ================================================================
            'folded_brochures' => array(
                'name' => 'Folded Brochures',
                'category' => 'brochures',
                'description' => 'Professional folded brochures for menus, pricelists, and company profiles. Bi-fold or tri-fold options.',
                'source' => 'Vistaprint',
                'source_url' => 'https://www.vistaprint.co.za/brochures',
                'image' => '',
                'variations' => array(
                    array('name' => '100 A4 Bi-Fold Brochures', 'cost' => 200, 'price' => 350, 'sku' => 'BRO-100-BI'),
                    array('name' => '250 A4 Bi-Fold Brochures', 'cost' => 320, 'price' => 560, 'sku' => 'BRO-250-BI'),
                    array('name' => '500 A4 Bi-Fold Brochures', 'cost' => 480, 'price' => 840, 'sku' => 'BRO-500-BI'),
                    array('name' => '100 A4 Tri-Fold Brochures', 'cost' => 240, 'price' => 420, 'sku' => 'BRO-100-TRI'),
                    array('name' => '250 A4 Tri-Fold Brochures', 'cost' => 380, 'price' => 665, 'sku' => 'BRO-250-TRI'),
                    array('name' => '500 A4 Tri-Fold Brochures', 'cost' => 560, 'price' => 980, 'sku' => 'BRO-500-TRI'),
                ),
            ),
            'booklets' => array(
                'name' => 'Booklets & Catalogues',
                'category' => 'brochures',
                'description' => 'Saddle-stitched booklets for company profiles, product catalogues, event programmes, and training manuals.',
                'source' => 'Printulu',
                'source_url' => 'https://www.printulu.co.za/booklets',
                'image' => '',
                'variations' => array(
                    array('name' => '50x A5 Booklet (8 pages)', 'cost' => 600, 'price' => 1050, 'sku' => 'BKL-50-8'),
                    array('name' => '50x A5 Booklet (16 pages)', 'cost' => 900, 'price' => 1575, 'sku' => 'BKL-50-16'),
                    array('name' => '100x A5 Booklet (8 pages)', 'cost' => 900, 'price' => 1575, 'sku' => 'BKL-100-8'),
                    array('name' => '100x A5 Booklet (16 pages)', 'cost' => 1400, 'price' => 2450, 'sku' => 'BKL-100-16'),
                ),
            ),

            // ================================================================
            // POSTERS
            // ================================================================
            'paper_posters' => array(
                'name' => 'Paper Posters',
                'category' => 'posters',
                'description' => 'Full-colour gloss or satin posters. Ideal for indoor displays, promotions, and wall art.',
                'source' => 'Vistaprint',
                'source_url' => 'https://www.vistaprint.co.za/posters',
                'image' => '',
                'variations' => array(
                    array('name' => 'A4 Poster (210×297mm)', 'cost' => 8, 'price' => 14, 'sku' => 'PP-A4'),
                    array('name' => 'A3 Poster (297×420mm)', 'cost' => 14, 'price' => 25, 'sku' => 'PP-A3'),
                    array('name' => 'A2 Poster (420×594mm)', 'cost' => 25, 'price' => 44, 'sku' => 'PP-A2'),
                    array('name' => 'A1 Poster (594×841mm)', 'cost' => 40, 'price' => 70, 'sku' => 'PP-A1'),
                    array('name' => 'A0 Poster (841×1189mm)', 'cost' => 70, 'price' => 123, 'sku' => 'PP-A0'),
                ),
            ),
            'foam_board_posters' => array(
                'name' => 'Foam Board Posters',
                'category' => 'posters',
                'description' => 'Rigid 5mm foam board prints. Lightweight, self-supporting — perfect for displays, exhibitions, and presentations.',
                'source' => 'ImpressWeb',
                'source_url' => 'https://impressweb.co.za',
                'image' => '',
                'variations' => array(
                    array('name' => 'A3 Foam Board', 'cost' => 50, 'price' => 88, 'sku' => 'FB-A3'),
                    array('name' => 'A2 Foam Board', 'cost' => 80, 'price' => 140, 'sku' => 'FB-A2'),
                    array('name' => 'A1 Foam Board', 'cost' => 130, 'price' => 228, 'sku' => 'FB-A1'),
                    array('name' => 'A0 Foam Board', 'cost' => 200, 'price' => 350, 'sku' => 'FB-A0'),
                ),
            ),

            // ================================================================
            // BANNERS
            // ================================================================
            'pvc_banners' => array(
                'name' => 'PVC Banners',
                'category' => 'banners',
                'description' => 'Durable 440gsm outdoor PVC banners with eyelets. Weatherproof and UV-resistant — built to last.',
                'source' => 'Media Mafia',
                'source_url' => 'https://themediamafia.co.za',
                'image' => '',
                'variations' => array(
                    array('name' => '1m × 0.5m Banner', 'cost' => 80, 'price' => 140, 'sku' => 'PVC-1x05'),
                    array('name' => '1m × 1m Banner', 'cost' => 120, 'price' => 210, 'sku' => 'PVC-1x1'),
                    array('name' => '2m × 1m Banner', 'cost' => 200, 'price' => 350, 'sku' => 'PVC-2x1'),
                    array('name' => '3m × 1m Banner', 'cost' => 280, 'price' => 490, 'sku' => 'PVC-3x1'),
                    array('name' => '4m × 1m Banner', 'cost' => 360, 'price' => 630, 'sku' => 'PVC-4x1'),
                    array('name' => '3m × 2m Banner', 'cost' => 500, 'price' => 875, 'sku' => 'PVC-3x2'),
                    array('name' => '5m × 1.5m Banner', 'cost' => 600, 'price' => 1050, 'sku' => 'PVC-5x15'),
                    array('name' => 'Custom Size (per m²)', 'cost' => 100, 'price' => 175, 'sku' => 'PVC-CUSTOM'),
                ),
            ),
            'mesh_banners' => array(
                'name' => 'Mesh Banners',
                'category' => 'banners',
                'description' => 'Perforated mesh banners for windy outdoor locations. Allows air to pass through while maintaining visibility.',
                'source' => 'Media Mafia',
                'source_url' => 'https://themediamafia.co.za',
                'image' => '',
                'variations' => array(
                    array('name' => '2m × 1m Mesh Banner', 'cost' => 240, 'price' => 420, 'sku' => 'MESH-2x1'),
                    array('name' => '3m × 1m Mesh Banner', 'cost' => 340, 'price' => 595, 'sku' => 'MESH-3x1'),
                    array('name' => '3m × 2m Mesh Banner', 'cost' => 600, 'price' => 1050, 'sku' => 'MESH-3x2'),
                ),
            ),
            'pullup_banners' => array(
                'name' => 'Pull-Up / Roll-Up Banners',
                'category' => 'banners',
                'description' => 'Portable retractable banners with aluminium stand and carry case. Set up in seconds — perfect for expos and events.',
                'source' => 'Vistaprint',
                'source_url' => 'https://www.vistaprint.co.za/banners',
                'image' => '',
                'variations' => array(
                    array('name' => '850×2000mm Economy Pull-Up', 'cost' => 280, 'price' => 490, 'sku' => 'PUB-850-ECO'),
                    array('name' => '850×2000mm Standard Pull-Up', 'cost' => 380, 'price' => 665, 'sku' => 'PUB-850-STD'),
                    array('name' => '850×2000mm Premium Pull-Up', 'cost' => 500, 'price' => 875, 'sku' => 'PUB-850-PRE'),
                    array('name' => '1000×2000mm Wide Pull-Up', 'cost' => 450, 'price' => 788, 'sku' => 'PUB-1000'),
                    array('name' => '1200×2000mm Extra Wide', 'cost' => 550, 'price' => 963, 'sku' => 'PUB-1200'),
                    array('name' => 'Replacement Print Only (850mm)', 'cost' => 150, 'price' => 263, 'sku' => 'PUB-REPRINT'),
                ),
            ),
            'x_banners' => array(
                'name' => 'X-Banner Stands',
                'category' => 'banners',
                'description' => 'Budget-friendly X-frame banner stands. Lightweight and easy to transport.',
                'source' => 'Media Mafia',
                'source_url' => 'https://themediamafia.co.za',
                'image' => '',
                'variations' => array(
                    array('name' => '600×1600mm X-Banner', 'cost' => 180, 'price' => 315, 'sku' => 'XB-600'),
                    array('name' => '800×1800mm X-Banner', 'cost' => 220, 'price' => 385, 'sku' => 'XB-800'),
                ),
            ),

            // ================================================================
            // SIGNAGE
            // ================================================================
            'correx_boards' => array(
                'name' => 'Correx / Corflute Boards',
                'category' => 'signage',
                'description' => 'Lightweight corrugated plastic boards. Ideal for estate agent boards, directional signs, and temporary outdoor signage.',
                'source' => 'ImpressWeb',
                'source_url' => 'https://impressweb.co.za',
                'image' => '',
                'variations' => array(
                    array('name' => 'A3 Correx Board', 'cost' => 40, 'price' => 70, 'sku' => 'CX-A3'),
                    array('name' => 'A2 Correx Board', 'cost' => 60, 'price' => 105, 'sku' => 'CX-A2'),
                    array('name' => 'A1 Correx Board', 'cost' => 90, 'price' => 158, 'sku' => 'CX-A1'),
                    array('name' => '600×900mm Correx', 'cost' => 100, 'price' => 175, 'sku' => 'CX-6090'),
                    array('name' => '900×1200mm Correx', 'cost' => 150, 'price' => 263, 'sku' => 'CX-90120'),
                    array('name' => '1200×1800mm Correx', 'cost' => 250, 'price' => 438, 'sku' => 'CX-120180'),
                ),
            ),
            'perspex_boards' => array(
                'name' => 'Perspex / Acrylic Boards',
                'category' => 'signage',
                'description' => 'Premium clear or white acrylic signs. Sleek, modern look for offices, weddings, and high-end displays.',
                'source' => 'ImpressWeb',
                'source_url' => 'https://impressweb.co.za',
                'image' => '',
                'variations' => array(
                    array('name' => 'A4 Perspex Sign (3mm)', 'cost' => 120, 'price' => 210, 'sku' => 'PX-A4'),
                    array('name' => 'A3 Perspex Sign (3mm)', 'cost' => 200, 'price' => 350, 'sku' => 'PX-A3'),
                    array('name' => 'A2 Perspex Sign (3mm)', 'cost' => 350, 'price' => 613, 'sku' => 'PX-A2'),
                    array('name' => 'A1 Perspex Sign (5mm)', 'cost' => 550, 'price' => 963, 'sku' => 'PX-A1'),
                    array('name' => 'Custom Size Perspex (per m²)', 'cost' => 800, 'price' => 1400, 'sku' => 'PX-CUSTOM'),
                ),
            ),
            'chromadek_signs' => array(
                'name' => 'Chromadek Signs',
                'category' => 'signage',
                'description' => 'Durable metal signs for permanent outdoor branding. Rust-resistant chromadek steel with UV-stable printing.',
                'source' => 'Media Mafia',
                'source_url' => 'https://themediamafia.co.za',
                'image' => '',
                'variations' => array(
                    array('name' => '600×400mm Chromadek', 'cost' => 180, 'price' => 315, 'sku' => 'CHR-6040'),
                    array('name' => '900×600mm Chromadek', 'cost' => 300, 'price' => 525, 'sku' => 'CHR-9060'),
                    array('name' => '1200×600mm Chromadek', 'cost' => 400, 'price' => 700, 'sku' => 'CHR-12060'),
                    array('name' => '1200×900mm Chromadek', 'cost' => 500, 'price' => 875, 'sku' => 'CHR-12090'),
                    array('name' => '2400×1200mm Chromadek', 'cost' => 900, 'price' => 1575, 'sku' => 'CHR-24120'),
                ),
            ),
            'forex_pvc_board' => array(
                'name' => 'Forex / PVC Board Signs',
                'category' => 'signage',
                'description' => 'Rigid 3mm or 5mm PVC board. Smooth surface for indoor signage, displays, and point-of-sale materials.',
                'source' => 'Media Mafia',
                'source_url' => 'https://themediamafia.co.za',
                'image' => '',
                'variations' => array(
                    array('name' => 'A3 Forex Board (3mm)', 'cost' => 60, 'price' => 105, 'sku' => 'FX-A3'),
                    array('name' => 'A2 Forex Board (3mm)', 'cost' => 100, 'price' => 175, 'sku' => 'FX-A2'),
                    array('name' => 'A1 Forex Board (5mm)', 'cost' => 160, 'price' => 280, 'sku' => 'FX-A1'),
                    array('name' => 'A0 Forex Board (5mm)', 'cost' => 280, 'price' => 490, 'sku' => 'FX-A0'),
                ),
            ),
            'aframe_signs' => array(
                'name' => 'A-Frame / Pavement Signs',
                'category' => 'signage',
                'description' => 'Double-sided A-frame pavement signs. Heavy-duty steel frame with replaceable panels. Perfect for shop fronts.',
                'source' => 'ImpressWeb',
                'source_url' => 'https://impressweb.co.za',
                'image' => '',
                'variations' => array(
                    array('name' => 'A1 Steel A-Frame (with panels)', 'cost' => 450, 'price' => 788, 'sku' => 'AF-A1'),
                    array('name' => 'A0 Steel A-Frame (with panels)', 'cost' => 600, 'price' => 1050, 'sku' => 'AF-A0'),
                    array('name' => 'Replacement Panels Only (A1)', 'cost' => 120, 'price' => 210, 'sku' => 'AF-PANEL'),
                ),
            ),

            // ================================================================
            // WEDDING & EVENTS
            // ================================================================
            'welcome_boards' => array(
                'name' => 'Welcome Boards',
                'category' => 'wedding',
                'description' => 'Beautiful welcome boards for weddings, corporate events, and parties. Available in Correx, Perspex, Foam Board, or Wood.',
                'source' => 'Switch Graphics Internal',
                'source_url' => 'https://switchgraphics.co.za',
                'image' => '',
                'variations' => array(
                    array('name' => 'A1 Correx Welcome Board', 'cost' => 100, 'price' => 175, 'sku' => 'WB-A1-CX'),
                    array('name' => 'A1 Foam Board Welcome Board', 'cost' => 140, 'price' => 245, 'sku' => 'WB-A1-FB'),
                    array('name' => 'A1 Perspex Welcome Board', 'cost' => 550, 'price' => 963, 'sku' => 'WB-A1-PX'),
                    array('name' => '600×900mm Correx Welcome Board', 'cost' => 120, 'price' => 210, 'sku' => 'WB-69-CX'),
                    array('name' => '600×900mm Foam Board Welcome Board', 'cost' => 160, 'price' => 280, 'sku' => 'WB-69-FB'),
                    array('name' => '600×900mm Perspex Welcome Board', 'cost' => 650, 'price' => 1138, 'sku' => 'WB-69-PX'),
                    array('name' => 'A0 Correx Welcome Board', 'cost' => 180, 'price' => 315, 'sku' => 'WB-A0-CX'),
                    array('name' => 'A0 Foam Board Welcome Board', 'cost' => 220, 'price' => 385, 'sku' => 'WB-A0-FB'),
                ),
            ),
            'seating_charts' => array(
                'name' => 'Seating Charts / Table Plans',
                'category' => 'wedding',
                'description' => 'Elegant seating chart boards for wedding receptions. Help guests find their seats in style.',
                'source' => 'Switch Graphics Internal',
                'source_url' => 'https://switchgraphics.co.za',
                'image' => '',
                'variations' => array(
                    array('name' => 'A1 Foam Board Seating Chart', 'cost' => 160, 'price' => 280, 'sku' => 'SC-A1-FB'),
                    array('name' => 'A1 Perspex Seating Chart', 'cost' => 600, 'price' => 1050, 'sku' => 'SC-A1-PX'),
                    array('name' => 'A0 Foam Board Seating Chart', 'cost' => 250, 'price' => 438, 'sku' => 'SC-A0-FB'),
                ),
            ),
            'table_numbers' => array(
                'name' => 'Table Numbers',
                'category' => 'wedding',
                'description' => 'Stylish table numbers for weddings and events. Card, acrylic, or wooden options available.',
                'source' => 'Switch Graphics Internal',
                'source_url' => 'https://switchgraphics.co.za',
                'image' => '',
                'variations' => array(
                    array('name' => 'Card Table Numbers (per set of 10)', 'cost' => 50, 'price' => 88, 'sku' => 'TN-CARD-10'),
                    array('name' => 'Card Table Numbers (per set of 20)', 'cost' => 80, 'price' => 140, 'sku' => 'TN-CARD-20'),
                    array('name' => 'Acrylic Table Numbers (per set of 10)', 'cost' => 250, 'price' => 438, 'sku' => 'TN-ACRYL-10'),
                    array('name' => 'Acrylic Table Numbers (per set of 20)', 'cost' => 450, 'price' => 788, 'sku' => 'TN-ACRYL-20'),
                ),
            ),
            'wedding_invitations' => array(
                'name' => 'Wedding & Event Invitations',
                'category' => 'wedding',
                'description' => 'Beautifully designed invitations for weddings, birthdays, and corporate events. Various paper finishes available.',
                'source' => 'Printulu',
                'source_url' => 'https://www.printulu.co.za',
                'image' => '',
                'variations' => array(
                    array('name' => '50 Flat Invitations', 'cost' => 200, 'price' => 350, 'sku' => 'INV-50-FLAT'),
                    array('name' => '100 Flat Invitations', 'cost' => 300, 'price' => 525, 'sku' => 'INV-100-FLAT'),
                    array('name' => '50 Folded Invitations', 'cost' => 350, 'price' => 613, 'sku' => 'INV-50-FOLD'),
                    array('name' => '100 Folded Invitations', 'cost' => 500, 'price' => 875, 'sku' => 'INV-100-FOLD'),
                    array('name' => 'Digital Invitation (WhatsApp/Email)', 'cost' => 200, 'price' => 350, 'sku' => 'INV-DIGITAL'),
                ),
            ),

            // ================================================================
            // STICKERS & LABELS
            // ================================================================
            'custom_stickers' => array(
                'name' => 'Custom Stickers',
                'category' => 'stickers',
                'description' => 'Custom-cut vinyl or paper stickers. Perfect for product labels, branding, promotions, and packaging.',
                'source' => 'Printulu',
                'source_url' => 'https://www.printulu.co.za/stickers',
                'image' => '',
                'variations' => array(
                    array('name' => '50 Stickers (50×50mm) - Paper', 'cost' => 60, 'price' => 105, 'sku' => 'STK-50-P'),
                    array('name' => '100 Stickers (50×50mm) - Paper', 'cost' => 90, 'price' => 158, 'sku' => 'STK-100-P'),
                    array('name' => '250 Stickers (50×50mm) - Paper', 'cost' => 150, 'price' => 263, 'sku' => 'STK-250-P'),
                    array('name' => '500 Stickers (50×50mm) - Paper', 'cost' => 240, 'price' => 420, 'sku' => 'STK-500-P'),
                    array('name' => '100 Stickers (50×50mm) - Vinyl', 'cost' => 140, 'price' => 245, 'sku' => 'STK-100-V'),
                    array('name' => '250 Stickers (50×50mm) - Vinyl', 'cost' => 250, 'price' => 438, 'sku' => 'STK-250-V'),
                    array('name' => '500 Stickers (50×50mm) - Vinyl', 'cost' => 400, 'price' => 700, 'sku' => 'STK-500-V'),
                    array('name' => '100 Stickers (100×100mm) - Vinyl', 'cost' => 220, 'price' => 385, 'sku' => 'STK-100-V-LG'),
                    array('name' => 'Custom Size (per sheet A3)', 'cost' => 80, 'price' => 140, 'sku' => 'STK-SHEET'),
                ),
            ),
            'product_labels' => array(
                'name' => 'Product Labels (Roll)',
                'category' => 'stickers',
                'description' => 'Professional roll labels for products, bottles, jars, and packaging. Available in various shapes and finishes.',
                'source' => 'Printulu',
                'source_url' => 'https://www.printulu.co.za/labels',
                'image' => '',
                'variations' => array(
                    array('name' => '250 Labels (Circle 50mm)', 'cost' => 200, 'price' => 350, 'sku' => 'LBL-250-C50'),
                    array('name' => '500 Labels (Circle 50mm)', 'cost' => 300, 'price' => 525, 'sku' => 'LBL-500-C50'),
                    array('name' => '1000 Labels (Circle 50mm)', 'cost' => 450, 'price' => 788, 'sku' => 'LBL-1000-C50'),
                    array('name' => '500 Labels (Rectangle 70×40mm)', 'cost' => 350, 'price' => 613, 'sku' => 'LBL-500-R'),
                    array('name' => '1000 Labels (Rectangle 70×40mm)', 'cost' => 500, 'price' => 875, 'sku' => 'LBL-1000-R'),
                ),
            ),

            // ================================================================
            // APPAREL & CLOTHING
            // ================================================================
            'tshirt_printing' => array(
                'name' => 'T-Shirt Printing',
                'category' => 'apparel',
                'description' => 'Custom printed t-shirts using DTF or vinyl heat transfer. Perfect for events, uniforms, and promotions.',
                'source' => 'Switch Graphics Internal',
                'source_url' => 'https://switchgraphics.co.za',
                'image' => '',
                'variations' => array(
                    array('name' => 'White T-Shirt + A4 Front Print', 'cost' => 55, 'price' => 96, 'sku' => 'TSH-W-A4'),
                    array('name' => 'White T-Shirt + A3 Front Print', 'cost' => 65, 'price' => 114, 'sku' => 'TSH-W-A3'),
                    array('name' => 'White T-Shirt + Front & Back Print', 'cost' => 90, 'price' => 158, 'sku' => 'TSH-W-FB'),
                    array('name' => 'Colour T-Shirt + A4 Front Print', 'cost' => 70, 'price' => 123, 'sku' => 'TSH-C-A4'),
                    array('name' => 'Colour T-Shirt + A3 Front Print', 'cost' => 80, 'price' => 140, 'sku' => 'TSH-C-A3'),
                    array('name' => 'Colour T-Shirt + Front & Back Print', 'cost' => 110, 'price' => 193, 'sku' => 'TSH-C-FB'),
                    array('name' => '10+ T-Shirts (White + A4 Print each)', 'cost' => 48, 'price' => 84, 'sku' => 'TSH-BULK-10'),
                    array('name' => '25+ T-Shirts (White + A4 Print each)', 'cost' => 42, 'price' => 74, 'sku' => 'TSH-BULK-25'),
                ),
            ),
            'golf_shirts' => array(
                'name' => 'Golf Shirts',
                'category' => 'apparel',
                'description' => 'Quality pique golf shirts with embroidery or DTF print. Professional look for corporate and events.',
                'source' => 'Switch Graphics Internal',
                'source_url' => 'https://switchgraphics.co.za',
                'image' => '',
                'variations' => array(
                    array('name' => 'Golf Shirt + Small Logo Embroidery', 'cost' => 90, 'price' => 158, 'sku' => 'GS-EMB-S'),
                    array('name' => 'Golf Shirt + Large Logo Print', 'cost' => 100, 'price' => 175, 'sku' => 'GS-PRT-L'),
                    array('name' => '10+ Golf Shirts (with embroidery each)', 'cost' => 80, 'price' => 140, 'sku' => 'GS-BULK-10'),
                    array('name' => '25+ Golf Shirts (with embroidery each)', 'cost' => 70, 'price' => 123, 'sku' => 'GS-BULK-25'),
                ),
            ),
            'hoodies' => array(
                'name' => 'Hoodies',
                'category' => 'apparel',
                'description' => 'Warm fleece hoodies with custom prints. Front logo, back design, or full print available.',
                'source' => 'Switch Graphics Internal',
                'source_url' => 'https://switchgraphics.co.za',
                'image' => '',
                'variations' => array(
                    array('name' => 'Hoodie + Front Logo Print', 'cost' => 160, 'price' => 280, 'sku' => 'HOOD-FRONT'),
                    array('name' => 'Hoodie + Front & Back Print', 'cost' => 200, 'price' => 350, 'sku' => 'HOOD-FB'),
                    array('name' => '10+ Hoodies (Front Logo each)', 'cost' => 140, 'price' => 245, 'sku' => 'HOOD-BULK'),
                ),
            ),
            'caps' => array(
                'name' => 'Branded Caps',
                'category' => 'apparel',
                'description' => 'Custom caps with embroidered or printed logos. Great for outdoor events and corporate wear.',
                'source' => 'Switch Graphics Internal',
                'source_url' => 'https://switchgraphics.co.za',
                'image' => '',
                'variations' => array(
                    array('name' => 'Cap + Front Embroidery', 'cost' => 50, 'price' => 88, 'sku' => 'CAP-EMB'),
                    array('name' => 'Cap + Full Front Print', 'cost' => 45, 'price' => 79, 'sku' => 'CAP-PRT'),
                    array('name' => '10+ Caps (Embroidery each)', 'cost' => 42, 'price' => 74, 'sku' => 'CAP-BULK-10'),
                    array('name' => '25+ Caps (Embroidery each)', 'cost' => 38, 'price' => 67, 'sku' => 'CAP-BULK-25'),
                ),
            ),

            // ================================================================
            // VEHICLE BRANDING
            // ================================================================
            'vehicle_magnets' => array(
                'name' => 'Vehicle Magnets',
                'category' => 'vehicle',
                'description' => 'Removable magnetic vehicle signs. Apply and remove easily — ideal for part-time business use.',
                'source' => 'ImpressWeb',
                'source_url' => 'https://impressweb.co.za',
                'image' => '',
                'variations' => array(
                    array('name' => '300×600mm Magnets (pair)', 'cost' => 200, 'price' => 350, 'sku' => 'VM-3060'),
                    array('name' => '400×600mm Magnets (pair)', 'cost' => 280, 'price' => 490, 'sku' => 'VM-4060'),
                    array('name' => '600×900mm Magnets (pair)', 'cost' => 400, 'price' => 700, 'sku' => 'VM-6090'),
                ),
            ),
            'vehicle_decals' => array(
                'name' => 'Vehicle Vinyl Decals',
                'category' => 'vehicle',
                'description' => 'Professional vinyl decals for doors, windows, and panels. Long-lasting outdoor vinyl with UV protection.',
                'source' => 'Media Mafia',
                'source_url' => 'https://themediamafia.co.za',
                'image' => '',
                'variations' => array(
                    array('name' => 'Door Decals (pair) - Logo & Contact', 'cost' => 400, 'price' => 700, 'sku' => 'VD-DOOR'),
                    array('name' => 'Rear Window Vinyl', 'cost' => 300, 'price' => 525, 'sku' => 'VD-REAR'),
                    array('name' => 'Side Panels (pair)', 'cost' => 600, 'price' => 1050, 'sku' => 'VD-SIDES'),
                    array('name' => 'Half Wrap (doors + rear)', 'cost' => 1800, 'price' => 3150, 'sku' => 'VD-HALF'),
                    array('name' => 'Full Vehicle Wrap', 'cost' => 4000, 'price' => 7000, 'sku' => 'VD-FULL'),
                ),
            ),
            'window_vinyl' => array(
                'name' => 'Window Vinyl / Frosting',
                'category' => 'vehicle',
                'description' => 'Window graphics, frosting, and one-way vision vinyl for shop fronts and vehicles.',
                'source' => 'Media Mafia',
                'source_url' => 'https://themediamafia.co.za',
                'image' => '',
                'variations' => array(
                    array('name' => 'Shop Window Vinyl (per m²)', 'cost' => 150, 'price' => 263, 'sku' => 'WV-SQM'),
                    array('name' => 'Frosted Vinyl (per m²)', 'cost' => 180, 'price' => 315, 'sku' => 'WV-FROST'),
                    array('name' => 'One-Way Vision (per m²)', 'cost' => 200, 'price' => 350, 'sku' => 'WV-OWV'),
                ),
            ),

            // ================================================================
            // CORPORATE GIFTING
            // ================================================================
            'branded_mugs' => array(
                'name' => 'Branded Mugs',
                'category' => 'corporate',
                'description' => '11oz ceramic mugs with vibrant full-wrap sublimation print. Great for gifts, promotions, and office branding.',
                'source' => 'Printulu',
                'source_url' => 'https://www.printulu.co.za/mugs',
                'image' => '',
                'variations' => array(
                    array('name' => '1 Branded Mug', 'cost' => 45, 'price' => 79, 'sku' => 'MUG-1'),
                    array('name' => '5 Branded Mugs', 'cost' => 200, 'price' => 350, 'sku' => 'MUG-5'),
                    array('name' => '10 Branded Mugs', 'cost' => 350, 'price' => 613, 'sku' => 'MUG-10'),
                    array('name' => '25 Branded Mugs', 'cost' => 750, 'price' => 1313, 'sku' => 'MUG-25'),
                    array('name' => '50 Branded Mugs', 'cost' => 1300, 'price' => 2275, 'sku' => 'MUG-50'),
                    array('name' => 'Magic Colour-Change Mug', 'cost' => 70, 'price' => 123, 'sku' => 'MUG-MAGIC'),
                ),
            ),
            'branded_pens' => array(
                'name' => 'Branded Pens',
                'category' => 'corporate',
                'description' => 'Custom printed promotional pens. Budget-friendly marketing that puts your brand in peoples hands.',
                'source' => 'Printulu',
                'source_url' => 'https://www.printulu.co.za',
                'image' => '',
                'variations' => array(
                    array('name' => '50 Branded Pens', 'cost' => 200, 'price' => 350, 'sku' => 'PEN-50'),
                    array('name' => '100 Branded Pens', 'cost' => 350, 'price' => 613, 'sku' => 'PEN-100'),
                    array('name' => '250 Branded Pens', 'cost' => 700, 'price' => 1225, 'sku' => 'PEN-250'),
                    array('name' => '500 Branded Pens', 'cost' => 1200, 'price' => 2100, 'sku' => 'PEN-500'),
                ),
            ),
            'calendars' => array(
                'name' => 'Custom Calendars',
                'category' => 'corporate',
                'description' => 'Branded wall or desk calendars. Year-round brand exposure with a practical gift your clients will use daily.',
                'source' => 'Vistaprint',
                'source_url' => 'https://www.vistaprint.co.za/calendars',
                'image' => '',
                'variations' => array(
                    array('name' => '10 Wall Calendars', 'cost' => 250, 'price' => 438, 'sku' => 'CAL-W-10'),
                    array('name' => '25 Wall Calendars', 'cost' => 500, 'price' => 875, 'sku' => 'CAL-W-25'),
                    array('name' => '50 Wall Calendars', 'cost' => 800, 'price' => 1400, 'sku' => 'CAL-W-50'),
                    array('name' => '10 Desk Calendars', 'cost' => 200, 'price' => 350, 'sku' => 'CAL-D-10'),
                    array('name' => '25 Desk Calendars', 'cost' => 400, 'price' => 700, 'sku' => 'CAL-D-25'),
                ),
            ),

            // ================================================================
            // LARGE FORMAT & CANVAS
            // ================================================================
            'canvas_prints' => array(
                'name' => 'Canvas Prints',
                'category' => 'canvas',
                'description' => 'Gallery-wrapped canvas prints ready to hang. Transform photos and artwork into stunning wall decor.',
                'source' => 'Switch Graphics Internal',
                'source_url' => 'https://switchgraphics.co.za',
                'image' => '',
                'variations' => array(
                    array('name' => '30×30cm Canvas', 'cost' => 100, 'price' => 175, 'sku' => 'CV-3030'),
                    array('name' => '40×30cm Canvas', 'cost' => 130, 'price' => 228, 'sku' => 'CV-4030'),
                    array('name' => '60×40cm Canvas', 'cost' => 200, 'price' => 350, 'sku' => 'CV-6040'),
                    array('name' => '80×60cm Canvas', 'cost' => 300, 'price' => 525, 'sku' => 'CV-8060'),
                    array('name' => '100×75cm Canvas', 'cost' => 400, 'price' => 700, 'sku' => 'CV-10075'),
                    array('name' => '120×80cm Canvas', 'cost' => 500, 'price' => 875, 'sku' => 'CV-12080'),
                ),
            ),
            'wall_decals' => array(
                'name' => 'Wall Decals & Murals',
                'category' => 'canvas',
                'description' => 'Custom vinyl wall graphics for offices, shops, and homes. Easy to apply and remove without damage.',
                'source' => 'Media Mafia',
                'source_url' => 'https://themediamafia.co.za',
                'image' => '',
                'variations' => array(
                    array('name' => 'Small Wall Decal (up to 0.5m²)', 'cost' => 120, 'price' => 210, 'sku' => 'WD-S'),
                    array('name' => 'Medium Wall Decal (up to 1m²)', 'cost' => 200, 'price' => 350, 'sku' => 'WD-M'),
                    array('name' => 'Large Wall Decal (up to 2m²)', 'cost' => 350, 'price' => 613, 'sku' => 'WD-L'),
                    array('name' => 'Full Wall Mural (per m²)', 'cost' => 200, 'price' => 350, 'sku' => 'WD-MURAL'),
                ),
            ),

            // ================================================================
            // NCR BOOKS & PADS
            // ================================================================
            'ncr_books' => array(
                'name' => 'NCR Invoice / Receipt Books',
                'category' => 'books',
                'description' => 'Carbonless duplicate and triplicate pads. Custom printed with your business details, numbering, and logo.',
                'source' => 'Vistaprint',
                'source_url' => 'https://www.vistaprint.co.za/ncr-forms',
                'image' => '',
                'variations' => array(
                    array('name' => 'A5 Duplicate (50 sets)', 'cost' => 220, 'price' => 385, 'sku' => 'NCR-A5-DUP-50'),
                    array('name' => 'A5 Triplicate (50 sets)', 'cost' => 280, 'price' => 490, 'sku' => 'NCR-A5-TRI-50'),
                    array('name' => 'A4 Duplicate (50 sets)', 'cost' => 300, 'price' => 525, 'sku' => 'NCR-A4-DUP-50'),
                    array('name' => 'A4 Triplicate (50 sets)', 'cost' => 380, 'price' => 665, 'sku' => 'NCR-A4-TRI-50'),
                    array('name' => 'A5 Duplicate (100 sets)', 'cost' => 350, 'price' => 613, 'sku' => 'NCR-A5-DUP-100'),
                    array('name' => 'A4 Duplicate (100 sets)', 'cost' => 480, 'price' => 840, 'sku' => 'NCR-A4-DUP-100'),
                ),
            ),
            'notepads' => array(
                'name' => 'Branded Notepads',
                'category' => 'books',
                'description' => 'Custom branded memo pads and notepads. Keep your brand top-of-mind on every desk.',
                'source' => 'Printulu',
                'source_url' => 'https://www.printulu.co.za',
                'image' => '',
                'variations' => array(
                    array('name' => 'A5 Notepad (50 sheets)', 'cost' => 40, 'price' => 70, 'sku' => 'PAD-A5-50'),
                    array('name' => 'A5 Notepad (100 sheets)', 'cost' => 60, 'price' => 105, 'sku' => 'PAD-A5-100'),
                    array('name' => 'A4 Notepad (50 sheets)', 'cost' => 60, 'price' => 105, 'sku' => 'PAD-A4-50'),
                    array('name' => '10x A5 Notepads (50 sheets each)', 'cost' => 300, 'price' => 525, 'sku' => 'PAD-A5-BULK'),
                ),
            ),

            // ================================================================
            // EVENTS & DISPLAYS
            // ================================================================
            'gazebos' => array(
                'name' => 'Branded Gazebos',
                'category' => 'events',
                'description' => '3×3m branded event gazebos with full-colour digital printing. Includes gazebo frame and branded canopy.',
                'source' => 'Media Mafia',
                'source_url' => 'https://themediamafia.co.za',
                'image' => '',
                'variations' => array(
                    array('name' => 'Branded Canopy Only (3×3m)', 'cost' => 1500, 'price' => 2625, 'sku' => 'GAZ-CAN'),
                    array('name' => 'Full Gazebo + Canopy (3×3m)', 'cost' => 2800, 'price' => 4900, 'sku' => 'GAZ-FULL'),
                    array('name' => 'Gazebo + Canopy + Back Wall', 'cost' => 3500, 'price' => 6125, 'sku' => 'GAZ-WALL'),
                    array('name' => 'Gazebo + Canopy + 3 Walls', 'cost' => 4500, 'price' => 7875, 'sku' => 'GAZ-3WALL'),
                ),
            ),
            'feather_flags' => array(
                'name' => 'Feather & Teardrop Flags',
                'category' => 'events',
                'description' => 'Eye-catching outdoor flags that flutter in the breeze. Complete with pole, base, and carry bag.',
                'source' => 'Media Mafia',
                'source_url' => 'https://themediamafia.co.za',
                'image' => '',
                'variations' => array(
                    array('name' => '2m Teardrop Flag', 'cost' => 380, 'price' => 665, 'sku' => 'FLAG-2T'),
                    array('name' => '3m Teardrop Flag', 'cost' => 480, 'price' => 840, 'sku' => 'FLAG-3T'),
                    array('name' => '4m Feather Flag', 'cost' => 580, 'price' => 1015, 'sku' => 'FLAG-4F'),
                    array('name' => '5m Feather Flag', 'cost' => 700, 'price' => 1225, 'sku' => 'FLAG-5F'),
                    array('name' => 'Replacement Print Only', 'cost' => 200, 'price' => 350, 'sku' => 'FLAG-REPRINT'),
                ),
            ),
            'tablecloths' => array(
                'name' => 'Branded Tablecloths',
                'category' => 'events',
                'description' => 'Custom printed tablecloths for events, exhibitions, and market stalls. Full-colour dye-sublimation print.',
                'source' => 'Media Mafia',
                'source_url' => 'https://themediamafia.co.za',
                'image' => '',
                'variations' => array(
                    array('name' => '6ft Fitted Tablecloth', 'cost' => 500, 'price' => 875, 'sku' => 'TBC-6FT'),
                    array('name' => '6ft Stretch Tablecloth', 'cost' => 600, 'price' => 1050, 'sku' => 'TBC-6STR'),
                    array('name' => '8ft Fitted Tablecloth', 'cost' => 600, 'price' => 1050, 'sku' => 'TBC-8FT'),
                ),
            ),

            // ================================================================
            // STAMPS
            // ================================================================
            'rubber_stamps' => array(
                'name' => 'Rubber Stamps',
                'category' => 'special',
                'description' => 'Self-inking custom rubber stamps with your logo, details, or custom text. Long-lasting ink cartridge included.',
                'source' => 'Vistaprint',
                'source_url' => 'https://www.vistaprint.co.za/stamps',
                'image' => '',
                'variations' => array(
                    array('name' => 'Small Stamp (38×14mm)', 'cost' => 80, 'price' => 140, 'sku' => 'STP-S'),
                    array('name' => 'Medium Stamp (58×22mm)', 'cost' => 110, 'price' => 193, 'sku' => 'STP-M'),
                    array('name' => 'Large Stamp (70×30mm)', 'cost' => 150, 'price' => 263, 'sku' => 'STP-L'),
                    array('name' => 'Round Stamp (40mm)', 'cost' => 130, 'price' => 228, 'sku' => 'STP-R40'),
                ),
            ),

            // ================================================================
            // WEBSITES
            // ================================================================
            'website_starter' => array(
                'name' => 'Starter Website Package',
                'category' => 'websites',
                'description' => 'Professional 5-page responsive website to get your business online. Includes hosting, SSL, and basic SEO.',
                'source' => 'Switch Graphics Internal',
                'source_url' => 'https://switchgraphics.co.za',
                'is_design_service' => true,
                'image' => '',
                'variations' => array(
                    array('name' => 'Starter Website (5 pages)', 'cost' => 2500, 'price' => 4375, 'sku' => 'WEB-START'),
                    array('name' => 'Starter + Logo Design', 'cost' => 3300, 'price' => 5775, 'sku' => 'WEB-START-LOGO'),
                ),
            ),
            'website_business' => array(
                'name' => 'Business Website Package',
                'category' => 'websites',
                'description' => 'Full business website with blog, gallery, and contact forms. Perfect for established businesses going digital.',
                'source' => 'Switch Graphics Internal',
                'source_url' => 'https://switchgraphics.co.za',
                'is_design_service' => true,
                'image' => '',
                'variations' => array(
                    array('name' => 'Business Website (10 pages)', 'cost' => 5000, 'price' => 8750, 'sku' => 'WEB-BIZ'),
                    array('name' => 'Business + E-commerce (50 products)', 'cost' => 8000, 'price' => 14000, 'sku' => 'WEB-BIZ-SHOP'),
                ),
            ),
            'website_ecommerce' => array(
                'name' => 'E-Commerce Website',
                'category' => 'websites',
                'description' => 'Full online store with payment gateway, inventory management, and customer accounts.',
                'source' => 'Switch Graphics Internal',
                'source_url' => 'https://switchgraphics.co.za',
                'is_design_service' => true,
                'image' => '',
                'variations' => array(
                    array('name' => 'E-Commerce (100 products)', 'cost' => 10000, 'price' => 17500, 'sku' => 'WEB-ECOM'),
                    array('name' => 'E-Commerce Pro (unlimited)', 'cost' => 15000, 'price' => 26250, 'sku' => 'WEB-ECOM-PRO'),
                ),
            ),
            'website_landing' => array(
                'name' => 'Landing Page',
                'category' => 'websites',
                'description' => 'High-converting single-page website for campaigns, product launches, and lead generation.',
                'source' => 'Switch Graphics Internal',
                'source_url' => 'https://switchgraphics.co.za',
                'is_design_service' => true,
                'image' => '',
                'variations' => array(
                    array('name' => 'Basic Landing Page', 'cost' => 1500, 'price' => 2625, 'sku' => 'WEB-LAND'),
                    array('name' => 'Premium Landing Page', 'cost' => 2500, 'price' => 4375, 'sku' => 'WEB-LAND-PRO'),
                ),
            ),
            'website_maintenance' => array(
                'name' => 'Website Maintenance',
                'category' => 'websites',
                'description' => 'Keep your website updated, secure, and running smoothly with regular maintenance and backups.',
                'source' => 'Switch Graphics Internal',
                'source_url' => 'https://switchgraphics.co.za',
                'is_design_service' => true,
                'image' => '',
                'variations' => array(
                    array('name' => 'Monthly Maintenance', 'cost' => 500, 'price' => 875, 'sku' => 'WEB-MAINT-M'),
                    array('name' => 'Annual Maintenance', 'cost' => 5000, 'price' => 8750, 'sku' => 'WEB-MAINT-Y'),
                ),
            ),

            // ================================================================
            // DELIVERY SERVICE
            // ================================================================
            'delivery' => array(
                'name' => 'Delivery Service',
                'category' => 'services',
                'description' => 'Safe and reliable delivery to your door. Packaging included for all orders.',
                'source' => 'Switch Graphics Internal',
                'source_url' => 'https://switchgraphics.co.za',
                'is_service' => true,
                'image' => '',
                'variations' => array(
                    array('name' => 'Newcastle Local Delivery', 'cost' => 50, 'price' => 160, 'sku' => 'DEL-LOC'),
                    array('name' => 'KZN Regional Delivery', 'cost' => 100, 'price' => 250, 'sku' => 'DEL-KZN'),
                    array('name' => 'National Delivery (Courier)', 'cost' => 150, 'price' => 350, 'sku' => 'DEL-NAT'),
                    array('name' => 'Express National (Next Day)', 'cost' => 250, 'price' => 500, 'sku' => 'DEL-EXP'),
                ),
            ),
        );
        
        $custom = get_option('sbha_custom_products', array());
        if (!empty($custom)) {
            foreach ($custom as $key => $product) {
                $defaults[$key] = $product;
            }
        }
        
        return $defaults;
    }
    
    public static function get_categories() {
        return array(
            'design' => array('name' => 'Design Services', 'emoji' => '🎨'),
            'business_cards' => array('name' => 'Business Cards', 'emoji' => '💳'),
            'flyers' => array('name' => 'Flyers & Leaflets', 'emoji' => '📄'),
            'brochures' => array('name' => 'Brochures & Booklets', 'emoji' => '📖'),
            'posters' => array('name' => 'Posters', 'emoji' => '🖼️'),
            'banners' => array('name' => 'Banners', 'emoji' => '🎪'),
            'signage' => array('name' => 'Signage & Boards', 'emoji' => '🪧'),
            'wedding' => array('name' => 'Wedding & Events', 'emoji' => '💒'),
            'stickers' => array('name' => 'Stickers & Labels', 'emoji' => '🏷️'),
            'apparel' => array('name' => 'Apparel & Clothing', 'emoji' => '👕'),
            'vehicle' => array('name' => 'Vehicle Branding', 'emoji' => '🚗'),
            'corporate' => array('name' => 'Corporate Gifts', 'emoji' => '🎁'),
            'canvas' => array('name' => 'Canvas & Wall Art', 'emoji' => '🖼️'),
            'books' => array('name' => 'Books & NCR Pads', 'emoji' => '📚'),
            'events' => array('name' => 'Event Displays', 'emoji' => '⛺'),
            'special' => array('name' => 'Stamps & Special', 'emoji' => '✨'),
            'websites' => array('name' => 'Websites', 'emoji' => '🌐'),
            'services' => array('name' => 'Services', 'emoji' => '⚙️'),
        );
    }
    
    public static function get_defaults() {
        return array();
    }
    
    public static function get($key) {
        $products = self::get_all();
        return $products[$key] ?? null;
    }
    
    public static function get_min_price($product) {
        if (empty($product['variations'])) return 0;
        return min(array_column($product['variations'], 'price'));
    }
    
    public static function search($query) {
        $products = self::get_all();
        $query = strtolower($query);
        return array_filter($products, function($p) use ($query) {
            return strpos(strtolower($p['name']), $query) !== false ||
                   strpos(strtolower($p['description'] ?? ''), $query) !== false ||
                   strpos(strtolower($p['category'] ?? ''), $query) !== false;
        });
    }
    
    public static function get_profit_info($product, $variation_idx = 0) {
        if (empty($product['variations'][$variation_idx])) return null;
        $v = $product['variations'][$variation_idx];
        $cost = $v['cost'] ?? 0;
        $price = $v['price'] ?? 0;
        $profit = $price - $cost;
        $margin = $cost > 0 ? round(($profit / $cost) * 100) : 0;
        return array(
            'cost' => $cost,
            'price' => $price,
            'profit' => $profit,
            'margin' => $margin,
            'source' => $product['source'] ?? 'Unknown',
            'source_url' => $product['source_url'] ?? '#'
        );
    }
}
