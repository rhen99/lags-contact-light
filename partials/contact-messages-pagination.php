<?php

/**
 * Display the pagination links for the contact messages table.
 *
 * @var int $total_pages The total number of pages for pagination.
 * @var int $current_page The current page number for pagination.
 */
?>
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