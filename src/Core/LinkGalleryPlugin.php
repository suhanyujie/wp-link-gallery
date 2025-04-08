<?php

namespace LinkGallery\Core;

class LinkGalleryPlugin {
    private static $instance = null;

    public static function getInstance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {}

    public function init() {
        $this->checkDependencies();
        $this->initComponents();
        $this->registerHooks();
    }

    private function checkDependencies() {
        if (!is_plugin_active('contact-form-cfdb7/contact-form-cfdb-7.php')) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>';
                echo '友情链接插件需要安装并激活 "Contact Form CFDB7" 插件才能正常工作。';
                echo '</p></div>';
            });
        }
    }

    private function initComponents() {
        // 自己定制的后台管理项
        new \LinkGallery\Controllers\Admin\CustomFormsController();
        // friend link 的 gutenberg 编辑器组件
        new \LinkGallery\Controllers\Frontend\LinkWidgetController();
        // 注册 contact form 7 的钩子接口
        new \LinkGallery\Services\ContactFormService();
        // some block
        new \LinkGallery\Controllers\GutenbergBlock\NormalPdfInsertController();
    }

    private function registerHooks() {
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminScripts']);
        register_activation_hook(LINK_GALLERY_PLUGIN_FILE, [$this, 'activate']);
        register_deactivation_hook(LINK_GALLERY_PLUGIN_FILE, [$this, 'deactivate']);
    }

    public function enqueueAdminScripts() {
        if ($this->isPluginPage()) {
            wp_enqueue_script('jquery');
        }
    }

    private function isPluginPage() {
        global $pagenow;
        return is_admin() && ($pagenow === 'admin.php' && isset($_GET['page']) && strpos($_GET['page'], 'link-gallery') === 0);
    }

    public function activate() {
        // 检查版本并执行升级
        $this->maybeUpgrade();
        // 初始化数据库
        \LinkGallery\Database\Migration::createLinkTable();
    }

    public function deactivate() {
        // 停用时的处理代码
    }

    private function maybeUpgrade() {
        $installed_version = get_option('link_gallery_version');
        if ($installed_version !== LINK_GALLERY_VERSION) {
            // 在这里添加版本升级逻辑
            update_option('link_gallery_version', LINK_GALLERY_VERSION);
        }
    }
}
