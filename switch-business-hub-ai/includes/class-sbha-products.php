<?php
/**
 * Switch Graphics Product Catalog
 *
 * Includes:
 * - Print/signage/design products with source references
 * - Per-variant SKU, cost and selling price
 * - Normalized 75% markup baseline where cost exists
 *
 * @package SwitchBusinessHub
 * @version 2.2.1
 */

if (!defined('ABSPATH')) exit;

class SBHA_Products {
    
    /**
     * Get all products with source info
     */
    public static function get_all() {
        $defaults = array(
            // ========== WEBSITE PACKAGES ==========
            'website_starter' => array(
                'name' => 'Starter Website Package',
                'category' => 'websites',
                'description' => '🌐 Perfect for small businesses getting online. 5-page responsive website.',
                'source' => 'Switch Graphics Internal',
                'source_url' => 'https://switchgraphics.co.za',
                'variations' => array(
                    array('name' => 'Starter Package (5 pages)', 'cost' => 2500, 'price' => 3750, 'sku' => 'WEB-START'),
                    array('name' => 'With Logo Design', 'cost' => 3500, 'price' => 5250, 'sku' => 'WEB-START-LOGO'),
                ),
                'includes' => array('5 pages', 'Mobile responsive', 'Contact form', 'Google Maps', 'Social links', 'Basic SEO', '1 year hosting'),
            ),
            'website_business' => array(
                'name' => 'Business Website Package',
                'category' => 'websites',
                'description' => '💼 Professional business website with all the features you need.',
                'source' => 'Switch Graphics Internal',
                'source_url' => 'https://switchgraphics.co.za',
                'variations' => array(
                    array('name' => 'Business Package (10 pages)', 'cost' => 5000, 'price' => 7500, 'sku' => 'WEB-BIZ'),
                    array('name' => 'With E-commerce (up to 50 products)', 'cost' => 8000, 'price' => 12000, 'sku' => 'WEB-BIZ-SHOP'),
                ),
                'includes' => array('10 pages', 'Mobile responsive', 'Blog section', 'Gallery', 'Contact forms', 'WhatsApp integration', 'SEO optimization', '1 year hosting', 'SSL certificate'),
            ),
            'website_ecommerce' => array(
                'name' => 'E-Commerce Website',
                'category' => 'websites',
                'description' => '🛒 Full online store with payment gateway integration.',
                'source' => 'Switch Graphics Internal',
                'source_url' => 'https://switchgraphics.co.za',
                'variations' => array(
                    array('name' => 'E-Commerce (up to 100 products)', 'cost' => 10000, 'price' => 15000, 'sku' => 'WEB-ECOM'),
                    array('name' => 'E-Commerce Pro (unlimited products)', 'cost' => 15000, 'price' => 22500, 'sku' => 'WEB-ECOM-PRO'),
                ),
                'includes' => array('Unlimited pages', 'Product catalog', 'Shopping cart', 'PayFast/Yoco integration', 'Order management', 'Customer accounts', 'Inventory tracking', 'Shipping calculator', '1 year hosting'),
            ),
            'website_landing' => array(
                'name' => 'Landing Page',
                'category' => 'websites',
                'description' => '🎯 High-converting single page for campaigns and promotions.',
                'source' => 'Switch Graphics Internal',
                'source_url' => 'https://switchgraphics.co.za',
                'variations' => array(
                    array('name' => 'Basic Landing Page', 'cost' => 1500, 'price' => 2250, 'sku' => 'WEB-LAND'),
                    array('name' => 'Premium Landing Page', 'cost' => 2500, 'price' => 3750, 'sku' => 'WEB-LAND-PRO'),
                ),
            ),
            'website_maintenance' => array(
                'name' => 'Website Maintenance',
                'category' => 'websites',
                'description' => '🔧 Keep your website updated, secure and running smoothly.',
                'source' => 'Switch Graphics Internal',
                'source_url' => 'https://switchgraphics.co.za',
                'variations' => array(
                    array('name' => 'Monthly Maintenance', 'cost' => 500, 'price' => 750, 'sku' => 'WEB-MAINT-M'),
                    array('name' => 'Annual Maintenance', 'cost' => 5000, 'price' => 7500, 'sku' => 'WEB-MAINT-Y'),
                ),
            ),

            // ========== DESIGN SERVICES ==========
            'design_logo' => array(
                'name' => 'Logo Design',
                'category' => 'design',
                'description' => '🎨 Professional logo design for your brand.',
                'source' => 'Switch Graphics Internal',
                'source_url' => 'https://switchgraphics.co.za',
                'is_design_service' => true,
                'variations' => array(
                    array('name' => 'Basic Logo (2 concepts)', 'cost' => 800, 'price' => 1200, 'sku' => 'DES-LOGO-B'),
                    array('name' => 'Standard Logo (4 concepts)', 'cost' => 1000, 'price' => 1500, 'sku' => 'DES-LOGO-S'),
                    array('name' => 'Premium Logo (unlimited revisions)', 'cost' => 1500, 'price' => 2250, 'sku' => 'DES-LOGO-P'),
                ),
            ),
            'design_flyer' => array(
                'name' => 'Flyer/Poster Design',
                'category' => 'design',
                'description' => '📄 Eye-catching flyer or poster design.',
                'source' => 'Switch Graphics Internal',
                'source_url' => 'https://switchgraphics.co.za',
                'is_design_service' => true,
                'variations' => array(
                    array('name' => 'Single Sided Design', 'cost' => 200, 'price' => 350, 'sku' => 'DES-FLY-SS'),
                    array('name' => 'Double Sided Design', 'cost' => 350, 'price' => 550, 'sku' => 'DES-FLY-DS'),
                    array('name' => 'Complex Design (infographic)', 'cost' => 500, 'price' => 850, 'sku' => 'DES-FLY-C'),
                ),
            ),
            'design_social' => array(
                'name' => 'Social Media Design',
                'category' => 'design',
                'description' => '📱 Graphics for Facebook, Instagram, LinkedIn.',
                'source' => 'Switch Graphics Internal',
                'source_url' => 'https://switchgraphics.co.za',
                'is_design_service' => true,
                'variations' => array(
                    array('name' => 'Single Post Design', 'cost' => 100, 'price' => 150, 'sku' => 'DES-SOC-1'),
                    array('name' => '5 Post Package', 'cost' => 400, 'price' => 600, 'sku' => 'DES-SOC-5'),
                    array('name' => '10 Post Package', 'cost' => 700, 'price' => 1050, 'sku' => 'DES-SOC-10'),
                    array('name' => 'Monthly Package (20 posts)', 'cost' => 1200, 'price' => 1800, 'sku' => 'DES-SOC-M'),
                ),
            ),
            'design_brand' => array(
                'name' => 'Full Brand Package',
                'category' => 'design',
                'description' => '✨ Complete brand identity: logo, colors, fonts, guidelines.',
                'source' => 'Switch Graphics Internal',
                'source_url' => 'https://switchgraphics.co.za',
                'is_design_service' => true,
                'variations' => array(
                    array('name' => 'Starter Brand Package', 'cost' => 2000, 'price' => 3000, 'sku' => 'DES-BRAND-S'),
                    array('name' => 'Full Brand Package', 'cost' => 3500, 'price' => 5250, 'sku' => 'DES-BRAND-F'),
                ),
            ),

            // ========== BUSINESS CARDS ==========
            'standard_business_cards' => array(
                'name' => 'Standard Business Cards',
                'category' => 'business_cards',
                'description' => '🔥 Premium 350gsm business cards with full colour printing.',
                'source' => 'Vistaprint',
                'source_url' => 'https://www.vistaprint.co.za/business-cards',
                'variations' => array(
                    array('name' => '100 Cards - Single Sided', 'cost' => 150, 'price' => 225, 'sku' => 'BC-100-SS'),
                    array('name' => '100 Cards - Double Sided', 'cost' => 183, 'price' => 275, 'sku' => 'BC-100-DS'),
                    array('name' => '250 Cards - Single Sided', 'cost' => 217, 'price' => 325, 'sku' => 'BC-250-SS'),
                    array('name' => '250 Cards - Double Sided', 'cost' => 263, 'price' => 395, 'sku' => 'BC-250-DS'),
                    array('name' => '500 Cards - Single Sided', 'cost' => 300, 'price' => 450, 'sku' => 'BC-500-SS'),
                    array('name' => '500 Cards - Double Sided', 'cost' => 367, 'price' => 550, 'sku' => 'BC-500-DS'),
                    array('name' => '1000 Cards - Double Sided', 'cost' => 530, 'price' => 795, 'sku' => 'BC-1000-DS'),
                ),
            ),
            'premium_business_cards' => array(
                'name' => 'Premium Business Cards',
                'category' => 'business_cards',
                'description' => '⭐ 400gsm luxury stock with lamination.',
                'source' => 'Vistaprint',
                'source_url' => 'https://www.vistaprint.co.za/business-cards',
                'variations' => array(
                    array('name' => '100 Cards', 'cost' => 177, 'price' => 265, 'sku' => 'PBC-100'),
                    array('name' => '250 Cards', 'cost' => 263, 'price' => 395, 'sku' => 'PBC-250'),
                    array('name' => '500 Cards', 'cost' => 397, 'price' => 595, 'sku' => 'PBC-500'),
                ),
            ),

            // ========== FLYERS ==========
            'a5_flyers' => array(
                'name' => 'A5 Flyers',
                'category' => 'flyers',
                'description' => '📢 A5 (148x210mm) full colour flyers.',
                'source' => 'Vistaprint',
                'source_url' => 'https://www.vistaprint.co.za/flyers',
                'variations' => array(
                    array('name' => '100 Flyers - Single Sided', 'cost' => 125, 'price' => 188, 'sku' => 'A5F-100-SS'),
                    array('name' => '100 Flyers - Double Sided', 'cost' => 163, 'price' => 245, 'sku' => 'A5F-100-DS'),
                    array('name' => '250 Flyers - Single Sided', 'cost' => 197, 'price' => 295, 'sku' => 'A5F-250-SS'),
                    array('name' => '250 Flyers - Double Sided', 'cost' => 263, 'price' => 395, 'sku' => 'A5F-250-DS'),
                    array('name' => '500 Flyers - Single Sided', 'cost' => 300, 'price' => 450, 'sku' => 'A5F-500-SS'),
                    array('name' => '500 Flyers - Double Sided', 'cost' => 397, 'price' => 595, 'sku' => 'A5F-500-DS'),
                    array('name' => '1000 Flyers - Single Sided', 'cost' => 463, 'price' => 695, 'sku' => 'A5F-1000-SS'),
                    array('name' => '1000 Flyers - Double Sided', 'cost' => 597, 'price' => 895, 'sku' => 'A5F-1000-DS'),
                ),
            ),
            'a4_flyers' => array(
                'name' => 'A4 Flyers',
                'category' => 'flyers',
                'description' => '🎯 A4 (210x297mm) full colour flyers.',
                'source' => 'Vistaprint',
                'source_url' => 'https://www.vistaprint.co.za/flyers',
                'variations' => array(
                    array('name' => '100 Flyers - Single Sided', 'cost' => 141, 'price' => 212, 'sku' => 'A4F-100-SS'),
                    array('name' => '100 Flyers - Double Sided', 'cost' => 197, 'price' => 295, 'sku' => 'A4F-100-DS'),
                    array('name' => '250 Flyers - Single Sided', 'cost' => 263, 'price' => 395, 'sku' => 'A4F-250-SS'),
                    array('name' => '500 Flyers - Single Sided', 'cost' => 397, 'price' => 595, 'sku' => 'A4F-500-SS'),
                    array('name' => '1000 Flyers - Single Sided', 'cost' => 597, 'price' => 895, 'sku' => 'A4F-1000-SS'),
                ),
            ),
            'dl_flyers' => array(
                'name' => 'DL Flyers',
                'category' => 'flyers',
                'description' => '📬 DL (99x210mm) rack cards.',
                'source' => 'Vistaprint',
                'source_url' => 'https://www.vistaprint.co.za/flyers',
                'variations' => array(
                    array('name' => '100 Flyers - Single Sided', 'cost' => 110, 'price' => 165, 'sku' => 'DLF-100-SS'),
                    array('name' => '250 Flyers - Single Sided', 'cost' => 177, 'price' => 265, 'sku' => 'DLF-250-SS'),
                    array('name' => '500 Flyers - Single Sided', 'cost' => 263, 'price' => 395, 'sku' => 'DLF-500-SS'),
                ),
            ),

            // ========== POSTERS ==========
            'paper_posters' => array(
                'name' => 'Paper Posters',
                'category' => 'posters',
                'description' => '🎨 Full colour gloss or satin posters.',
                'source' => 'Vistaprint',
                'source_url' => 'https://www.vistaprint.co.za/posters',
                'variations' => array(
                    array('name' => 'A4 Poster', 'cost' => 10, 'price' => 15, 'sku' => 'PP-A4'),
                    array('name' => 'A3 Poster', 'cost' => 17, 'price' => 25, 'sku' => 'PP-A3'),
                    array('name' => 'A2 Poster', 'cost' => 30, 'price' => 45, 'sku' => 'PP-A2'),
                    array('name' => 'A1 Poster', 'cost' => 50, 'price' => 75, 'sku' => 'PP-A1'),
                    array('name' => 'A0 Poster', 'cost' => 83, 'price' => 125, 'sku' => 'PP-A0'),
                ),
            ),

            // ========== BANNERS ==========
            'pvc_banners' => array(
                'name' => 'PVC Banners',
                'category' => 'banners',
                'description' => '🚀 440gsm weatherproof with eyelets.',
                'source' => 'Bannerxpress',
                'source_url' => 'https://www.bannerxpress.co.za',
                'variations' => array(
                    array('name' => '1m x 1m Banner', 'cost' => 173, 'price' => 259, 'sku' => 'PVC-1x1'),
                    array('name' => '2m x 1m Banner', 'cost' => 300, 'price' => 450, 'sku' => 'PVC-2x1'),
                    array('name' => '3m x 1m Banner', 'cost' => 397, 'price' => 595, 'sku' => 'PVC-3x1'),
                    array('name' => '2m x 3m Banner', 'cost' => 720, 'price' => 1080, 'sku' => 'PVC-2x3'),
                ),
            ),
            'pullup_banners' => array(
                'name' => 'Pull-Up Banners',
                'category' => 'banners',
                'description' => '⚡ Retractable with carry case.',
                'source' => 'Bannerxpress',
                'source_url' => 'https://www.bannerxpress.co.za',
                'variations' => array(
                    array('name' => '850mm x 2000mm Economy', 'cost' => 403, 'price' => 604, 'sku' => 'PUB-850-ECO'),
                    array('name' => '850mm x 2000mm Standard', 'cost' => 530, 'price' => 795, 'sku' => 'PUB-850-STD'),
                    array('name' => '1000mm x 2000mm', 'cost' => 597, 'price' => 895, 'sku' => 'PUB-1000'),
                ),
            ),

            // ========== SIGNAGE ==========
            'correx_boards' => array(
                'name' => 'Correx Boards',
                'category' => 'signage',
                'description' => '🏠 Lightweight corrugated plastic.',
                'source' => 'Sign Warehouse',
                'source_url' => 'https://www.signwarehouse.co.za',
                'variations' => array(
                    array('name' => 'A3 Correx', 'cost' => 50, 'price' => 75, 'sku' => 'CX-A3'),
                    array('name' => 'A2 Correx', 'cost' => 77, 'price' => 116, 'sku' => 'CX-A2'),
                    array('name' => 'A1 Correx', 'cost' => 117, 'price' => 175, 'sku' => 'CX-A1'),
                    array('name' => '600x900mm Correx', 'cost' => 130, 'price' => 195, 'sku' => 'CX-6090'),
                ),
            ),

            // ========== MUGS ==========
            'personalised_mugs' => array(
                'name' => 'Personalised Mugs',
                'category' => 'mugs',
                'description' => '☕ 11oz ceramic with full wrap print.',
                'source' => 'Printulu',
                'source_url' => 'https://www.printulu.co.za/mugs',
                'variations' => array(
                    array('name' => '1 Mug', 'cost' => 60, 'price' => 90, 'sku' => 'MUG-1'),
                    array('name' => '5 Mugs', 'cost' => 263, 'price' => 395, 'sku' => 'MUG-5'),
                    array('name' => '10 Mugs', 'cost' => 463, 'price' => 695, 'sku' => 'MUG-10'),
                    array('name' => '25 Mugs', 'cost' => 997, 'price' => 1495, 'sku' => 'MUG-25'),
                ),
            ),

            // ========== STICKERS ==========
            'custom_stickers' => array(
                'name' => 'Custom Stickers',
                'category' => 'stickers',
                'description' => '🎉 Vinyl or paper, any shape.',
                'source' => 'Printulu',
                'source_url' => 'https://www.printulu.co.za/stickers',
                'variations' => array(
                    array('name' => '50 Stickers (50x50mm)', 'cost' => 100, 'price' => 150, 'sku' => 'STK-50'),
                    array('name' => '100 Stickers (50x50mm)', 'cost' => 150, 'price' => 225, 'sku' => 'STK-100'),
                    array('name' => '250 Stickers (50x50mm)', 'cost' => 263, 'price' => 395, 'sku' => 'STK-250'),
                    array('name' => '500 Stickers (50x50mm)', 'cost' => 397, 'price' => 595, 'sku' => 'STK-500'),
                ),
            ),

            // ========== CANVAS ==========
            'canvas_prints' => array(
                'name' => 'Canvas Prints',
                'category' => 'canvas',
                'description' => '🖼️ Gallery wrapped, ready to hang.',
                'source' => 'Canvas Factory',
                'source_url' => 'https://www.canvasfactory.co.za',
                'variations' => array(
                    array('name' => '30x30cm Canvas', 'cost' => 150, 'price' => 225, 'sku' => 'CV-3030'),
                    array('name' => '40x30cm Canvas', 'cost' => 197, 'price' => 295, 'sku' => 'CV-4030'),
                    array('name' => '60x40cm Canvas', 'cost' => 300, 'price' => 450, 'sku' => 'CV-6040'),
                    array('name' => '80x60cm Canvas', 'cost' => 397, 'price' => 595, 'sku' => 'CV-8060'),
                ),
            ),

            // ========== NCR BOOKS ==========
            'ncr_books' => array(
                'name' => 'NCR Invoice Books',
                'category' => 'books',
                'description' => '📋 Carbonless duplicate/triplicate.',
                'source' => 'Vistaprint',
                'source_url' => 'https://www.vistaprint.co.za/ncr-forms',
                'variations' => array(
                    array('name' => 'A5 Duplicate (50 sets)', 'cost' => 330, 'price' => 495, 'sku' => 'NCR-A5-DUP'),
                    array('name' => 'A5 Triplicate (50 sets)', 'cost' => 397, 'price' => 595, 'sku' => 'NCR-A5-TRI'),
                    array('name' => 'A4 Duplicate (50 sets)', 'cost' => 425, 'price' => 638, 'sku' => 'NCR-A4-DUP'),
                    array('name' => 'A4 Triplicate (50 sets)', 'cost' => 475, 'price' => 713, 'sku' => 'NCR-A4-TRI'),
                ),
            ),

            // ========== VEHICLE ==========
            'vehicle_decals' => array(
                'name' => 'Vehicle Decals',
                'category' => 'vehicle',
                'description' => '🚗 Vinyl for cars, bakkies, vans.',
                'source' => 'Sign Warehouse',
                'source_url' => 'https://www.signwarehouse.co.za',
                'variations' => array(
                    array('name' => 'Door Decals (pair)', 'cost' => 597, 'price' => 895, 'sku' => 'VD-DOOR'),
                    array('name' => 'Rear Window', 'cost' => 433, 'price' => 650, 'sku' => 'VD-REAR'),
                    array('name' => 'Full DIY Kit', 'cost' => 1100, 'price' => 1650, 'sku' => 'VD-KIT'),
                ),
            ),

            // ========== EVENTS ==========
            'gazebos' => array(
                'name' => 'Branded Gazebos',
                'category' => 'events',
                'description' => '⛺ 3x3m branded event gazebos.',
                'source' => 'Display Solutions',
                'source_url' => 'https://www.displaysolutions.co.za',
                'variations' => array(
                    array('name' => 'Canopy Only', 'cost' => 1997, 'price' => 2995, 'sku' => 'GAZ-CAN'),
                    array('name' => 'Full Gazebo with Frame', 'cost' => 3500, 'price' => 5250, 'sku' => 'GAZ-FULL'),
                ),
            ),
            'flags' => array(
                'name' => 'Feather Flags',
                'category' => 'events',
                'description' => '🚩 Attention-grabbing outdoor flags.',
                'source' => 'Display Solutions',
                'source_url' => 'https://www.displaysolutions.co.za',
                'variations' => array(
                    array('name' => '2m Teardrop Flag', 'cost' => 530, 'price' => 795, 'sku' => 'FLAG-2T'),
                    array('name' => '3m Teardrop Flag', 'cost' => 650, 'price' => 975, 'sku' => 'FLAG-3T'),
                    array('name' => '4m Feather Flag', 'cost' => 730, 'price' => 1095, 'sku' => 'FLAG-4F'),
                ),
            ),

            // ========== SPECIAL ==========
            'stamps' => array(
                'name' => 'Rubber Stamps',
                'category' => 'special',
                'description' => '📝 Self-inking custom stamps.',
                'source' => 'Stamp It',
                'source_url' => 'https://www.stampit.co.za',
                'variations' => array(
                    array('name' => 'Small (38x14mm)', 'cost' => 130, 'price' => 195, 'sku' => 'STP-S'),
                    array('name' => 'Medium (58x22mm)', 'cost' => 167, 'price' => 250, 'sku' => 'STP-M'),
                    array('name' => 'Large (70x30mm)', 'cost' => 209, 'price' => 313, 'sku' => 'STP-L'),
                ),
            ),

            // ========== WEDDING SIGNAGE ==========
            'wedding_welcome_boards' => array(
                'name' => 'Wedding Welcome Boards',
                'category' => 'wedding',
                'description' => '💒 Elegant welcome signs with A1 options in Correx, Forex and Perspex.',
                'source' => 'Vistaprinters + Local Signage',
                'source_url' => 'http://www.vistaprinters.co.za/',
                'variations' => array(
                    array('name' => 'A2 Correx Board', 'cost' => 190, 'price' => 333, 'sku' => 'WWB-A2-COR'),
                    array('name' => 'A1 Correx Board', 'cost' => 250, 'price' => 438, 'sku' => 'WWB-A1-COR'),
                    array('name' => 'A1 Forex Board 5mm', 'cost' => 360, 'price' => 630, 'sku' => 'WWB-A1-FOR'),
                    array('name' => 'A1 Perspex Clear 3mm', 'cost' => 720, 'price' => 1260, 'sku' => 'WWB-A1-PER'),
                    array('name' => 'A1 Mirror Perspex Gold/Silver', 'cost' => 980, 'price' => 1715, 'sku' => 'WWB-A1-MIR'),
                ),
            ),
            'wedding_seating_charts' => array(
                'name' => 'Wedding Seating Charts',
                'category' => 'wedding',
                'description' => '🪑 Seating plan boards with premium materials and finishing.',
                'source' => 'Vistaprinters + Local Signage',
                'source_url' => 'http://www.vistaprinters.co.za/',
                'variations' => array(
                    array('name' => 'A2 Foam Board Seating Chart', 'cost' => 280, 'price' => 490, 'sku' => 'WSC-A2-FOAM'),
                    array('name' => 'A1 Foam Board Seating Chart', 'cost' => 390, 'price' => 683, 'sku' => 'WSC-A1-FOAM'),
                    array('name' => 'A1 Perspex Seating Chart', 'cost' => 860, 'price' => 1505, 'sku' => 'WSC-A1-PER'),
                ),
            ),

            // ========== VISTAPRINTER-LIKE PRINT RANGE ==========
            'letterheads' => array(
                'name' => 'Letterheads',
                'category' => 'printing',
                'description' => '📄 Professional A4 letterheads on premium uncoated stock.',
                'source' => 'Vistaprinters',
                'source_url' => 'http://www.vistaprinters.co.za/',
                'variations' => array(
                    array('name' => '100 A4 Letterheads 120gsm', 'cost' => 180, 'price' => 315, 'sku' => 'LH-100'),
                    array('name' => '250 A4 Letterheads 120gsm', 'cost' => 310, 'price' => 543, 'sku' => 'LH-250'),
                    array('name' => '500 A4 Letterheads 120gsm', 'cost' => 540, 'price' => 945, 'sku' => 'LH-500'),
                ),
            ),
            'compliment_slips' => array(
                'name' => 'Compliment Slips',
                'category' => 'printing',
                'description' => '✉️ DL compliment slips for branded correspondence.',
                'source' => 'Vistaprinters',
                'source_url' => 'http://www.vistaprinters.co.za/',
                'variations' => array(
                    array('name' => '250 DL Slips', 'cost' => 140, 'price' => 245, 'sku' => 'CS-250'),
                    array('name' => '500 DL Slips', 'cost' => 230, 'price' => 403, 'sku' => 'CS-500'),
                    array('name' => '1000 DL Slips', 'cost' => 390, 'price' => 683, 'sku' => 'CS-1000'),
                ),
            ),
            'brochures' => array(
                'name' => 'Brochures',
                'category' => 'printing',
                'description' => '📘 Folded brochures for menus, events and marketing packs.',
                'source' => 'Vistaprinters',
                'source_url' => 'http://www.vistaprinters.co.za/',
                'variations' => array(
                    array('name' => 'A4 Tri-Fold 150gsm (100)', 'cost' => 420, 'price' => 735, 'sku' => 'BR-A4-TF-100'),
                    array('name' => 'A4 Tri-Fold 150gsm (250)', 'cost' => 780, 'price' => 1365, 'sku' => 'BR-A4-TF-250'),
                    array('name' => 'A5 Bi-Fold 170gsm (250)', 'cost' => 690, 'price' => 1208, 'sku' => 'BR-A5-BF-250'),
                ),
            ),
            'presentation_folders' => array(
                'name' => 'Presentation Folders',
                'category' => 'printing',
                'description' => '📁 Branded folders with business card slit and pockets.',
                'source' => 'Vistaprinters',
                'source_url' => 'http://www.vistaprinters.co.za/',
                'variations' => array(
                    array('name' => '100 Folders 350gsm', 'cost' => 890, 'price' => 1558, 'sku' => 'PF-100'),
                    array('name' => '250 Folders 350gsm', 'cost' => 1750, 'price' => 3063, 'sku' => 'PF-250'),
                ),
            ),
            'postcards' => array(
                'name' => 'Postcards',
                'category' => 'printing',
                'description' => '📮 Full-colour postcard marketing cards.',
                'source' => 'Vistaprinters',
                'source_url' => 'http://www.vistaprinters.co.za/',
                'variations' => array(
                    array('name' => 'A6 Postcards 350gsm (100)', 'cost' => 170, 'price' => 298, 'sku' => 'PC-A6-100'),
                    array('name' => 'A6 Postcards 350gsm (250)', 'cost' => 310, 'price' => 543, 'sku' => 'PC-A6-250'),
                    array('name' => 'DL Postcards 350gsm (500)', 'cost' => 520, 'price' => 910, 'sku' => 'PC-DL-500'),
                ),
            ),
            'fabric_banners' => array(
                'name' => 'Fabric Banners',
                'category' => 'banners',
                'description' => '🧵 Premium fabric banners for indoor events and shows.',
                'source' => 'Vistaprinters + Printulu',
                'source_url' => 'https://www.printulu.co.za/',
                'variations' => array(
                    array('name' => '1.5m x 1m Fabric Banner', 'cost' => 510, 'price' => 893, 'sku' => 'FB-15X10'),
                    array('name' => '2m x 1m Fabric Banner', 'cost' => 640, 'price' => 1120, 'sku' => 'FB-20X10'),
                    array('name' => '3m x 1m Fabric Banner', 'cost' => 920, 'price' => 1610, 'sku' => 'FB-30X10'),
                ),
            ),

            // ========== ADDITIONAL SUPPLIERS ==========
            'printability_booklets' => array(
                'name' => 'Saddle-Stitched Booklets',
                'category' => 'printing',
                'description' => '📘 Multi-page booklet printing from Printability Press.',
                'source' => 'Printability Press',
                'source_url' => 'https://www.printabilitypress.co.za/',
                'supplier_links' => array('https://www.printabilitypress.co.za/'),
                'variations' => array(
                    array('name' => 'A5 Booklet 8pp (100)', 'cost' => 980, 'price' => 1715, 'sku' => 'PP-BKT-A5-100'),
                    array('name' => 'A5 Booklet 12pp (100)', 'cost' => 1320, 'price' => 2310, 'sku' => 'PP-BKT-A5-12-100'),
                    array('name' => 'A4 Booklet 8pp (100)', 'cost' => 1680, 'price' => 2940, 'sku' => 'PP-BKT-A4-100'),
                ),
            ),
            'printability_posters_large' => array(
                'name' => 'Large Format Posters',
                'category' => 'posters',
                'description' => '🖼️ High-impact indoor/outdoor posters from Printability Press.',
                'source' => 'Printability Press',
                'source_url' => 'https://www.printabilitypress.co.za/',
                'supplier_links' => array('https://www.printabilitypress.co.za/'),
                'variations' => array(
                    array('name' => 'A1 Poster 200gsm', 'cost' => 120, 'price' => 210, 'sku' => 'PP-POS-A1'),
                    array('name' => 'A0 Poster 200gsm', 'cost' => 190, 'price' => 333, 'sku' => 'PP-POS-A0'),
                    array('name' => 'A0 Laminated Poster', 'cost' => 260, 'price' => 455, 'sku' => 'PP-POS-A0-LAM'),
                ),
            ),
            'emdee_branded_tshirts' => array(
                'name' => 'Branded T-Shirts',
                'category' => 'apparel',
                'description' => '👕 Promotional and staff t-shirts via Emdee Branding.',
                'source' => 'Emdee Branding',
                'source_url' => 'https://emdeebranding.co.za/',
                'supplier_links' => array('https://emdeebranding.co.za/'),
                'variations' => array(
                    array('name' => '10x Branded T-Shirts', 'cost' => 850, 'price' => 1488, 'sku' => 'EMD-TS-10'),
                    array('name' => '25x Branded T-Shirts', 'cost' => 1980, 'price' => 3465, 'sku' => 'EMD-TS-25'),
                    array('name' => '50x Branded T-Shirts', 'cost' => 3720, 'price' => 6510, 'sku' => 'EMD-TS-50'),
                ),
            ),
            'emdee_corp_sets' => array(
                'name' => 'Corporate Gifting Sets',
                'category' => 'apparel',
                'description' => '🎁 Branded promo kits and gifting sets from Emdee Branding.',
                'source' => 'Emdee Branding',
                'source_url' => 'https://emdeebranding.co.za/',
                'supplier_links' => array('https://emdeebranding.co.za/'),
                'variations' => array(
                    array('name' => '25x Pen + Notebook Sets', 'cost' => 1250, 'price' => 2188, 'sku' => 'EMD-GIFT-25'),
                    array('name' => '50x Pen + Notebook Sets', 'cost' => 2380, 'price' => 4165, 'sku' => 'EMD-GIFT-50'),
                ),
            ),
            'displaymania_x_banners' => array(
                'name' => 'X-Banner Stands',
                'category' => 'display',
                'description' => '🧷 Portable x-banner display systems from Display Mania.',
                'source' => 'Display Mania',
                'source_url' => 'https://displaymania.co.za/',
                'supplier_links' => array('https://displaymania.co.za/'),
                'variations' => array(
                    array('name' => '600x1600mm X-Banner', 'cost' => 340, 'price' => 595, 'sku' => 'DM-XB-60'),
                    array('name' => '800x1800mm X-Banner', 'cost' => 470, 'price' => 823, 'sku' => 'DM-XB-80'),
                ),
            ),
            'displaymania_backdrops' => array(
                'name' => 'Media Backdrop Stands',
                'category' => 'display',
                'description' => '🎬 Event media wall backdrops and displays from Display Mania.',
                'source' => 'Display Mania',
                'source_url' => 'https://displaymania.co.za/',
                'supplier_links' => array('https://displaymania.co.za/'),
                'variations' => array(
                    array('name' => '2.4m Curved Backdrop', 'cost' => 1850, 'price' => 3238, 'sku' => 'DM-BD-24'),
                    array('name' => '3m Straight Backdrop', 'cost' => 2450, 'price' => 4288, 'sku' => 'DM-BD-30'),
                ),
            ),

            // ========== CORE SERVICES ==========
            'design_service' => array(
                'name' => 'Design Service',
                'category' => 'services',
                'description' => '🎨 Creative layout/design support when you do not have a print-ready file.',
                'source' => 'Switch Graphics Internal',
                'source_url' => 'https://switchgraphics.co.za',
                'is_design_service' => true,
                'is_service' => true,
                'variations' => array(
                    array('name' => 'Basic Design (single artwork)', 'cost' => 200, 'price' => 350, 'sku' => 'DS-BASIC'),
                    array('name' => 'Advanced Design (multi-element)', 'cost' => 430, 'price' => 753, 'sku' => 'DS-ADV'),
                ),
            ),
            'custom_jobs_hourly' => array(
                'name' => 'Custom Jobs (Hourly)',
                'category' => 'services',
                'description' => '🛠️ Custom studio, setup, or production work billed per hour.',
                'source' => 'Switch Graphics Internal',
                'source_url' => 'https://switchgraphics.co.za',
                'is_service' => true,
                'variations' => array(
                    array('name' => 'Custom Job - Per Hour', 'cost' => 200, 'price' => 350, 'sku' => 'CJOB-HR'),
                ),
            ),

            // ========== DELIVERY ==========
            'delivery' => array(
                'name' => 'Delivery Service',
                'category' => 'services',
                'description' => '🚚 Delivery to your door.',
                'source' => 'Switch Graphics Internal',
                'source_url' => 'https://switchgraphics.co.za',
                'is_service' => true,
                'variations' => array(
                    array('name' => 'Local Delivery (within Newcastle)', 'cost' => 92, 'price' => 161, 'sku' => 'DEL-LOC'),
                    array('name' => 'Regional Delivery (KZN)', 'cost' => 140, 'price' => 245, 'sku' => 'DEL-KZN'),
                    array('name' => 'National Courier Delivery', 'cost' => 230, 'price' => 403, 'sku' => 'DEL-NAT'),
                ),
            ),
        );

        // Merge with custom products from database
        $custom = get_option('sbha_custom_products', array());
        if (!empty($custom)) {
            foreach ($custom as $key => $product) {
                $defaults[$key] = $product; // Override or add
            }
        }

        self::normalize_catalog($defaults);
        
        return $defaults;
    }
    
