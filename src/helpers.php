<?php

if (!function_exists('view')) {
    /**
     * 渲染视图
     *
     * @param string $view 视图名称（使用点号分隔，例如：'admin.links.index'）
     * @param array $data 传递给视图的数据
     * @return void
     */
    function view($view, $data = [])
    {
        // 将点号分隔的视图路径转换为目录路径
        $view = str_replace('.', '/', $view);
        $view_path = LINK_GALLERY_PLUGIN_DIR . 'resources/views/' . $view . '.blade.php';

        // 检查视图文件是否存在
        if (!file_exists($view_path)) {
            wp_die(sprintf('视图文件不存在：%s', $view_path));
        }

        // 提取变量，使其在视图中可用
        if (!empty($data)) {
            extract($data);
        }

        // 开启输出缓冲
        ob_start();

        // 包含视图文件
        include $view_path;

        // 获取并清理输出缓冲
        echo ob_get_clean();
    }
}