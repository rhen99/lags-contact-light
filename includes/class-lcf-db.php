<?php

class LCF_DB
{

    public function __construct()
    {
        register_activation_hook(__FILE__, [$this, 'create_table']);
    }

    public static function create_table()
    {
        global $wpdb;

        $table = $wpdb->prefix . 'lcf_messages';
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100),
            email VARCHAR(100),
            message TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) $charset;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    public static function insert($data)
    {
        global $wpdb;

        $result = $wpdb->insert(
            $wpdb->prefix . 'lcf_messages',
            $data
        );

        return $result ? $wpdb->insert_id : false;
    }
    public static function get_messages()
    {
        
        global $wpdb;

        $table = $wpdb->prefix . 'lcf_messages';
        $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $messages_per_page = 10;
        $total_messages = $wpdb->get_var("SELECT COUNT(*) FROM $table");
        $total_pages = ceil($total_messages / $messages_per_page);

        $offset = ($current_page - 1) * $messages_per_page;

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, name, email, message, created_at FROM $table ORDER BY id DESC LIMIT %d OFFSET %d",
                $messages_per_page,
                $offset
            )
        );
        return ['messages' => $results, 'total_pages' => $total_pages];
    }
    public static function bulk_delete($ids)
    {
        global $wpdb;

        $table = $wpdb->prefix . 'lcf_messages';
        $ids_placeholder = implode(',', array_fill(0, count($ids), '%d'));

        return $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM $table WHERE id IN ($ids_placeholder)",
                ...$ids
            )
        );
    }
    public static function delete($id)
    {
        global $wpdb;

        $table = $wpdb->prefix . 'lcf_messages';

        return $wpdb->delete(
            $table,
            ['id' => $id],
            ['%d']
        );
    }
}
