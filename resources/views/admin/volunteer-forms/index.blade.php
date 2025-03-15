<?php
if (!defined('ABSPATH')) {
    exit;
}

$page = isset($_GET['paged']) ? absint($_GET['paged']) : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;
$total_items = count($applications);
$total_pages = ceil($total_items / $per_page);
$current_items = array_slice($applications, $offset, $per_page);

$status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
$date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '';
?>
<div class="wrap">
    <h1 class="wp-heading-inline">ボランティア申請管理</h1>
    <hr class="wp-header-end">

    <!-- フィルターフォーム -->
    <div class="tablenav top">
        <form method="get" action="">
            <input type="hidden" name="page" value="volunteer-form">
            <div class="alignleft actions">
                <select name="status">
                    <option value="">全てのステータス</option>
                    <option value="未審査" <?php selected($status_filter, '未審査'); ?>>未審査</option>
                    <option value="審査通過" <?php selected($status_filter, '審査通過'); ?>>審査通過</option>
                    <option value="審査不通過" <?php selected($status_filter, '審査不通過'); ?>>審査不通過</option>
                </select>
                <input type="date" name="date_from" value="<?php echo esc_attr($date_from); ?>" placeholder="開始日">
                <input type="date" name="date_to" value="<?php echo esc_attr($date_to); ?>" placeholder="終了日">
                <input type="search" name="search" value="<?php echo esc_attr(isset($_GET['search']) ? $_GET['search'] : ''); ?>" placeholder="名前またはメールで検索">
                <input type="submit" class="button" value="フィルター">
            </div>
        </form>
        <div class="alignright">
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="display: inline;">
                <input type="hidden" name="action" value="volunteer_form_export_csv">
                <input type="hidden" name="status" value="<?php echo esc_attr($status_filter); ?>">
                <input type="hidden" name="date_from" value="<?php echo esc_attr($date_from); ?>">
                <input type="hidden" name="date_to" value="<?php echo esc_attr($date_to); ?>">
                <input type="hidden" name="search" value="<?php echo esc_attr(isset($_GET['search']) ? $_GET['search'] : ''); ?>">
                <?php wp_nonce_field('volunteer_form_export_csv'); ?>
                <button type="submit" class="button">CSVエクスポート</button>
            </form>
        </div>
    </div>

    <!-- テーブル -->
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th scope="col">ID</th>
                <th scope="col">申請日</th>
                <th scope="col">名前</th>
                <th scope="col">メール</th>
                <th scope="col">ステータス</th>
                <th scope="col">時間</th>
                <th scope="col">詳細</th>
                <th scope="col">操作</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($current_items)): ?>
                <?php foreach ($current_items as $item): ?>
                <tr>
                <td><?php echo esc_html($item['id']); ?></td>
                <td><?php echo esc_html(date('Y-m-d H:i', strtotime($item['date']))); ?></td>
                <td><?php echo esc_html($item['name']); ?></td>
                <td><?php echo esc_html($item['email']); ?></td>
                <td class="status-column">
                    <span class="status-<?php echo sanitize_html_class(strtolower($item['status'])); ?>">
                        <?php echo esc_html($item['status']); ?>
                    </span>
                </td>
                <td>
                    <?php
                    $form_data = $item['details'];
                    if (isset($form_data['checkbox-time']) && is_array($form_data['checkbox-time'])) {
                        echo esc_html(implode(', ', $form_data['checkbox-time']));
                    }
                    ?>
                </td>
                <td>
                    <button class="button-link view-details" data-id="<?php echo esc_attr($item['id']); ?>">詳細を見る</button>
                </td>
                <td>
                    <div class="row-actions">
                        <?php if ($item['status'] === '未審査'): ?>
                            <button class="button-link update-status" data-id="<?php echo esc_attr($item['id']); ?>" data-status="1">承認</button>
                            |
                            <button class="button-link update-status" data-id="<?php echo esc_attr($item['id']); ?>" data-status="2">却下</button>
                        <?php else: ?>
                            ——
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" style="text-align: center;">データが見つかりませんでした</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- ページネーション -->
    <div class="tablenav bottom">
        <div class="tablenav-pages">
            <?php
            echo paginate_links(array(
                'base' => add_query_arg('paged', '%#%'),
                'format' => '',
                'prev_text' => __('&laquo;'),
                'next_text' => __('&raquo;'),
                'total' => $total_pages,
                'current' => $page,
            ));
            ?>
        </div>
    </div>
</div>

<!-- 詳細モーダル -->
<div id="details-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>申請詳細</h2>
        <div id="details-content"></div>
    </div>
</div>

<style>
/* モーダルスタイル */
.modal {
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.4);
}

.modal-content {
    background-color: #fefefe;
    margin: 15% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    max-width: 600px;
    border-radius: 4px;
    position: relative;
}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close:hover,
.close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}
.status-column .status-未審査 { color: #646970; }
.status-column .status-審査通過 { color: #00a32a; }
.status-column .status-審査不通過 { color: #d63638; }
</style>

<script>
jQuery(document).ready(function($) {
    // 詳細を見るボタンのクリックイベント
    $('.view-details').click(function() {
        var id = $(this).data('id');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'get_volunteer_form_details',
                id: id,
                nonce: '<?php echo wp_create_nonce("get_volunteer_form_details"); ?>'
            },
            success: function(response) {
                if (response.success) {
                    $('#details-content').html(response.data.html);
                    $('#details-modal').show();
                } else {
                    alert(response.data.message);
                }
            }
        });
    });

    // モーダルを閉じる
    $('.close').click(function() {
        $('#details-modal').hide();
    });

    // モーダル外クリックで閉じる
    $(window).click(function(event) {
        if ($(event.target).is('#details-modal')) {
            $('#details-modal').hide();
        }
    });

    // ステータス更新
    $('.update-status').click(function() {
        if (!confirm('ステータスを更新してもよろしいですか？')) {
            return;
        }

        var button = $(this);
        var id = button.data('id');
        var status = button.data('status');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'volunteer_form_update',
                id: id,
                status: status,
                nonce: '<?php echo wp_create_nonce("volunteer_form_update"); ?>'
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data.message);
                }
            }
        });
    });
});
</script>
