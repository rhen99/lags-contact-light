<?php

class LAGS_DB
{

    public function __construct()
    {
        register_activation_hook(__FILE__, [$this, 'create_table']);
    }

    public static function create_table()
    {
        global $wpdb;

        $table = $wpdb->prefix . 'lags_messages';
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100),
            email VARCHAR(100),
            message TEXT,
            is_read TINYINT(1) DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) $charset;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    public static function insert($data)
    {
        global $wpdb;

        $result = $wpdb->insert(
            $wpdb->prefix . 'lags_messages',
            $data
        );

        return $result ? $wpdb->insert_id : false;
    }
    public static function get_messages($search = '', $status = '', $current_page = 1)
    {
        global $wpdb;

        $table = $wpdb->prefix . 'lags_messages';

        $per_page = 10;
        $offset = ($current_page - 1) * $per_page;

        $where = [];
        $params = [];

        // 🔍 Search
        if (!empty($search)) {
            $like = '%' . $wpdb->esc_like($search) . '%';
            $where[] = "(name LIKE %s OR email LIKE %s OR message LIKE %s)";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        // 🧪 Filter
        if ($status === 'read') {
            $where[] = "is_read = 1";
        } elseif ($status === 'unread') {
            $where[] = "is_read = 0";
        }

        // Combine WHERE
        $where_sql = '';
        if (!empty($where)) {
            $where_sql = 'WHERE ' . implode(' AND ', $where);
        }

        // 📊 Count
        $count_sql = "SELECT COUNT(*) FROM $table $where_sql";
        $count_query = $wpdb->prepare($count_sql, ...$params);
        $total_items = $wpdb->get_var($count_query);

        $total_pages = ceil($total_items / $per_page);

        // 📄 Results
        $query_sql = "SELECT id, name, email, message, created_at, is_read
                  FROM $table
                  $where_sql
                  ORDER BY id DESC
                  LIMIT %d OFFSET %d";

        $query = $wpdb->prepare(
            $query_sql,
            ...array_merge($params, [$per_page, $offset])
        );

        $results = $wpdb->get_results($query);

        return [
            'messages' => $results,
            'total_pages' => $total_pages
        ];
    }
    public static function delete($id)
    {
        global $wpdb;

        $table = $wpdb->prefix . 'lags_messages';

        return $wpdb->delete(
            $table,
            ['id' => $id],
            ['%d']
        );
    }
    public static function bulk_delete($ids)
    {
        global $wpdb;

        $table = $wpdb->prefix . 'lags_messages';
        $ids_placeholder = implode(',', array_fill(0, count($ids), '%d'));

        return $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM $table WHERE id IN ($ids_placeholder)",
                ...$ids
            )
        );
    }
    public static function mark_as_read($id)
    {
        global $wpdb;

        $table = $wpdb->prefix . 'lags_messages';

        return $wpdb->update(
            $table,
            ['is_read' => 1],
            ['id' => $id],
            ['%d'],
            ['%d']
        );
    }
    public static function mark_as_unread($id)
    {
        global $wpdb;

        $table = $wpdb->prefix . 'lags_messages';

        return $wpdb->update(
            $table,
            ['is_read' => 0],
            ['id' => $id],
            ['%d'],
            ['%d']
        );
    }
    public static function get_unread_count()
    {
        global $wpdb;

        $table = $wpdb->prefix . 'lags_messages';

        return $wpdb->get_var(
            "SELECT COUNT(*) FROM $table WHERE is_read = 0"
        );
    }
    public static function search_messages($search_term, $current_page = 1)
    {
        global $wpdb;

        $table = $wpdb->prefix . 'lags_messages';
        $messages_per_page = 10;
        $offset = ($current_page - 1) * $messages_per_page;

        $like = '%' . $wpdb->esc_like($search_term) . '%';

        $where = "WHERE name LIKE %s OR email LIKE %s OR message LIKE %s";

        // Total
        $total_messages = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $table $where",
                $like,
                $like,
                $like
            )
        );

        $total_pages = ceil($total_messages / $messages_per_page);

        // Results
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, name, email, message, created_at, is_read 
             FROM $table 
             $where 
             ORDER BY id DESC 
             LIMIT %d OFFSET %d",
                $like,
                $like,
                $like,
                $messages_per_page,
                $offset
            )
        );

        return [
            'messages' => $results,
            'total_pages' => $total_pages
        ];
    }
}
