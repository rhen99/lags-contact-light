<?php
class LCF_Mailer {

    public static function send($name, $email, $message) {
        return wp_mail(
            get_option('admin_email'),
            'New Contact Message from ' . $name,
            $message,
            ['Reply-To: ' . $email]
        );
    }
}