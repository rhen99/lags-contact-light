<?php
/**
 * Plugin Name: Lightweight Contact Form
 * Plugin URI: https://example.com/lightweight-contact-form
 * Description: Lightweight contact form plugin.
 */

if (!defined('ABSPATH')) exit;
require_once plugin_dir_path(__FILE__) . 'includes/form-render.php';
require_once plugin_dir_path(__FILE__) . 'includes/form-handler.php';

function lcf_enqueue_styles_conditionally() {
    if (is_singular()) {
        global $post;

        if (has_shortcode($post->post_content, 'lightweight_contact_form')) {
            wp_enqueue_style(
                'lcf-style',
                plugin_dir_url(__FILE__) . 'assets/style.css',
                array(),
                '1.0.0'
            );
        }
    }
}
add_action('wp_enqueue_scripts', 'lcf_enqueue_styles_conditionally');

function lcf_enqueue_scripts() {
    wp_enqueue_script(
        'lcf-script',
        plugin_dir_url(__FILE__) . 'assets/script.js',
        [],
        null,
        true
    );

    wp_localize_script('lcf-script', 'lcf_ajax', [
        'ajax_url' => admin_url('admin-ajax.php')
    ]);
}
add_action('wp_enqueue_scripts', 'lcf_enqueue_scripts');

register_activation_hook(__FILE__, 'lcf_create_table');

function lcf_create_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'lcf_messages';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        message TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}