    /**
     * Get default products (before customization)
     */
    public static function get_defaults() {
        // This would need to be a separate hardcoded array to avoid recursion
        // For now, just return empty - use get_all() instead
        return array();
    }
    
    public static function get_categories() {
        return array(
            'printing' => array('name' => 'Printing', 'emoji' => '🖨️', 'icon' => '🖨️'),
            'websites' => array('name' => 'Websites', 'emoji' => '🌐', 'icon' => '🌐'),
            'design' => array('name' => 'Design Services', 'emoji' => '🎨', 'icon' => '🎨'),
            'business_cards' => array('name' => 'Business Cards', 'emoji' => '💳', 'icon' => '💳'),
            'flyers' => array('name' => 'Flyers', 'emoji' => '📄', 'icon' => '📄'),
            'posters' => array('name' => 'Posters', 'emoji' => '🖼️', 'icon' => '🖼️'),
            'banners' => array('name' => 'Banners', 'emoji' => '🎪', 'icon' => '🎪'),
            'signage' => array('name' => 'Signage', 'emoji' => '🪧', 'icon' => '🪧'),
            'wedding' => array('name' => 'Wedding & Events', 'emoji' => '💒', 'icon' => '💒'),
            'mugs' => array('name' => 'Mugs', 'emoji' => '☕', 'icon' => '☕'),
            'stickers' => array('name' => 'Stickers', 'emoji' => '🏷️', 'icon' => '🏷️'),
            'canvas' => array('name' => 'Canvas', 'emoji' => '🖼️', 'icon' => '🖼️'),
            'books' => array('name' => 'Books & NCR', 'emoji' => '📚', 'icon' => '📚'),
            'vehicle' => array('name' => 'Vehicle', 'emoji' => '🚗', 'icon' => '🚗'),
            'events' => array('name' => 'Events', 'emoji' => '⛺', 'icon' => '⛺'),
            'apparel' => array('name' => 'Apparel', 'emoji' => '👕', 'icon' => '👕'),
            'display' => array('name' => 'Display Systems', 'emoji' => '🖼️', 'icon' => '🖼️'),
            'special' => array('name' => 'Special', 'emoji' => '✨', 'icon' => '✨'),
            'services' => array('name' => 'Services', 'emoji' => '⚙️', 'icon' => '⚙️'),
        );
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
                   strpos(strtolower($p['description']), $query) !== false;
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

    /**
     * Resolve product image URL from custom product or settings.
     */
    public static function get_product_image($key, $product = array()) {
        if (!empty($product['image_url'])) {
            return (string) $product['image_url'];
        }

        $stored = get_option('sbha_product_image_' . $key, '');
        if (!empty($stored)) {
            return (string) $stored;
        }

        return self::build_generated_catalog_image($key, $product);
    }

    /**
     * Apply consistent catalog rules:
     * - Enforce minimum 75% markup where cost > 0
     * - Ensure each variation has an SKU
     * - Ensure each product has a product_type marker
     */
    private static function normalize_catalog(&$products) {
        foreach ($products as $product_key => &$product) {
            if (empty($product['variations']) || !is_array($product['variations'])) {
                $product['variations'] = array();
            }

            if (empty($product['product_type'])) {
                if (!empty($product['is_design_service'])) {
                    $product['product_type'] = 'design';
                } elseif (($product['category'] ?? '') === 'websites') {
                    $product['product_type'] = 'digital';
                } else {
                    $product['product_type'] = 'physical';
                }
            }

            foreach ($product['variations'] as $index => &$variation) {
                $cost = isset($variation['cost']) ? floatval($variation['cost']) : 0;
                $price = isset($variation['price']) ? floatval($variation['price']) : 0;

                if ($cost > 0) {
                    $minimum_price = self::apply_markup($cost, 0.75);
                    if ($price < $minimum_price) {
                        $price = $minimum_price;
                    }
                }

                if ($price <= 0 && $cost > 0) {
                    $price = self::apply_markup($cost, 0.75);
                }

                $variation['cost'] = $cost;
                $variation['price'] = $price;

                if (empty($variation['sku'])) {
                    $variation['sku'] = self::build_sku($product_key, $variation['name'] ?? '', $index);
                }
            }
            unset($variation);
        }
        unset($product);
    }

    private static function apply_markup($cost, $markup_rate = 0.75) {
        return round(floatval($cost) * (1 + floatval($markup_rate)), 2);
    }

    private static function build_sku($product_key, $variation_name, $index) {
        $base = strtoupper(preg_replace('/[^A-Z0-9]/', '', substr($product_key, 0, 8)));
        if (strlen($base) < 3) {
            $base = str_pad($base, 3, 'X');
        }

        $suffix = strtoupper(substr(preg_replace('/[^A-Z0-9]/', '', $variation_name), 0, 4));
        if (empty($suffix)) {
            $suffix = 'VAR' . str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT);
        }

        return $base . '-' . $suffix;
    }

