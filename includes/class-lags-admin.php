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
        add_action('wp_ajax_lags_toggle_read', [$this, 'ajax_toggle_read']);
        add_action('wp_ajax_lags_get_unread_count', [$this, 'ajax_get_unread_count']);
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
    public function ajax_toggle_read()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $id = intval($_POST['id']);
        $action = sanitize_text_field($_POST['toggle_action']);
        $nonce = $_POST['nonce'];

        if (!wp_verify_nonce($nonce, 'lags_' . $action . '_' . $id)) {
            wp_send_json_error(['message' => 'Invalid nonce']);
        }

        if ($action === 'mark_read') {
            LAGS_DB::mark_as_read($id);
            $new_action = 'mark_unread';
            $new_label = 'Mark as Unread';
        } else {
            LAGS_DB::mark_as_unread($id);
            $new_action = 'mark_read';
            $new_label = 'Mark as Read';
        }

        wp_send_json_success([
            'new_action' => $new_action,
            'new_label' => $new_label,
            'is_read' => $action === 'mark_read' ? 1 : 0,
            'unread_count' => LAGS_DB::get_unread_count(),
            'new_nonce' => wp_create_nonce('lags_' . $new_action . '_' . $id)
        ]);
    }


    public function ajax_get_unread_count()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error();
        }

        $count = LAGS_DB::get_unread_count();

        wp_send_json_success([
            'count' => intval($count)
        ]);
    }
}
