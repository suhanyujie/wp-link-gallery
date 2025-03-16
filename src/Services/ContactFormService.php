<?php

namespace LinkGallery\Services;

class ContactFormService
{
    public function __construct()
    {
        add_action('wpcf7_before_send_mail', [$this, 'saveFormData']);
    }

    public function saveFormData($contact_form)
    {
        try {
            $submission = \WPCF7_Submission::get_instance();
            if ($submission) {
                $posted_data = $submission->get_posted_data();

                // 记录表单提交的数据
                error_log('Contact Form Posted Data: ' . print_r($posted_data, true));

                global $wpdb;
                $table_name = $wpdb->prefix . 'lg_contact_forms';
                $content = [
                    'your-name' => $posted_data['your-name'] ?? '',
                    'your-email' => $posted_data['your-email'] ?? '',
                    'your-message' => $posted_data['your-message'] ?? '',
                ];
                $data = [
                    'form_id' => $contact_form->id(),
                    'content' => json_encode($content),
                    'status' => '0', // 0 oending；1 pass；2 not pass
                ];

                $wpdb->insert($table_name, $data);

                if ($wpdb->last_error) {
                    error_log('Contact Form Save Error: ' . $wpdb->last_error);
                }
            }
        } catch (\Exception $e) {
            error_log('Contact Form Save Exception: ' . $e->getMessage());
        }
    }
}
