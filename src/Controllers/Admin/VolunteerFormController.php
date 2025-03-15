<?php

namespace LinkGallery\Controllers\Admin;

class VolunteerFormController
{
    protected $form_post_id = 908;
    protected $page_slug = 'volunteer-form';
    protected $page_title = 'ボランティア申請管理';

    public function __construct()
    {
        $this->emailService = new \LinkGallery\Services\VolunteerEmailService();
        add_action('admin_post_volunteer_form_update', [$this, 'update']);
        add_action('wp_ajax_volunteer_form_update', [$this, 'ajaxUpdate']);
        add_action('wp_ajax_get_volunteer_form_details', [$this, 'getFormDetails']);
        add_action('admin_post_volunteer_form_export_csv', [$this, 'exportCsv']);
        add_action('wp_ajax_volunteer_form_export_csv', [$this, 'exportCsv']);
    }

    public function index()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'db7_forms';

        // フィルター条件の取得
        $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        $date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '';
        $date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '';
        $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';

        // 基本のクエリ
        $query = "SELECT * FROM {$table_name} WHERE form_post_id = %d";
        $params = [$this->form_post_id];

        // フィルター条件の追加
        if ($date_from) {
            $query .= " AND form_date >= %s";
            $params[] = $date_from . ' 00:00:00';
        }
        if ($date_to) {
            $query .= " AND form_date <= %s";
            $params[] = $date_to . ' 23:59:59';
        }
        if ($search) {
            $query .= " AND form_value LIKE %s";
            $params[] = '%' . $wpdb->esc_like($search) . '%';
        }
        $query .= " ORDER BY form_date DESC";

        // ページネーション
        $per_page = 20;
        $current_page = isset($_GET['paged']) ? absint($_GET['paged']) : 1;
        $offset = ($current_page - 1) * $per_page;

        // 総件数を取得
        $total_query = "SELECT COUNT(*) FROM ({$wpdb->prepare($query, $params)}) AS t";
        $total_items = $wpdb->get_var($total_query);

        // ページネーション用にクエリを修正
        $query .= " LIMIT %d OFFSET %d";
        $params[] = $per_page;
        $params[] = $offset;

        // クエリの実行
        $forms = $wpdb->get_results($wpdb->prepare($query, $params));

        // データの整形
        $applications = [];
        foreach ($forms as $form) {
            $form_data = unserialize($form->form_value);
            $itemData = [
                'id' => $form->form_id,
                'date' => $form->form_date,
                'name' => $form_data['your-name'] ?? '',
                'email' => $form_data['your-email'] ?? '',
                'status' => $form_data['status'] ?? '未審査',
                'details' => $form_data
            ];
            $itemData['status_code'] = $itemData['status'];
            if ($itemData['status'] == '1') {
                $itemData['status'] = '審査通過';
            } elseif ($itemData['status'] == '2') {
                $itemData['status'] = '審査不通過';
            } else {
                $itemData['status'] = '未審査';
            }

            // ステータスフィルター
            if ($status_filter && $status_filter !== $itemData['status']) {
                continue;
            }

            $applications[] = $itemData;
        }

        view('admin.volunteer-forms.index', compact('applications'));
    }

    public function update()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        check_admin_referer('volunteer_form_update');

        $id = $_POST['id'] ?? 0;
        $status = sanitize_text_field($_POST['status'] ?? '');

        $result = $this->updateStatus($id, $status);

        wp_redirect(admin_url('admin.php?page=' . $this->page_slug . '&message=updated'));
        exit;
    }

    public function ajaxUpdate()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        check_ajax_referer('volunteer_form_update', 'nonce');

        $id = $_POST['id'] ?? 0;
        $status = sanitize_text_field($_POST['status'] ?? '');

        $result = $this->updateStatus($id, $status);

        if ($result) {
            wp_send_json_success(['message' => '更新成功']);
        } else {
            wp_send_json_error(['message' => '更新失败']);
        }
    }

    public function getFormDetails()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        check_ajax_referer('get_volunteer_form_details', 'nonce');

         $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if (!$id) {
            wp_send_json_error(['message' => 'Invalid ID']);
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'db7_forms';

        $form = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE form_id = %d",
            $id
        ));

        if (!$form) {
            wp_send_json_error(['message' => 'Form not found']);
        }

        $form_data = unserialize($form->form_value);

        // 构建HTML内容
        $html = '<table class="wp-list-table widefat fixed striped">';
        foreach ($form_data as $key => $value) {
            if(in_array($key, ['cfdb7_status'])) {
              continue;
            }
            if (!in_array($key, ['status'])) {
                $label = $key;
                switch ($key) {
                    case 'your-name':
                        $label = '氏名';
                        break;
                    case 'your-email':
                        $label = 'メールアドレス';
                        break;
                    case 'your-message':
                        $label = 'メッセージ';
                        break;
                    case 'checkbox-time':
                        $label = '時間';
                        if (is_array($value)) {
                            $value = implode("、", $value);
                        }
                        break;
                }
                $html .= sprintf(
                    '<tr><th>%s</th><td>%s</td></tr>',
                    esc_html($label),
                    esc_html($value)
                );
            }
        }
        $html .= '</table>';

        wp_send_json_success(['html' => $html]);
    }

    public function exportCsv()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        check_admin_referer('volunteer_form_export_csv');

        global $wpdb;
        $table_name = $wpdb->prefix . 'db7_forms';

        $query = "SELECT * FROM {$table_name} WHERE form_post_id = %d ORDER BY form_date DESC";
        $forms = $wpdb->get_results($wpdb->prepare($query, $this->form_post_id));

        // 清除所有输出缓冲区
        while (ob_get_level()) {
            ob_end_clean();
        }

        // 设置响应头
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=volunteer_forms.csv');

        // 创建输出流
        $output = fopen('php://output', 'w');

        // 添加 BOM 以支持 Excel 中的中文
        fputs($output, "\xEF\xBB\xBF");

        // 写入 CSV 头部
        fputcsv($output, [
            '申請日時',
            '氏名',
            'メールアドレス',
            'ステータス'
        ]);

        // 写入数据
        foreach ($forms as $form) {
            $form_data = unserialize($form->form_value);
            $status = $form_data['status'] ?? '未審査';
            if ($status == '1') {
                $status = '審査通過';
            } elseif ($status == '2') {
                $status = '審査不通過';
            }

            fputcsv($output, [
                $form->form_date,
                $form_data['your-name'] ?? '',
                $form_data['your-email'] ?? '',
                $status
            ]);
        }

        fclose($output);
        exit;
    }
}
