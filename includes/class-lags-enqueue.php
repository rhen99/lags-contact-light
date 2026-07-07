<?php

class LAGS_Enqueue
{

    public function __construct()
    {
        add_action('wp_enqueue_scripts', [$this, 'load']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }

    public function load()
    {
        wp_enqueue_script(
            'lags-frontend-js',
            plugin_dir_url(__FILE__) . '../assets/js/frontend.js',
            [],
            null,
            true
        );

        wp_localize_script('lags-frontend-js', 'lags_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('lags_nonce')
        ]);

        wp_enqueue_style(
            'lags-frontend-css',
            plugin_dir_url(__FILE__) . '../assets/css/frontend.css'
        );
    }
    public function enqueue_admin_assets($hook)
    {
        if ($hook !== 'toplevel_page_lags-messages') {
            return;
        }

        wp_enqueue_script(
            'lags-admin-js',
            plugin_dir_url(__FILE__) . '../assets/js/admin.js',
            [],
            '1.0',
            true
        );
        wp_enqueue_style(
            'lags-admin-css',
            plugin_dir_url(__FILE__) . '../assets/css/admin.css'
        );
        wp_localize_script('lags-admin-js', 'lags_admin_ajax', [
            'ajax_url' => admin_url('admin-ajax.php')
        ]);
    }
}
