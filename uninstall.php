<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// 删除数据表
require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';
\LinkGallery\Database\Migration::dropLinkTable();