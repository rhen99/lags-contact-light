<?php
require_once plugin_dir_path(__FILE__) . 'validation.php';

function lcf_ajax_handler() {
    global $wpdb;
    // nonce check
    if (!isset($_POST['lcf_nonce']) || 
        !wp_verify_nonce($_POST['lcf_nonce'], 'lcf_form_action')) {
        wp_send_json_error(['message' => 'Security check failed']);
    }
    check_ajax_referer('lcf_form_action', 'lcf_nonce');
    $name = sanitize_text_field($_POST['name']);
    $email = sanitize_email($_POST['email']);
    $message = sanitize_textarea_field($_POST['message']);

    $errors = lcf_validate_form($name, $email, $message);
    if (!empty($errors)) {
        wp_send_json_error([
            'errors' => $errors,
            'message' => 'Please fix the errors below.'
    ]);
    }
    $table_name = $wpdb->prefix . 'lcf_messages';

    $wpdb->insert($table_name, [
        'name' => $name,
        'email' => $email,
        'message' => $message,
    ]);
    if(!$wpdb->insert_id){
        wp_send_json_error(['message' => 'Failed to save message. Please try again.']);
    }

    $sent = wp_mail(
        get_option('admin_email'),
        'New Message',
        "Name: $name\nEmail: $email\n$message"
    );

    if (!$sent) {
        wp_send_json_error(['message' => 'Failed to send message. Please try again.']);
    }

    wp_send_json_success(['message' => 'Message sent!']);
}

add_action('wp_ajax_lcf_submit_form', 'lcf_ajax_handler');
add_action('wp_ajax_nopriv_lcf_submit_form', 'lcf_ajax_handler');