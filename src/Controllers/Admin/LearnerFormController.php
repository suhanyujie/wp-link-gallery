<?php

namespace LinkGallery\Controllers\Admin;

class LearnerFormController
{
    private $emailService;

    public function __construct()
    {
        $this->emailService = new \LinkGallery\Services\EmailService();
        add_action('admin_post_learner_form_update', [$this, 'update']);
        add_action('wp_ajax_learner_form_update', [$this, 'ajaxUpdate']);
        add_action('wp_ajax_get_learner_form_details', [$this, 'getLearnerFormDetails']);
    }

    public function index()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'db7_forms';
        $form_post_id = 894;

        // フィルター条件の取得
        $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        $date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '';
        $date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '';
        $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';

        // 基本のクエリ
        $query = "SELECT * FROM {$table_name} WHERE form_post_id = %d";
        $params = [$form_post_id];

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

        // CSVエクスポート
        if (isset($_GET['export']) && $_GET['export'] === 'csv') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=learner_forms.csv');
            $output = fopen('php://output', 'w');
            fputcsv($output, ['ID', '申請日', '名前', 'メール', 'ステータス']);
            foreach ($forms as $form) {
                $form_data = unserialize($form->form_value);
                $status = $form_data['status'] ?? '未審査';
                if ($status === '1') $status = '審査通過';
                elseif ($status === '2') $status = '審査不通過';
                fputcsv($output, [
                    $form->form_id,
                    $form->form_date,
                    $form_data['your-name'] ?? '',
                    $form_data['your-email'] ?? '',
                    $status
                ]);
            }
            fclose($output);
            exit;
        }

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

        view('admin.learner-forms.index2', compact('applications'));
    }

    public function update()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        check_admin_referer('learner_form_update');

        $id = $_POST['id'] ?? 0;
        $status = sanitize_text_field($_POST['status'] ?? '');

        $result = $this->updateStatus($id, $status);

        wp_redirect(admin_url('admin.php?page=learner-form&message=updated'));
        exit;
    }

    public function ajaxUpdate()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        check_ajax_referer('learner_form_update', 'nonce');

        $id = $_POST['id'] ?? 0;
        $status = sanitize_text_field($_POST['status'] ?? '');

        $result = $this->updateStatus($id, $status);

        if ($result) {
            wp_send_json_success(['message' => '更新成功']);
        } else {
            wp_send_json_error(['message' => '更新失败']);
        }
    }

    private function updateStatus($id, $status)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'db7_forms';

        $form = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE form_id = %d",
            $id
        ));

        if ($form && in_array($status, ['1', '2'])) {
            $form_data = unserialize($form->form_value);
            // if (isset($form_data['status'])) {
            //   // 状态已经被更新，不可再次更新
            //   return false;
            // }

            $form_data['status'] = $status;

            $result = $wpdb->update(
                $table_name,
                ['form_value' => serialize($form_data)],
                ['form_id' => $id],
                ['%s'],
                ['%d']
            );

            if ($result !== false) {
                $email = $form_data['your-email'] ?? '';
                $name = $form_data['your-name'] ?? '';

                if ($email && $name) {
                    if ($status === '1') {
                        $this->emailService->sendApprovalEmail($email, $name);
                    } elseif ($status === '2') {
                        $this->emailService->sendRejectionEmail($email, $name);
                    }
                }
                return true;
            }
            return false;
        }

        return false;
    }

    public function getLearnerFormDetails()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        check_ajax_referer('get_learner_form_details', 'nonce');

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
                    case 'checkbox-241':
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
}
