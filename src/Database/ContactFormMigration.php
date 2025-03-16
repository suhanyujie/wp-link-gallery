<?php

namespace LinkGallery\Database;

class ContactFormMigration
{
    public static function createContactFormTable()
    {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'lg_contact_forms';

        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            form_id bigint(20) UNSIGNED NOT NULL,
            meta_key varchar(255) NOT NULL,
            content longtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
            status varchar(20) DEFAULT 'pending',
            created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY form_id_idx (form_id),
            KEY longtext_prefix_idx (content(255))
        ) {$charset_collate};";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public static function dropContactFormTable()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'lg_contact_forms';
        $wpdb->query("DROP TABLE IF EXISTS {$table_name}");
    }
}
