<?php

class LCF_AJAX {

    public function __construct() {
        add_action('wp_ajax_lcf_submit_form', [$this, 'handle']);
        add_action('wp_ajax_nopriv_lcf_submit_form', [$this, 'handle']);
    }

    public function handle() {
        check_ajax_referer('lcf_form_action', 'lcf_nonce');

        $name = sanitize_text_field($_POST['name']);
        $email = sanitize_email($_POST['email']);
        $message = sanitize_textarea_field($_POST['message']);

        $errors = LCF_Validation::validate($name, $email, $message);

        if (!empty($errors)) {
            wp_send_json_error([
                'message' => 'Please fix the errors below.',
                'errors' => $errors
                ]);
        }

        $insert_id = LCF_DB::insert([
            'name' => $name,
            'email' => $email,
            'message' => $message,
        ]);
        if (!$insert_id) {
            wp_send_json_error(['message' => 'Failed to save message. Please try again.']);
        }
        $sent = LCF_Mailer::send($name, $email, $message);
        if (!$sent) {
            wp_send_json_error(['message' => 'Failed to send message. Please try again.']);
        }

        wp_send_json_success([
            'message' => 'Message sent successfully!'
        ]);
    }
}