    /**
     * Build generated in-memory SVG mockup for products with no custom image.
     * This creates a premium "stock" look while keeping branding visible.
     */
    private static function build_generated_catalog_image($key, $product) {
        $title = $product['name'] ?? ucwords(str_replace('_', ' ', $key));
        $category = strtolower($product['category'] ?? 'services');
        $palette = self::get_category_palette($category);
        $logo_url = trim((string) get_option('sbha_business_logo', ''));
        $business_name = trim((string) get_option('sbha_business_name', 'Switch Graphics'));

        $title = substr((string) $title, 0, 34);
        $subtitle = $business_name !== '' ? $business_name : 'Switch Graphics';

        $mockup = '<rect x="560" y="180" width="248" height="164" rx="14" fill="rgba(255,255,255,0.08)" stroke="rgba(255,255,255,0.24)" stroke-width="2"/>'
            . '<rect x="588" y="214" width="190" height="24" rx="8" fill="rgba(255,255,255,0.2)"/>'
            . '<rect x="588" y="250" width="140" height="14" rx="6" fill="rgba(255,255,255,0.14)"/>'
            . '<rect x="588" y="274" width="106" height="10" rx="5" fill="rgba(255,255,255,0.12)"/>';

        if ($category === 'business_cards') {
            $mockup = '<g transform="rotate(-8 670 272)"><rect x="548" y="198" width="250" height="160" rx="14" fill="#f8fafc" opacity="0.95"/>'
                . '<rect x="576" y="226" width="172" height="22" rx="6" fill="#111827" opacity="0.9"/>'
                . '<rect x="576" y="258" width="130" height="12" rx="6" fill="#6b7280"/>'
                . '<rect x="576" y="278" width="98" height="10" rx="5" fill="#9ca3af"/></g>';
        } elseif ($category === 'flyers' || $category === 'posters') {
            $mockup = '<rect x="574" y="156" width="220" height="306" rx="10" fill="#ffffff" opacity="0.94"/>'
                . '<rect x="596" y="192" width="176" height="84" rx="8" fill="#FF6600" opacity="0.92"/>'
                . '<rect x="596" y="292" width="158" height="14" rx="7" fill="#111827" opacity="0.8"/>'
                . '<rect x="596" y="318" width="132" height="12" rx="6" fill="#6b7280" opacity="0.7"/>'
                . '<rect x="596" y="340" width="108" height="12" rx="6" fill="#9ca3af" opacity="0.7"/>';
        } elseif ($category === 'signage' || $category === 'wedding') {
            $mockup = '<rect x="548" y="176" width="260" height="180" rx="8" fill="rgba(255,255,255,0.1)" stroke="rgba(255,255,255,0.3)" stroke-width="3"/>'
                . '<rect x="574" y="208" width="210" height="24" rx="8" fill="rgba(255,255,255,0.2)"/>'
                . '<rect x="574" y="242" width="150" height="12" rx="6" fill="rgba(255,255,255,0.14)"/>'
                . '<rect x="628" y="364" width="100" height="10" rx="5" fill="rgba(255,255,255,0.26)"/>';
        }

        $logo_image = '';
        if ($logo_url !== '') {
            $logo_image = '<image x="590" y="376" width="74" height="74" href="' . self::svg_escape($logo_url) . '" preserveAspectRatio="xMidYMid meet" />';
        }

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 960 640">'
            . '<defs><linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">'
            . '<stop offset="0%" stop-color="' . $palette['bg1'] . '"/>'
            . '<stop offset="100%" stop-color="' . $palette['bg2'] . '"/>'
            . '</linearGradient>'
            . '<filter id="shadow" x="-20%" y="-20%" width="140%" height="140%">'
            . '<feDropShadow dx="0" dy="8" stdDeviation="14" flood-color="#000" flood-opacity="0.35"/>'
            . '</filter></defs>'
            . '<rect width="960" height="640" fill="url(#bg)"/>'
            . '<rect x="72" y="72" width="816" height="496" rx="28" fill="rgba(10,10,10,0.5)" stroke="rgba(255,255,255,0.16)" stroke-width="2"/>'
            . '<rect x="116" y="126" width="728" height="280" rx="20" fill="' . $palette['panel'] . '" filter="url(#shadow)"/>'
            . '<circle cx="170" cy="182" r="18" fill="#FF6600"/>'
            . '<circle cx="206" cy="182" r="11" fill="#FDBA2D"/>'
            . '<rect x="150" y="238" width="660" height="16" rx="8" fill="rgba(255,255,255,0.18)"/>'
            . '<rect x="150" y="268" width="510" height="12" rx="6" fill="rgba(255,255,255,0.14)"/>'
            . $mockup
            . $logo_image
            . '<rect x="150" y="468" width="256" height="60" rx="12" fill="#FF6600"/>'
            . '<text x="170" y="506" font-size="30" font-weight="700" fill="#fff">View Options</text>'
            . '<text x="150" y="352" font-size="54" font-weight="800" fill="#ffffff" font-family="Inter,Arial,sans-serif">' . self::svg_escape($title) . '</text>'
            . '<text x="150" y="392" font-size="24" fill="rgba(255,255,255,0.78)" font-family="Inter,Arial,sans-serif">' . self::svg_escape($subtitle) . '</text>'
            . '</svg>';

        return 'data:image/svg+xml;charset=utf-8,' . rawurlencode($svg);
    }

