<?php

/**
 * Plugin Name: Lightweight Contact Form
 * Plugin URI: https://example.com/lightweight-contact-form
 * Description: Lightweight contact form plugin.
 */

if (!defined('ABSPATH')) exit;

// Load all classes
require_once plugin_dir_path(__FILE__) . 'includes/class-lcf-admin.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-lcf-db.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-lcf-validation.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-lcf-mailer.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-lcf-ajax.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-lcf-enqueue.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-lcf-shortcode.php';

// Init everything
function lcf_init()
{
    new LCF_DB();
    register_activation_hook(__FILE__, ['LCF_DB', 'create_table']);
    new LCF_Validation();
    new LCF_Mailer();
    new LCF_AJAX();
    new LCF_Enqueue();
    new LCF_Shortcode();
    new LCF_Admin();
}
add_action('plugins_loaded', 'lcf_init');
