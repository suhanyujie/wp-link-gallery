<?php
/**
 * 問い合わせのフォーム管理
 */

namespace LinkGallery\Controllers\Admin;

use LinkGallery\Services\Admin\QaFormService;

class QaFormController
{
    protected $form_post_id = 246;
    protected $page_slug = 'ask-question-form';
    protected $page_title = '問い合わせ管理';
    protected $table_name = 'lg_contact_forms';

    public function __construct()
    {
        add_action('admin_post_qa_form_update', [$this, 'update']);
        add_action('admin_post_qa_form_sendMessage', [$this, 'sendReply']);
        add_action('wp_ajax_qa_form_sendMessage', [$this, 'sendReply']);
        add_action('wp_ajax_qa_form_update', [$this, 'ajaxUpdate']);
        add_action('wp_ajax_get_qa_form_details', [$this, 'getFormDetails']);
        add_action('admin_post_qa_form_export_csv', [$this, 'exportCsv']);
        add_action('wp_ajax_qa_form_export_csv', [$this, 'exportCsv']);
    }

    public function index()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . $this->table_name;
        $paginationInfo = [
            'total' => 0,
            'pageNum' => 0,
            'numPerPage' => 10,
        ];

        // フィルター条件の取得
        $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '0';
        $date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '';
        $date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '';
        $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';

        // 基本のクエリ
        $query = "SELECT * FROM {$table_name} WHERE form_id = %d";
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
        error_log(print_r(['log' => [
            '$status_filter' => $status_filter,
            '1' => var_export($status_filter, true),
        ]], true));
        if ($status_filter !== '-1') {
            $query .= " AND status = %s";
            $params[] = (string)($status_filter);
        }
        $query .= " ORDER BY id DESC";

        // ページネーション
        $per_page = 20;
        $paginationInfo['numPerPage'] = $per_page;
        $current_page = isset($_GET['paged']) ? absint($_GET['paged']) : 1;
        $offset = ($current_page - 1) * $per_page;

        // 総件数を取得
        $total_query = "SELECT COUNT(*) FROM ({$wpdb->prepare($query, $params)}) AS t";
        $total_items = $wpdb->get_var($total_query);
        $paginationInfo['total'] = $total_items;

        // ページネーション用にクエリを修正
        $query .= " LIMIT %d OFFSET %d";
        $params[] = $per_page;
        $params[] = $offset;

        // クエリの実行
        $forms = $wpdb->get_results($wpdb->prepare($query, $params));

        // データの整形
        $applications = [];
        foreach ($forms as $form) {
            $form_data = json_decode($form->content, 256);
            $itemData = [
                'id' => $form->id,
                'date' => $form->created_at,
                'name' => $form_data['your-name'] ?? '',
                'email' => $form_data['your-email'] ?? '',
                'status' => $form->status ?? '0',
                'details' => $form_data
            ];
            $itemData['status_desc'] = $itemData['status'];
            if ($itemData['status'] == '1') {
                $itemData['status_desc'] = '審査通過';
            } elseif ($itemData['status'] == '2') {
                $itemData['status_desc'] = '審査不通過';
            } else {
                $itemData['status_desc'] = '未審査';
            }

            $applications[] = $itemData;
        }

        view('admin.qa-forms.index', compact('applications', 'paginationInfo'));
    }

    public function update()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        check_admin_referer('qa_form_update');

        $id = $_POST['id'] ?? 0;
        $status = sanitize_text_field($_POST['status'] ?? '');

        $result = $this->updateStatus($id, $status);

        wp_redirect(admin_url('admin.php?page=' . $this->page_slug . '&message=updated'));
        exit;
    }

    // for ajax
    public function sendReply()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        check_ajax_referer('qa_form_sendMessage', 'nonce');
        // 记录日志
//        error_log(print_r(['log' => []], true));
        $id = $_POST['id'] ?? 0;
        $message = sanitize_text_field($_POST['message'] ?? '');
        $isReject = sanitize_text_field($_POST['isReject'] ?? 0);
        $svc = new QaFormService();
        if ($isReject) {
            $res = $svc->replyForReject($id, $message);
        } else {
            $res = $svc->replyToUserAndSendEmail($id, $message);
        }

        wp_send_json_success([
            'message' => '返信が送信されました',
            'data' => [
                'isReject' => $isReject,
                'res' => $res,
            ],
        ]);

        // wp_redirect(admin_url('admin.php?page=' . $this->page_slug . '&status=0'));
        exit;
    }

    public function getFormDetails()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        check_ajax_referer('get_qa_form_details', 'nonce');

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

        $form_data = json_decode($form->content, 256);

        wp_send_json_success(['form_data' => $form_data]);
    }

    public function exportCsv()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        check_admin_referer('qa_form_export_csv');

        global $wpdb;
        $table_name = $wpdb->prefix . $this->table_name;

        $query = "SELECT * FROM {$table_name} WHERE form_id = %d ORDER BY id DESC";
        $forms = $wpdb->get_results($wpdb->prepare($query, $this->form_post_id));

        // 清除所有输出缓冲区
        while (ob_get_level()) {
            ob_end_clean();
        }

        // 设置响应头
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=qa_forms.csv');

        // 创建输出流
        $output = fopen('php://output', 'w');
        // 写入 CSV 头部
        fputcsv($output, [
            '申請日時',
            '氏名',
            'メールアドレス',
            'ステータス'
        ]);

        // 写入数据
        foreach ($forms as $form) {
            $form_data = json_decode($form->content, 256);
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
