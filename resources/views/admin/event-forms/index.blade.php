<div class="wrap">
    <h1><?php echo esc_html($page_title); ?></h1>

    <div class="tablenav top">
        <div class="alignleft actions">
            <form method="get" action="" class="form-inline">
                <input type="hidden" name="page" value="event-form-data-manage">
                <select name="form_id" id="form_id" class="postform">
                    <option value="">すべてのフォーム</option>
                    <?php foreach($forms as $form): ?>
                        <option value="<?php echo esc_attr($form->id()); ?>" <?php echo $form_id == $form->id() ? 'selected' : ''; ?>>
                            <?php echo esc_html($form->title()); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="submit" class="button" value="フィルター">
                
            </form>
        </div>
        <div class="alignright">
            <?php if($form_id): ?>
                <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=event_form_export_csv&form_id=' . $form_id), 'event_form_export_csv'); ?>" class="button">CSVエクスポート</a>
            <?php endif; ?>
        </div>
        <br class="clear">
    </div>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>提出日時</th>
                <th>内容</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php if(count($form_entries) > 0): ?>
                <?php foreach($form_entries as $entry): ?>
                    <tr>
                        <td><?php echo esc_html($entry['id']); ?></td>
                        <td><?php echo esc_html($entry['date']); ?></td>
                        <td>
                            <?php
                                $details = $entry['details'];
                                $preview = array_slice($details, 0, 3);
                                $preview_text = implode(', ', array_map(function($key, $value) {
                                    return $key . ': ' . (is_array($value) ? implode(',', $value) : $value);
                                }, array_keys($preview), $preview));
                                echo esc_html($preview_text);
                                if(count($details) > 3) {
                                    echo '...';
                                }
                            ?>
                        </td>
                        <td>
                            <button class="button view-details" data-id="<?php echo esc_attr($entry['id']); ?>" data-nonce="<?php echo wp_create_nonce('get_event_form_details'); ?>">詳細</button>
                            <button class="button edit-entry" data-id="<?php echo esc_attr($entry['id']); ?>" data-nonce="<?php echo wp_create_nonce('event_form_update'); ?>">編集</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">データがありません</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if($paginationInfo['total'] > $paginationInfo['numPerPage']): ?>
        <div class="tablenav bottom">
            <div class="tablenav-pages">
                <?php
                    $total_pages = ceil($paginationInfo['total'] / $paginationInfo['numPerPage']);
                    $current_page = isset($_GET['paged']) ? absint($_GET['paged']) : 1;
                    $page_links = paginate_links(array(
                        'base' => add_query_arg('paged', '%#%'),
                        'format' => '',
                        'prev_text' => __('&laquo;'),
                        'next_text' => __('&raquo;'),
                        'total' => $total_pages,
                        'current' => $current_page
                    ));
                ?>
                <span class="displaying-num"><?php echo esc_html($paginationInfo['total']); ?>件</span>
                <span class="pagination-links"><?php echo $page_links; ?></span>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- 詳細モーダル -->
<div id="entry-details-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>申込詳細</h2>
        <div id="entry-details-content"></div>
    </div>
</div>

<!-- 編集モーダル -->
<div id="entry-edit-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>申込編集</h2>
        <form id="entry-edit-form">
            <div id="entry-edit-content"></div>
            <input type="hidden" name="entry_id" id="entry_id">
            <input type="hidden" name="nonce" id="edit_nonce">
            <button type="submit" class="button button-primary">保存</button>
        </form>
    </div>
</div>

<style>
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
</style>

<script>
jQuery(document).ready(function($) {
    // フォーム選択時に自動送信
    $('#form_id').change(function() {
        $(this).closest('form').submit();
    });

    // 詳細表示
    $('.view-details').click(function() {
        var id = $(this).data('id');
        var nonce = $(this).data('nonce');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'get_event_form_details',
                id: id,
                nonce: nonce
            },
            success: function(response) {
                if (response.success) {
                    var content = '<table class="wp-list-table widefat fixed">';
                    $.each(response.data.form_data, function(key, value) {
                        content += '<tr><th>' + key + '</th><td>' + (Array.isArray(value) ? value.join(', ') : value) + '</td></tr>';
                    });
                    content += '</table>';
                    $('#entry-details-content').html(content);
                    $('#entry-details-modal').show();
                } else {
                    alert(response.data.message);
                }
            }
        });
    });

    // 編集モーダル表示
    $('.edit-entry').click(function() {
        var id = $(this).data('id');
        var nonce = $(this).data('nonce');
        $('#entry_id').val(id);
        $('#edit_nonce').val(nonce);

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'get_event_form_details',
                id: id,
                nonce: $(this).data('nonce')
            },
            success: function(response) {
                if (response.success) {
                    var content = '';
                    $.each(response.data.form_data, function(key, value) {
                        content += '<div class="form-field"><label>' + key + '</label>';
                        if (Array.isArray(value)) {
                            content += '<input type="text" name="content[' + key + ']" value="' + value.join(', ') + '">';
                        } else {
                            content += '<input type="text" name="content[' + key + ']" value="' + value + '">';
                        }
                        content += '</div>';
                    });
                    $('#entry-edit-content').html(content);
                    $('#entry-edit-modal').show();
                } else {
                    alert(response.data.message);
                }
            }
        });
    });

    // モーダルを閉じる
    $('.close').click(function() {
        $(this).closest('.modal').hide();
    });

    $(window).click(function(e) {
        if ($(e.target).hasClass('modal')) {
            $('.modal').hide();
        }
    });

    // 編集フォーム送信
    $('#entry-edit-form').submit(function(e) {
        e.preventDefault();
        var formData = $(this).serializeArray();
        var data = {
            action: 'event_form_update',
            id: $('#entry_id').val(),
            content: JSON.stringify(Object.fromEntries(
                formData
                    .filter(item => item.name.startsWith('content['))
                    .map(item => [item.name.match(/\[(.*?)\]/)[1], item.value])
            )),
            nonce: $('#edit_nonce').val()
        };

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: data,
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert(response.data.message);
                }
            }
        });
    });
});
</script>
