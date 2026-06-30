<?php

class LCF_Enqueue {

    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'load']);
    }

    public function load() {
        wp_enqueue_script(
            'lcf-script',
            plugin_dir_url(__FILE__) . '../assets/js/script.js',
            [],
            null,
            true
        );

        wp_localize_script('lcf-script', 'lcf_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('lcf_nonce')
        ]);

        wp_enqueue_style(
            'lcf-style',
            plugin_dir_url(__FILE__) . '../assets/css/style.css'
        );
    }
}