<?php
/**
 * 各种活动（event）对应的报名表单数据的管理
 */
namespace LinkGallery\Controllers\Admin;

use WPCF7_ContactForm;
use LinkGallery\Services\EventFormDataManageService;

class EventFormDataManageController
{
    protected $page_slug = 'event-form-data-manage';
    protected $page_title = 'イベント申込ダータ管理';
    protected $table_name = 'lg_contact_forms';

    public function __construct()
    {
        add_action('admin_post_event_form_update', [$this, 'update']);
        add_action('wp_ajax_event_form_update', [$this, 'ajaxUpdate']);
        add_action('wp_ajax_get_event_form_details', [$this, 'getFormDetails']);
        add_action('admin_post_event_form_export_csv', [$this, 'exportCsv']);
        add_action('wp_ajax_event_form_export_csv', [$this, 'exportCsv']);
        add_action('wp_ajax_get_event_form_fields', [$this, 'getFormFields']);
        add_action('wp_ajax_event_form_create', [$this, 'ajaxCreate']);
        add_action('wp_ajax_event_form_delete_bulk', [$this, 'ajaxBatchDelete']);
    }

    public function index()
    {
        // 获取Contact Form 7的所有表单
        $forms = WPCF7_ContactForm::find();
        $form_id = isset($_GET['form_id']) ? intval($_GET['form_id']) : 0;

        // 如果没有指定form_id，使用最新创建的表单ID
        if (!$form_id && !empty($forms)) {
            // Contact Form 7的表单按照ID降序排列，第一个就是最新的
            $latest_form = reset($forms);
            $form_id = $latest_form->id();
        }
        global $wpdb;
        $table_name = $wpdb->prefix . $this->table_name;
        $numPerPage = 20;
        $paginationInfo = [
            'total' => 0,
            'pageNum' => 0,
            'numPerPage' =>$numPerPage,
        ];

        // 基本查询
        $query = "SELECT * FROM {$table_name}";
        $params = [];

        if ($form_id) {
            $query .= " WHERE form_id = %d";
            $params[] = $form_id;
        }

        // 搜索条件
        $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
        if ($search) {
            $query .= $form_id ? " AND" : " WHERE";
            $query .= " form_value LIKE %s";
            $params[] = '%' . $wpdb->esc_like($search) . '%';
        }

        $query .= " ORDER BY id DESC";

        // 分页
        $current_page = isset($_GET['paged']) ? absint($_GET['paged']) : 1;
        $offset = ($current_page - 1) * $paginationInfo['numPerPage'];

        // 获取总数
        $total_query = "SELECT COUNT(*) FROM ({$wpdb->prepare($query, $params)}) AS t";
        $total_items = $wpdb->get_var($total_query);
        $paginationInfo['total'] = $total_items;

        // 添加分页限制
        $query .= " LIMIT %d OFFSET %d";
        $params[] = $paginationInfo['numPerPage'];
        $params[] = $offset;

        // 执行查询
        $entries = $wpdb->get_results($wpdb->prepare($query, $params));

        // 格式化数据
        $form_entries = [];
        foreach ($entries as $entry) {
            $form_data = json_decode($entry->content, true) ?? [];
            error_log(print_r([
                'content'=>$entry->content,
                'arr'=>$form_data,
            ], true));
            $form_entries[] = [
                'id' => $entry->id,
                'date' => $entry->created_at,
                'form_id' => $entry->form_id,
                'details' => $form_data
            ];
        }

        view('admin.event-forms.index', compact('forms', 'form_entries', 'form_id', 'paginationInfo'));
    }

    public function update()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        check_admin_referer('event_form_update');

       $id = $_POST['id'] ?? 0;
        $content = $_POST['content'] ?? [];
        $contentStr = json_encode($content);

        global $wpdb;
        $table_name = $wpdb->prefix . $this->table_name;

        $result = $wpdb->update(
            $table_name,
            ['content' => $contentStr],
            ['id' => $id],
            ['%s'],
            ['%d']
        );

