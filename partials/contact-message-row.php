<?php

/**
 * Display a single contact message row in the admin area.
 *
 * @var object $msg The contact message object to display.
 * @var string $label The label for the read/unread action link.
 * @var string $action The action for the read/unread action link.
 */
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
        </a> | <a href="#"
            class="view-message"
            data-id="<?php echo $msg->id; ?>"
            data-name="<?php echo esc_attr($msg->name); ?>"
            data-email="<?php echo esc_attr($msg->email); ?>"
            data-message="<?php echo esc_attr($msg->message); ?>"
            data-is-read="<?php echo $msg->is_read; ?>"
            data-nonce="<?php echo wp_create_nonce('lags_mark_read_' . $msg->id); ?>">
            View
        </a> |
        <a href="<?php echo wp_nonce_url(
                        admin_url('admin.php?page=lags-messages&action=' . $action . '&id=' . $msg->id),
                        'lags_' . $action . '_' . $msg->id
                    ); ?>"
            class="toggle-read"
            data-id="<?php echo $msg->id; ?>"
            data-action="<?php echo $action; ?>"
            data-nonce="<?php echo wp_create_nonce('lags_' . $action . '_' . $msg->id); ?>">
            <?php echo esc_html($label); ?>
        </a>
    </td>
</tr>
<div id="message-modal" style="display:none;">
    <div class="modal-content">
        <h2 id="modal-name"></h2>
        <p><strong>Email:</strong> <span id="modal-email"></span></p>
        <p id="modal-message"></p>

        <button id="close-modal" class="button">Close</button>
    </div>
</div>