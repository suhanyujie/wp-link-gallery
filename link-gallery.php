<?php
/**
 * Plugin Name: Link Gallery
 * Plugin URI: https://github.com/suhanyujie/wp-link-gallery
 * Description: 一个现代化的友情链接管理插件，使用Blade模板引擎和Illuminate ORM
 * Version: 0.1.5
 * Author: suhanyujie
 * Author URI: https://github.com/suhanyujie
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: https://github.com/suhanyujie/wp-link-gallery
 */

if (!defined('ABSPATH')) {
    exit;
}

// 定义插件常量
define('LINK_GALLERY_VERSION', '0.1.5');
define('LINK_GALLERY_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('LINK_GALLERY_PLUGIN_URL', plugin_dir_url(__FILE__));

// 自动加载
require_once LINK_GALLERY_PLUGIN_DIR . 'vendor/autoload.php';

// 初始化插件
function link_gallery_init() {
    // friend link 后台的菜单和页面显示
    new \LinkGallery\Controllers\Admin\LinkController();
    // 友情链接相关的 gutenberg 编辑器组件
    new \LinkGallery\Controllers\Frontend\LinkWidgetController();
}

// 激活插件时的处理
function link_gallery_activate() {
    // 初始化数据库
    \LinkGallery\Database\Migration::createLinkTable();
}
register_activation_hook(__FILE__, 'link_gallery_activate');

// 停用插件时的处理
function link_gallery_deactivate() {
    // 停用时的处理代码将在这里添加
}
register_deactivation_hook(__FILE__, 'link_gallery_deactivate');

// 初始化插件
add_action('plugins_loaded', 'link_gallery_init');
