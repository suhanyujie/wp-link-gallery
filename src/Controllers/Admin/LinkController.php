<?php

namespace LinkGallery\Controllers\Admin;

use LinkGallery\Models\Link;

class LinkController
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'addMenuPage']);
        add_action('admin_post_link_gallery_create', [$this, 'create']);
        add_action('admin_post_link_gallery_update', [$this, 'update']);
        add_action('admin_post_link_gallery_delete', [$this, 'delete']);
    }

    public function addMenuPage()
    {
        add_menu_page(
            'リンク管理',
            'リンク',
            'manage_options',
            'link-gallery',
            [$this, 'index'],
            'dashicons-admin-links',
            30
        );
    }

    public function index()
    {
        $links = Link::orderBy('sort_order', 'asc')->get();
        view('admin.links.index', compact('links'));
    }

    public function showCreateForm()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        view('admin.links.create');
    }

    public function create()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        check_admin_referer('link_gallery_create');

        $data = $this->validateData();
        Link::create($data);

        wp_redirect(admin_url('admin.php?page=link-gallery&message=created'));
        exit;
    }

    public function showEditForm()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        $id = $_GET['id'] ?? 0;
        $link = Link::find($id);
        if (!$link) {
            wp_die('Link not found');
        }

        view('admin.links.edit', compact('link'));
    }

    public function update()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        check_admin_referer('link_gallery_update');

        $id = $_POST['id'] ?? 0;
        $link = Link::find($id);
        if (!$link) {
            wp_die('Link not found');
        }

        $data = $this->validateData();
        $link->update($data);

        wp_redirect(admin_url('admin.php?page=link-gallery&message=updated'));
        exit;
    }

    public function delete()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        check_admin_referer('link_gallery_delete');

        $id = $_POST['id'] ?? 0;
        $link = Link::find($id);
        if ($link) {
            $link->delete();
        }

        wp_redirect(admin_url('admin.php?page=link-gallery&message=deleted'));
        exit;
    }

    private function validateData()
    {
        return [
            'name' => sanitize_text_field($_POST['name'] ?? ''),
            'url' => esc_url_raw($_POST['url'] ?? ''),
            'description' => sanitize_textarea_field($_POST['description'] ?? ''),
            'image' => esc_url_raw($_POST['image'] ?? ''),
            'target' => sanitize_text_field($_POST['target'] ?? '_blank'),
            'status' => sanitize_text_field($_POST['status'] ?? 'active'),
            'sort_order' => intval($_POST['sort_order'] ?? 0)
        ];
    }
}
