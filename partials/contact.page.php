<?php include plugin_dir_path(__FILE__) . 'contact-messages-search.php'; ?>
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
            <?php include plugin_dir_path(__FILE__) . 'contact-table.php'; ?>

        <?php endif; ?>
</form>