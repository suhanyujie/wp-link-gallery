<?php
/**
 * Plugin Name: Link Gallery
 * Plugin URI: https://www.example.com/link-gallery
 * Description: 一个现代化的友情链接管理插件，使用Blade模板引擎和Illuminate ORM
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://www.example.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: link-gallery
 */

if (!defined('ABSPATH')) {
    exit;
}

// 定义插件常量
define('LINK_GALLERY_VERSION', '1.0.0');
define('LINK_GALLERY_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('LINK_GALLERY_PLUGIN_URL', plugin_dir_url(__FILE__));

// 自动加载
require_once LINK_GALLERY_PLUGIN_DIR . 'vendor/autoload.php';

// 初始化插件
function link_gallery_init() {
    // 初始化数据库
    \LinkGallery\Database\Migration::createLinkTable();

    // 添加后台菜单
    // add_action('admin_menu', function() {
    //     add_menu_page(
    //         '友情链接管理', // 页面标题
    //         '友情链接', // 菜单标题
    //         'manage_options', // 权限
    //         'link-gallery', // 菜单slug
    //         function() {
    //             // 后台页面内容将在这里添加
    //             echo '<div class="wrap"><h1>友情链接管理</h1></div>';
    //         },
    //         'dashicons-admin-links', // 菜单图标
    //         30 // 菜单位置
    //     );
    // });

    new \LinkGallery\Controllers\Admin\LinkController();
    new \LinkGallery\Controllers\Frontend\LinkWidgetController();
}

// 激活插件时的处理
register_activation_hook(__FILE__, function() {
    // 激活时的处理代码将在这里添加
});

// 停用插件时的处理
register_deactivation_hook(__FILE__, function() {
    // 停用时的处理代码将在这里添加
});

// 初始化插件
add_action('plugins_loaded', 'link_gallery_init');
