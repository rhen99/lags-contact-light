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
        ob_start();
?>
        <form method="GET" class="lags-filters">

            <input type="hidden" name="page" value="lags-messages">

            <div class="tablenav top">

                <!-- LEFT SIDE (filters) -->
                <div class="alignleft actions">

                    <select name="status" onchange="this.form.submit()">
                        <option value="">All</option>
                        <option value="unread" <?php selected($_GET['status'] ?? '', 'unread'); ?>>
                            Unread
                        </option>
                        <option value="read" <?php selected($_GET['status'] ?? '', 'read'); ?>>
                            Read
                        </option>
                    </select>

                </div>

                <!-- RIGHT SIDE (search) -->
                <p class="search-box">
                    <label class="screen-reader-text" for="lags-search-input">
                        Search Messages:
                    </label>

                    <input
                        type="search"
                        id="lags-search-input"
                        name="s"
                        value="<?php echo esc_attr($_GET['s'] ?? ''); ?>"
                        placeholder="Search messages...">

                    <input
                        type="submit"
                        class="button"
                        value="Search">
                </p>

                <br class="clear">

            </div>
        </form>
        <form method="post" id="lags-messages-form">
            <?php wp_nonce_field('lags_bulk_action', 'lags_bulk_nonce'); ?>
            <input type="hidden" name="page" value="lags-messages">
            <input type="hidden" name="s" value="<?php echo esc_attr($_GET['s'] ?? ''); ?>">
            <input type="hidden" name="status" value="<?php echo esc_attr($_GET['status'] ?? ''); ?>">
            <input type="hidden" name="paged" value="<?php echo esc_attr($_GET['paged'] ?? 1); ?>">
            <div class="wrap">
                <h1 class="wp-heading-inline">Contact Messages</h1>
                <?php if (empty($messages)) : ?>
                    <p>No messages found.</p>
                <?php else : ?>

                    <div class="tabnav top">
                        <div class="alignleft actions bulkactions">
                            <select name="bulk_action">
                                <option value="">Bulk Actions</option>
                                <option value="delete">Delete</option>
                            </select>
                            <button type="submit" class="button">Apply</button>
                        </div>

                    </div>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="select-all"></th>
                                <th style="width: 5%;">ID</th>
                                <th style="width: 15%;">Name</th>
                                <th style="width: 20%;">Email</th>
                                <th>Message</th>
                                <th style="width: 20%;">Date</th>
                                <th style="width: 10%;">Status</th>
                                <th style="width: 10%;">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($messages as $msg) : ?>
                                <?php
                                $label = $msg->is_read ? 'Mark as Unread' : 'Mark as Read';
                                $action = $msg->is_read ? 'mark_unread' : 'mark_read';
                                ?>
                                <tr class="<?php echo !$msg->is_read ? "lags-unread" : ""; ?>">
                                    <td>
                                        <input type="checkbox" name="ids[]" value="<?php echo esc_attr($msg->id); ?>">
                                    </td>
                                    <td><?php echo esc_html($msg->id); ?></td>
                                    <td><?php echo esc_html($msg->name); ?></td>
                                    <td>
                                        <a href="mailto:<?php echo esc_attr($msg->email); ?>">
                                            <?php echo esc_html($msg->email); ?>
                                        </a>
                                    </td>
                                    <td><?php echo esc_html($msg->message); ?></td>
                                    <td>
                                        <?php echo esc_html(
                                            date('M d, Y h:i A', strtotime($msg->created_at))
                                        ); ?>
                                    </td>
                                    <td>
                                        <?php if ((int) $msg->is_read === 1): ?>
                                            <span style="color: green;">Read</span>
                                        <?php else: ?>
                                            <strong style="color: #d63638;">Unread</strong>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $delete_url = wp_nonce_url(
                                            admin_url('admin.php?page=lags-messages&action=delete&id=' . $msg->id),
                                            'lags_delete_message_' . $msg->id
                                        );
                                        ?>
                                        <a href="<?php echo esc_url($delete_url); ?>"
                                            onclick="return confirm('Are you sure you want to delete this message?');">
                                            Delete
                                        </a> |
                                        <a href="<?php echo wp_nonce_url(
                                                        admin_url('admin.php?page=lags-messages&action=' . $action . '&id=' . $msg->id),
                                                        'lags_' . $action . '_' . $msg->id
                                                    ); ?>">
                                            <?php echo esc_html($label); ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="tablenav bottom">
                        <div class="tablenav-pages">
                            <?php
                            echo paginate_links([
                                'base' => add_query_arg([
                                    'page' => sanitize_text_field($_GET['page'] ?? 'lags-messages'),
                                    'paged' => '%#%',
                                    's' => sanitize_text_field($_GET['s'] ?? ''),
                                    'status' => sanitize_text_field($_GET['status'] ?? '')
                                ], admin_url('admin.php')),
                                'format' => '',
                                'total' => $total_pages,
                                'current' => $current_page,
                                'prev_text' => __('&laquo; Previous'),
                                'next_text' => __('Next &raquo;'),
                                'type' => 'list',
                            ]);
                            ?>
                        </div>
                    <?php endif; ?>
        </form>
        </div>
<?php
        echo ob_get_clean();
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
