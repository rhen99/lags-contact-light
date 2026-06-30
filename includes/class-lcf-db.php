<?php

class LCF_DB {

    public function __construct() {
        register_activation_hook(__FILE__, [$this, 'create_table']);
    }

    public static function create_table() {
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

    public static function insert($data) {
        global $wpdb;

        $result = $wpdb->insert(
            $wpdb->prefix . 'lcf_messages',
            $data
        );

        return $result ? $wpdb->insert_id : false;
    }
}