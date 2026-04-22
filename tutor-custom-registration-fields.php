<?php
/**
 * Plugin Name: Tutor LMS Custom Registration Fields
 * Description: Add custom registration fields to Tutor LMS student registration form
 * Version: 1.0.0
 * Author: 
 * License: GPLv2 or later
 * Text Domain: tutor-custom-registration-fields
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('TCF_VERSION', '1.0.0');
define('TCF_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('TCF_PLUGIN_URL', plugin_dir_url(__FILE__));
define('TCF_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('TCF_OPTION_KEY', 'tutor_custom_fields_settings');

/**
 * Plugin activation callback
 */
function tcf_activate() {
    // Set default options
    $defaults = array(
        'fields' => array(),
        'version' => TCF_VERSION
    );
    
    if (get_option(TCF_OPTION_KEY) === false) {
        add_option(TCF_OPTION_KEY, $defaults, '', 'no');
    }
}

/**
 * Plugin deactivation callback
 */
function tcf_deactivate() {
    // Cleanup if needed
}

/**
 * Load plugin text domain for internationalization
 */
function tcf_load_textdomain() {
    load_plugin_textdomain(
        'tutor-custom-registration-fields',
        false,
        dirname(TCF_PLUGIN_BASENAME) . '/languages'
    );
}

// Initialize plugin
register_activation_hook(__FILE__, 'tcf_activate');
register_deactivation_hook(__FILE__, 'tcf_deactivate');
add_action('plugins_loaded', 'tcf_load_textdomain');

// Include required files
require_once TCF_PLUGIN_DIR . 'includes/functions.php';
require_once TCF_PLUGIN_DIR . 'includes/class-tcf-field.php';
require_once TCF_PLUGIN_DIR . 'admin/admin-page.php';
require_once TCF_PLUGIN_DIR . 'frontend/frontend-hooks.php';