    private static function get_category_palette($category) {
        $palettes = array(
            'design' => array('bg1' => '#13070f', 'bg2' => '#2e0f18', 'panel' => '#371327'),
            'websites' => array('bg1' => '#040d18', 'bg2' => '#12253f', 'panel' => '#163257'),
            'business_cards' => array('bg1' => '#121212', 'bg2' => '#2a2a2a', 'panel' => '#1f1f1f'),
            'signage' => array('bg1' => '#0f0c04', 'bg2' => '#2f220a', 'panel' => '#47310d'),
            'wedding' => array('bg1' => '#1b1018', 'bg2' => '#3b1c31', 'panel' => '#4f2341'),
            'apparel' => array('bg1' => '#0a1015', 'bg2' => '#183046', 'panel' => '#1f3b58'),
            'display' => array('bg1' => '#0d0f12', 'bg2' => '#1d2c39', 'panel' => '#24384a'),
            'printing' => array('bg1' => '#0f1318', 'bg2' => '#243040', 'panel' => '#2d3e52'),
            'default' => array('bg1' => '#0f0f0f', 'bg2' => '#222', 'panel' => '#2a2a2a')
        );

        return $palettes[$category] ?? $palettes['default'];
    }

    private static function svg_escape($text) {
        return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
    }
}
