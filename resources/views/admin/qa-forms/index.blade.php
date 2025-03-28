<?php
if (!defined('ABSPATH')) {
    exit;
}

$page = isset($_GET['paged']) ? absint($_GET['paged']) : 1;
$per_page = $paginationInfo['numPerPage'] ?? 20;
$offset = ($page - 1) * $per_page;
$total_items = $paginationInfo['total'] ?? 0;
$total_pages = ceil($total_items / $per_page);
$current_items = array_slice($applications, $offset, $per_page);

$status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
$date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '';
?>
<div class="wrap">
    <h1 class="wp-heading-inline">問い合わせのフォーム管理</h1>
    <hr class="wp-header-end">

    <!-- フィルターフォーム -->
    <div class="tablenav top">
        <form method="get" action="">
            <input type="hidden" name="page" value="ask-question-form">
            <div class="alignleft actions">
                <select name="status">
                    <option value="0" <?php selected($status_filter, '0'); ?>>未審査</option>
                    <option value="1" <?php selected($status_filter, '1'); ?>>審査通過</option>
                    <option value="2" <?php selected($status_filter, '2'); ?>>審査不通過</option>
                    <option value="-1">全てのステータス</option>
                </select>
                <!-- <input type="search" name="search" value="<?php echo esc_attr(isset($_GET['search']) ? $_GET['search'] : ''); ?>" placeholder="名前またはメールで検索"> -->
                <input type="submit" class="button" value="フィルター">
            </div>
        </form>
        <div class="alignright">
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="display: inline;">
                <input type="hidden" name="action" value="qa_form_export_csv">
                <input type="hidden" name="status" value="<?php echo esc_attr($status_filter); ?>">
                <input type="hidden" name="date_from" value="<?php echo esc_attr($date_from); ?>">
                <input type="hidden" name="date_to" value="<?php echo esc_attr($date_to); ?>">
                <input type="hidden" name="search" value="<?php echo esc_attr(isset($_GET['search']) ? $_GET['search'] : ''); ?>">
                <?php wp_nonce_field('qa_form_export_csv'); ?>
                <span>number: </span><span><?=$paginationInfo['total']??0;?></span>
            </form>
        </div>
    </div>

    <!-- テーブル -->
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th scope="col">ID</th>
                <th scope="col">時日</th>
                <th scope="col">名前</th>
                <th scope="col">メール</th>
                <th scope="col">ステータス</th>
                <th scope="col">詳細</th>
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
                        <?php echo esc_html($item['status_desc']); ?>
                    </span>
                </td>
                <td>
                    <button class="button-link view-details" data-id="<?php echo esc_attr($item['id']); ?>">詳細を見る</button>
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
        <h2>詳細</h2>
        <div>
          <div id="details-content"></div>
          <hr/>
          <div id="reply-box" data-id="">
            <h3>返信</h3>
            <textarea id="reply-content" class="widefat" rows="5" placeholder="返信内容を入力してください..."></textarea>
            <p class="submit">
              <button type="button" id="submit-reply" class="button button-primary">返信を送信</button>
              <button type="button" id="submit-reject" class="button button-danger">拒否</button>
            </p>
          </div>
        </div>
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
.status-column .status-0 { color: #646970; }
.status-column .status-1 { color: #00a32a; }
.status-column .status-2 { color: #d63638; }
#submit-reject{margin-left: 200px; border-color: #d63638;}
</style>

<script>
// モーダル管理用のオブジェクト
const DetailsModal = {
    modal: null,
    closeBtn: null,
    contentContainer: null,

    init: function() {
        this.modal = document.getElementById('details-modal');
        this.closeBtn = this.modal.querySelector('.close');
        this.contentContainer = document.getElementById('details-content');

        this.closeBtn.onclick = () => this.hide();
        window.onclick = (event) => {
            if (event.target === this.modal) {
                this.hide();
            }
        };
    },

    show: function() {
        this.modal.style.display = 'block';
    },

    hide: function() {
        this.modal.style.display = 'none';
    },

    setContent: function(data) {
        let dataObj = data['form_data'] ?? {};
        let html = '<table class="wp-list-table widefat fixed striped">';
        let keyDesc = '';
        for (var key in dataObj) {
            keyDesc = DetailsModal.getKeyDesc(key);

            html += '<tr>';
            html += '<th style="width: 30%; padding: 8px;">' + keyDesc + '</th>';
            html += '<td style="padding: 8px;">' + (dataObj[key] || '-') + '</td>';
            html += '</tr>';
        }
        html += '</table>';
        // this.contentContainer.innerHTML = html;
        jQuery('#details-content').html(html)
        this.show();
    },
    getKeyDesc: function(key) {
       let desc = key;
        switch (key) {
            case 'your-name':
                desc = '氏名';
                break;
            case 'your-email':
                desc = 'メールアドレス';
                break;
            case 'your-message':
                desc = 'メッセージ';
                break;
        }
        return desc;
    }
};

jQuery(document).ready(function($) {
    // モーダルの初期化
    DetailsModal.init();

    // 詳細を見るボタンのクリックイベント
    $('.view-details').on('click', function() {
        const id = $(this).data('id');
        $('#reply-box').attr('data-id', id);
        // Ajax リクエスト
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'get_qa_form_details',
                id: id,
                nonce: '<?php echo wp_create_nonce("get_qa_form_details"); ?>'
            },
            success: function(response) {
                if (response.success) {
                    // モーダルにデータを設定して表示
                    DetailsModal.setContent(response.data);
                } else {
                    alert('データの取得に失敗しました');
                }
            },
            error: function() {
                alert('サーバーとの通信に失敗しました');
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

    // 回复提交
    $('#submit-reply,#submit-reject').click(function() {
        const replyContent = $('#reply-content').val().trim();
        if (!replyContent) {
            alert('返信内容を入力してください');
            return;
        }

        const currentId = $('#reply-box').attr('data-id');
        if (!currentId) {
            alert('フォームIDを取得できません');
            return;
        }
        let _this = $(this);
        let isReject = 0;
        console.log('attr-id: ', _this.attr('id'));
        if (_this.attr('id') === 'submit-reject') {
            isReject = 1;
        }

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'qa_form_sendMessage',
                id: currentId,
                message: replyContent,
                isReject: isReject,
                nonce: '<?php echo wp_create_nonce("qa_form_sendMessage"); ?>'
            },
            success: function(response) {
                if (response.success) {
                    alert('返信が送信されました');
                    $('#reply-content').val('');
                    $('#details-modal').hide();
                    // 刷新页面
                    location.reload();
                } else {
                    alert(response.data.message || '送信に失敗しました');
                }
            },
            error: function() {
                alert('サーバーとの通信に失敗しました');
            }
        });
    });
});
</script>
