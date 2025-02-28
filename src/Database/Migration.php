<?php

namespace LinkGallery\Database;

class Migration
{
    public static function createLinkTable()
    {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'link_gallery';

        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            url varchar(255) NOT NULL,
            description text,
            image varchar(255),
            target varchar(20) DEFAULT '_blank',
            status varchar(20) DEFAULT 'active',
            sort_order int DEFAULT 0,
            created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) {$charset_collate};";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public static function dropLinkTable()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'link_gallery';
        $wpdb->query("DROP TABLE IF EXISTS {$table_name}");
    }
}
