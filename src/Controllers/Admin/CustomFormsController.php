<?php

namespace LinkGallery\Controllers\Admin;

class CustomFormsController
{
    private $learnerFormController;
    private $volunteerFormController;
    private $linkController;

    public function __construct()
    {
        $this->learnerFormController = new LearnerFormController();
        $this->volunteerFormController = new VolunteerFormController();
        $this->linkController = new LinkController();
        add_action('admin_menu', [$this, 'addMenuPage']);
    }

    public function getLearnerFormController() {
        return $this->learningFormController;
    }

    public function getVolunteerFormController() {
        return $this->volunteerFormController;
    }

    public function getLinkController() {
        return $this->linkController;
    }

    public function addMenuPage()
    {
        add_menu_page(
            'カスタムフォーム管理',
            'カスタムフォーム',
            'manage_options',
            'custom-forms',
            [$this, 'index'],
            'dashicons-feedback',
            30
        );

        // 添加リンク子菜单
        add_submenu_page(
            'custom-forms',
            'リンク管理',
            'リンク',
            'manage_options',
            'link-gallery',
            [$this->linkController, 'index'],
            10
        );

        // 添加学習者申請子菜单
        add_submenu_page(
            'custom-forms',
            '学習者申請管理',
            '学習者申請',
            'manage_options',
            'learner-form',
            [$this->learnerFormController, 'index'],
            20
        );
        // ボランティア申請管理子菜单
        add_submenu_page(
            'custom-forms',
            'ボランティア申請管理',
            'ボランティア申請',
            'manage_options',
            'volunteer-form',
            [$this->volunteerFormController, 'index'],
            21
        );
    }

    public function index()
    {
        view('admin.custom-forms.index');
    }
}
