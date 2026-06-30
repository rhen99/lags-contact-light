<?php

class LCF_Validation {

    public static function validate($name, $email, $message) {
        $errors = [];
    if (empty($name)) {
        $errors['name'] = 'Name is required.';
    }
    if (empty($email)) {
        $errors['email'] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email address.';
    }
    if (empty($message)) {
        $errors['message'] = 'Message is required.';
    }

    return $errors;
    }
}