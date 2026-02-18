<?php
/**
 * Plugin Name: Switch Business Hub AI
 * Plugin URI: https://switchgraphics.co.za
 * Description: AI-Powered Customer Portal for Switch Graphics - Printing, Signage, Apparel & More
 * Version: 3.0.0
 * Author: Switch Graphics (Pty) Ltd
 * Author URI: https://switchgraphics.co.za
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: switch-business-hub
 * Domain Path: /languages
 *
 * @package SwitchBusinessHub
 */

if (!defined('ABSPATH')) {
    exit;
}

define('SBHA_VERSION', '3.0.0');
define('SBHA_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SBHA_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SBHA_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('SBHA_DB_VERSION', '1.3.0');

final class Switch_Business_Hub_AI {

    private static $instance = null;

    public $admin;
    public $public_interface;
    public $ai_engine;
    public $job_manager;
    public $service_catalog;
    public $analytics;
    public $recommendations;

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }

    private function load_dependencies() {
        require_once SBHA_PLUGIN_DIR . 'includes/class-sbha-installer.php';
        require_once SBHA_PLUGIN_DIR . 'includes/class-sbha-database.php';
        require_once SBHA_PLUGIN_DIR . 'includes/class-sbha-products.php';
        require_once SBHA_PLUGIN_DIR . 'includes/class-sbha-smart-ai.php';
        require_once SBHA_PLUGIN_DIR . 'includes/class-sbha-ai-engine.php';
        require_once SBHA_PLUGIN_DIR . 'includes/class-sbha-service-catalog.php';
        require_once SBHA_PLUGIN_DIR . 'includes/class-sbha-job-manager.php';
        require_once SBHA_PLUGIN_DIR . 'includes/class-sbha-customer.php';
        require_once SBHA_PLUGIN_DIR . 'includes/class-sbha-recommendations.php';
        require_once SBHA_PLUGIN_DIR . 'includes/class-sbha-analytics.php';
        require_once SBHA_PLUGIN_DIR . 'includes/class-sbha-price-optimizer.php';
        require_once SBHA_PLUGIN_DIR . 'includes/class-sbha-insights.php';
        require_once SBHA_PLUGIN_DIR . 'includes/class-sbha-reports.php';
        require_once SBHA_PLUGIN_DIR . 'includes/class-sbha-api.php';

        if (is_admin()) {
            require_once SBHA_PLUGIN_DIR . 'admin/class-sbha-admin.php';
            require_once SBHA_PLUGIN_DIR . 'admin/class-sbha-admin-dashboard.php';
            require_once SBHA_PLUGIN_DIR . 'admin/class-sbha-admin-services.php';
            require_once SBHA_PLUGIN_DIR . 'admin/class-sbha-admin-jobs.php';
            require_once SBHA_PLUGIN_DIR . 'admin/class-sbha-admin-customers.php';
            require_once SBHA_PLUGIN_DIR . 'admin/class-sbha-admin-insights.php';
            require_once SBHA_PLUGIN_DIR . 'admin/class-sbha-admin-settings.php';
            require_once SBHA_PLUGIN_DIR . 'admin/class-sbha-admin-products.php';
            require_once SBHA_PLUGIN_DIR . 'admin/class-sbha-admin-orders.php';
            require_once SBHA_PLUGIN_DIR . 'admin/class-sbha-admin-portfolio.php';
        }

        require_once SBHA_PLUGIN_DIR . 'public/class-sbha-public.php';
        require_once SBHA_PLUGIN_DIR . 'public/class-sbha-shortcodes.php';
        require_once SBHA_PLUGIN_DIR . 'public/class-sbha-ajax.php';
    }

    private function init_hooks() {
        register_activation_hook(__FILE__, array('SBHA_Installer', 'activate'));
        register_deactivation_hook(__FILE__, array('SBHA_Installer', 'deactivate'));

        add_action('plugins_loaded', array($this, 'init'));
        add_action('init', array($this, 'load_textdomain'));
        
        add_filter('upload_size_limit', array($this, 'increase_upload_limit'));
    }

    public function init() {
        $this->ai_engine = new SBHA_AI_Engine();
        $this->service_catalog = new SBHA_Service_Catalog();
        $this->job_manager = new SBHA_Job_Manager();
        $this->recommendations = new SBHA_Recommendations();
        $this->analytics = new SBHA_Analytics();

        if (is_admin()) {
            $this->admin = new SBHA_Admin();
        }

        $this->public_interface = new SBHA_Public();
    }

    public function load_textdomain() {
        load_plugin_textdomain(
            'switch-business-hub',
            false,
            dirname(SBHA_PLUGIN_BASENAME) . '/languages/'
        );
    }
    
    public function increase_upload_limit() {
        return 256 * 1024 * 1024; // 256MB
    }

    public function get_ai_engine() { return $this->ai_engine; }
    public function get_service_catalog() { return $this->service_catalog; }
    public function get_job_manager() { return $this->job_manager; }
    public function get_recommendations() { return $this->recommendations; }
    public function get_analytics() { return $this->analytics; }
}

function SBHA() {
    return Switch_Business_Hub_AI::instance();
}

SBHA();
