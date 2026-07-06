<?php

/**
 * Display the contact messages table in the admin area.
 *
 * @var array $messages The array of contact messages to display.
 * @var int $total_pages The total number of pages for pagination.
 * @var int $current_page The current page number for pagination.
 */
?>
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
            <?php include plugin_dir_path(__FILE__) . 'contact-message-row.php'; ?>
        <?php endforeach; ?>
    </tbody>
</table>
<div class="tabnav bottom">
    <?php include plugin_dir_path(__FILE__) . 'contact-messages-pagination.php'; ?>
</div>