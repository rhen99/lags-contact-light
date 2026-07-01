<?php

class LCF_Admin
{

    public function __construct()
    {
        add_action('admin_menu', [$this, 'register_menu']);
        add_action('admin_init', [$this, 'handle_bulk_actions']);
        add_action('admin_init', [$this, 'handle_delete_action']);
    }

    public function register_menu()
    {
        add_menu_page(
            'Contact Messages',
            'Contact Form',
            'manage_options',
            'lcf-messages',
            [$this, 'render_page'],
            'dashicons-email',
            25
        );
    }

    public function render_page()
    {
        $messages_data = LCF_DB::get_messages();
        $messages = $messages_data['messages'];
        $total_pages = $messages_data['total_pages'];
        $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        ob_start();
?>
        <form method="post" id="lcf-messages-form">
            <?php wp_nonce_field('lcf_bulk_action', 'lcf_bulk_nonce'); ?>

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
                                <th style="width: 10%;">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($messages as $msg) : ?>
                                <tr>
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
                                        <?php
                                        $delete_url = wp_nonce_url(
                                            admin_url('admin.php?page=lcf-messages&action=delete&id=' . $msg->id),
                                            'lcf_delete_message_' . $msg->id
                                        );
                                        ?>
                                        <a href="<?php echo esc_url($delete_url); ?>"
                                            onclick="return confirm('Are you sure you want to delete this message?');">
                                            Delete
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
                                    'page' => sanitize_text_field($_GET['page'] ?? 'lcf-messages'),
                                    'paged' => '%#%'
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

        if (!wp_verify_nonce($_POST['lcf_bulk_nonce'], 'lcf_bulk_action')) {
            return;
        };

        $action = sanitize_text_field($_POST['bulk_action']);
        $ids = array_map('intval', $_POST['ids']);

        if ($action === 'delete' && !empty($ids)) {
            LCF_DB::bulk_delete($ids);
        }
    }
    public function handle_delete_action()
    {
        if (!isset($_GET['action']) || $_GET['action'] !== 'delete' || !isset($_GET['id'])) {
            return;
        }

        $id = intval($_GET['id']);
        $nonce_action = 'lcf_delete_message_' . $id;

        if (!wp_verify_nonce($_GET['_wpnonce'], $nonce_action)) {
            return;
        }

        LCF_DB::delete($id);
    }
}
