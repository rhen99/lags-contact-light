<?php

class LAGS_Admin
{

    public function __construct()
    {
        add_action('admin_menu', [$this, 'register_menu']);
        add_action('admin_init', [$this, 'handle_bulk_actions']);
        add_action('admin_init', [$this, 'handle_delete_action']);
        add_action('admin_init', [$this, 'handle_mark_read']);
        add_action('admin_init', [$this, 'handle_mark_unread']);
    }

    public function register_menu()
    {
        $unread_count = LAGS_DB::get_unread_count();
        $menu_title = 'Contact Form';
        if ($unread_count > 0) {
            $menu_title .= ' <span class="awaiting-mod">' . $unread_count . '</span>';
        }

        add_menu_page(
            'Contact Messages',
            $menu_title,
            'manage_options',
            'lags-messages',
            [$this, 'render_page'],
            'dashicons-email',
            25
        );
    }

    public function render_page()
    {
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        $status = $_GET['status'] ?? '';
        $current_page = max(1, intval($_GET['paged'] ?? 1));

        $messages_data = LAGS_DB::get_messages($search, $status, $current_page);

        $messages = $messages_data['messages'];
        $total_pages = $messages_data['total_pages'];

        include plugin_dir_path(__FILE__) . '../partials/contact.page.php';
    }
    public function handle_bulk_actions()
    {
        if (!isset($_POST['bulk_action']) || !isset($_POST['ids'])) {
            return;
        }

        if (!wp_verify_nonce($_POST['lags_bulk_nonce'], 'lags_bulk_action')) {
            return;
        };

        $action = sanitize_text_field($_POST['bulk_action']);
        $ids = array_map('intval', $_POST['ids']);

        if ($action === 'delete' && !empty($ids)) {
            LAGS_DB::bulk_delete($ids);
        }
    }
    public function handle_delete_action()
    {
        if (!isset($_GET['action']) || $_GET['action'] !== 'delete' || !isset($_GET['id'])) {
            return;
        }

        $id = intval($_GET['id']);
        $nonce_action = 'lags_delete_message_' . $id;

        if (!wp_verify_nonce($_GET['_wpnonce'], $nonce_action)) {
            return;
        }

        LAGS_DB::delete($id);
    }

    public function handle_mark_read()
    {
        if (!isset($_GET['action']) || $_GET['action'] !== 'mark_read') {
            return;
        }

        if (!isset($_GET['id'])) {
            return;
        }

        $id = intval($_GET['id']);

        if (!wp_verify_nonce($_GET['_wpnonce'], 'lags_mark_read_' . $id)) {
            wp_die('Security check failed');
        }

        LAGS_DB::mark_as_read($id);

        wp_redirect(admin_url('admin.php?page=lags-messages'));
        exit;
    }
    public function handle_mark_unread()
    {
        if (!isset($_GET['action']) || $_GET['action'] !== 'mark_unread') {
            return;
        }

        if (!isset($_GET['id'])) {
            return;
        }

        $id = intval($_GET['id']);

        if (!wp_verify_nonce($_GET['_wpnonce'], 'lags_mark_unread_' . $id)) {
            wp_die('Security check failed');
        }

        LAGS_DB::mark_as_unread($id);

        wp_redirect(admin_url('admin.php?page=lags-messages'));
        exit;
    }
    public function handle_search()
    {
        if (!isset($_GET['s'])) {
            return;
        }

        $search_term = sanitize_text_field($_GET['s']);
        $messages_data = LAGS_DB::get_messages($search_term);
        $messages = $messages_data['messages'];
        $total_pages = $messages_data['total_pages'];
        $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;

        ob_start();
        include plugin_dir_path(__FILE__) . 'partials/admin-messages-table.php';
        echo ob_get_clean();
    }
}