        wp_redirect(admin_url('admin.php?page=' . $this->page_slug . '&message=updated'));
        exit;
    }

    public function ajaxUpdate()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        check_ajax_referer('event_form_update', 'nonce');

        $id = $_POST['id'] ?? 0;
        $content = $_POST['content'] ?? [];
        $contentStr = json_encode($content);

        global $wpdb;
        $table_name = $wpdb->prefix . $this->table_name;

        $result = $wpdb->update(
            $table_name,
            ['content' => $contentStr],
            ['id' => $id],
            ['%s'],
            ['%d']
        );
        if ($result !== false) {
            wp_send_json_success(['message' => '更新が完了しました']);
        } else {
            wp_send_json_error(['message' => '更新に失敗しました']);
        }
    }

    public function getFormDetails()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        check_ajax_referer('get_event_form_details', 'nonce');

        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if (!$id) {
            wp_send_json_error(['message' => 'Invalid ID']);
        }

        global $wpdb;
        $table_name = $wpdb->prefix . $this->table_name;

        $form = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE id = %d",
            $id
        ));

        if (!$form) {
            wp_send_json_error(['message' => 'Form not found']);
        }
        $defaultContentObj = [
            'your-name'=>'',
            'your-email'=>'',
            'your-message'=>'',
        ];
        $form_data = json_decode($form->content, true) ?? $defaultContentObj;
        // error_log(print_r([
        //     'content'=>$form->content,
        //     'arr'=>$form_data,
        // ], true));

        wp_send_json_success(['form_data' => $form_data]);
    }

    public function getFormFields()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        check_ajax_referer('event_form_create', 'nonce');

        $form_id = isset($_POST['form_id']) ? intval($_POST['form_id']) : 0;
        if (!$form_id) {
            wp_send_json_error(['message' => 'Invalid form ID']);
        }

        $form = WPCF7_ContactForm::get_instance($form_id);
        if (!$form) {
            wp_send_json_error(['message' => 'Form not found']);
        }

        $fields = [];
        $form_fields = $form->scan_form_tags();
        foreach ($form_fields as $field) {
            if (!empty($field['name'])) {
                $fields[] = $field['name'];
            }
        }
        $fields = array_unique($fields);

        wp_send_json_success(['fields' => $fields]);
    }

    public function ajaxCreate()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        check_ajax_referer('event_form_create', 'nonce');

        $form_id = isset($_POST['form_id']) ? intval($_POST['form_id']) : 0;
        $content = isset($_POST['content']) ? $_POST['content'] : [];

        if (!$form_id || !$content) {
            wp_send_json_error(['message' => '必須項目が不足しています']);
        }
        $contentStr = json_encode($content);

        global $wpdb;
        $table_name = $wpdb->prefix . $this->table_name;

        $result = $wpdb->insert(
            $table_name,
            [
                'form_id' => $form_id,
                'content' => $contentStr,
                'created_at' => current_time('mysql')
            ],
            ['%d', '%s', '%s']
        );

        if ($result !== false) {
            wp_send_json_success(['message' => '新規作成が完了しました']);
        } else {
            wp_send_json_error(['message' => '新規作成に失敗しました']);
        }
    }

    public function ajaxBatchDelete()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        check_ajax_referer('event_form_delete_bulk', 'nonce');

        $ids = isset($_POST['ids']) ? array_map('intval', (array)$_POST['ids']) : [];
        if (empty($ids)) {
            wp_send_json_error(['message' => '削除する項目を選択してください']);
        }

        global $wpdb;
        $table_name = $wpdb->prefix . $this->table_name;

        // 使用IN子句进行批量删除
        $placeholders = array_fill(0, count($ids), '%d');
        $query = $wpdb->prepare(
            "DELETE FROM {$table_name} WHERE id IN (" . implode(',', $placeholders) . ")",
            $ids
        );

        $result = $wpdb->query($query);

        if ($result !== false) {
            wp_send_json_success([
                'message' => '選択した項目を削除しました',
                'deleted_count' => $result
            ]);
        } else {
            wp_send_json_error(['message' => '削除に失敗しました']);
        }
    }

    public function exportCsv()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        $form_id = isset($_REQUEST['form_id']) ? intval($_REQUEST['form_id']) : 0;
        if (!$form_id) {
            wp_die('Invalid form ID');
        }

        global $wpdb;
        $table_name = $wpdb->prefix . $this->table_name;

        $entries = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE form_id = %d ORDER BY id DESC",
            $form_id
        ));

        $filename = 'event-form-data-' . date('Y-m-d') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $output = fopen('php://output', 'w');
        fputs($output, "\xEF\xBB\xBF"); // UTF-8 BOM

        // ヘッダー行
        $headers = ['ID', '提出日時'];
        if (!empty($entries)) {
            $first_entry = json_decode($entries[0]->content, true);
            $extraColArr = [];
            foreach (array_keys($first_entry) as $key) {
                $extraColArr[] = EventFormDataManageService::getColumnMapForExport($key);
            }
            $headers = array_merge($headers, $extraColArr);
        }
        fputcsv($output, $headers);

        // データ行
        foreach ($entries as $entry) {
            $form_data = json_decode($entry->content, true);
            $row = [$entry->id, $entry->created_at];
            foreach (array_keys($first_entry) as $key) {
                $row[] = $form_data[$key] ?? '';
            }
            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }
}
