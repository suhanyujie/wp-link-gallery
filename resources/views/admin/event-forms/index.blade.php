<div class="wrap">
    <h1><?php echo esc_html($page_title); ?></h1>

    <div class="tablenav top">
        <div class="alignleft actions" style="display:flex;">

            <form method="get" action="" class="form-inline">
                <input type="hidden" name="page" value="event-form-data-manage">
                <select name="form_id" id="form_id" class="postform">
                    <!-- <option value="">すべてのフォーム</option> -->
                    <?php foreach($forms as $form): ?>
                        <option value="<?php echo esc_attr($form->id()); ?>" <?php echo $form_id == $form->id() ? 'selected' : ''; ?>>
                            <?php echo esc_html($form->title()); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="submit" class="button" value="フィルター">
            </form>

            <?php if($form_id): ?>
                <span class="separator" style="display: inline-block; width: 2px; height: 28px; background-color: #ccd0d4; margin: 0 50px; vertical-align: middle;"></span>
                <button class="button add-entry" data-nonce="<?php echo wp_create_nonce('event_form_create'); ?>" style="margin-left:10px;">新規作成</button>
                <button class="button delete-selected" data-nonce="<?php echo wp_create_nonce('event_form_delete_bulk'); ?>" style="margin-left:10px; color: #a00;">選択したものを削除</button>
            <?php endif; ?>
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
                <th class="check-column"><input type="checkbox" id="cb-select-all"></th>
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
                        <td><input type="checkbox" name="entry[]" value="<?php echo esc_attr($entry['id']); ?>"></td>
                        <td><?php echo esc_html($entry['id']); ?></td>
                        <td><?php echo esc_html($entry['date']); ?></td>
                        <td>
                            <?php
                                $details = $entry['details'];
                                $preview = [];
                                if(is_array($details) && count($details) >= 3) {
                                    $preview = array_slice($details, 0, 3);
                                }
                                $preview_text = implode('; ', array_map(function($key, $value) {
                                    $tmpLabelName = LinkGallery\Services\EventFormDataManageService::getColumnMapForExport($key);
                                    return $tmpLabelName . ': ' . (is_array($value) ? implode(',', $value) : $value);
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
                    <td colspan="5">データがありません</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

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

                <?php if($paginationInfo['total'] > $paginationInfo['numPerPage']): ?>
                    <span class="pagination-links"><?php echo $page_links; ?></span>
                <?php endif; ?>
            </div>
        </div>
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
            <button type="submit" class="button button-primary entry-edit-content-btn">保存</button>
        </form>
    </div>
</div>

<!-- 新規作成モーダル -->
<div id="entry-create-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>新規作成</h2>
        <form id="entry-create-form">
            <div id="entry-create-content"></div>
            <input type="hidden" name="form_id" id="create_form_id">
            <input type="hidden" name="nonce" id="create_nonce">
            <button type="submit" class="button button-primary entry-create-content-btn">保存</button>
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

.tablenav-pages .pagination-links a,
.tablenav-pages .pagination-links span.current {
    display: inline-block;
    min-width: 28px;
    padding: 4px 8px;
    margin: 0 3px;
    border: 1px solid #ccc;
    border-radius: 3px;
    background: #f7f7f7;
    color: #555;
    text-align: center;
    text-decoration: none;
    font-size: 13px;
    line-height: 1.5;
    vertical-align: middle;
}

.tablenav-pages .pagination-links span.current {
    background: #0073aa;
    border-color: #006799;
    color: #fff;
    font-weight: 600;
}

.tablenav-pages .pagination-links a:hover,
.tablenav-pages .pagination-links a:focus {
    background: #00a0d2;
    border-color: #006799;
    color: #fff;
    box-shadow: 0 1px 1px rgba(0,0,0,0.1);
}

.tablenav-pages .displaying-num {
    margin-right: 10px;
    color: #555;
}
</style>

<script>
jQuery(document).ready(function($) {
    var FieldMap1 = {
        'your-name': '氏名',
        'your-email': 'メールアドレス',
        'your-message': 'メモ',
    }
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
                    var labelName = '';
                    var content = '<table class="wp-list-table widefat fixed">';
                    $.each(response.data.form_data, function(key, value) {
                        labelName = FieldMap1[key]?? key;
                        content += '<tr><th>' + labelName + '</th><td>' + (Array.isArray(value)? value.join(', ') : value) + '</td></tr>';
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
        var editQueryNonce = $('.view-details').data('nonce')
        var tmpLabelName = '';

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'get_event_form_details',
                id: id,
                nonce: editQueryNonce
            },
            success: function(response) {
                if (response.success) {
                    var content = '';
                    var label = '';
                    $.each(response.data.form_data, function(key, value) {
                        tmpLabelName = FieldMap1[key] ?? key;
                        label = key;
                        switch (label) {
                            case 'your-name':
                                  content += '<div class="form-field">\
                                                <label>' + tmpLabelName + '</label>\
                                                <input type="text" name="content[' + label + ']" value="'+value+'">\
                                            </div>';
                                    break;
                            case 'your-email':
                                  content += '<div class="form-field">\
                                                <label>' + tmpLabelName + '</label>\
                                                <input type="text" name="content[' + label + ']" value="'+value+'">\
                                            </div>';
                                    break;
                            case 'your-message':
                                  content += '<div class="form-field">\n\
                                                <label>' + tmpLabelName + '</label>\n\
                                                <textarea name="content[' + label + ']" rows="4" style="width: 100%; margin-top: 5px;">'+value+'</textarea>\n\
                                            </div>';
                                    break;
                        }
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
        var subBtn = $(this).find('.entry-edit-content-btn');
        if (subBtn.attr('disabled')) {
            return;
        }
        subBtn.attr('disabled', true);
        e.preventDefault();
        var formData = $(this).serializeArray();
        var postContent = Object.fromEntries(
                formData
                    .filter(item => item.name.startsWith('content['))
                    .map(item => [item.name.match(/\[([\w\-]+)\]/)[1], item.value])
            )
        var data = {
            action: 'event_form_update',
            id: $('#entry_id').val(),
            content: postContent,
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
            },
            complete: function() {
                subBtn.attr('disabled', false);
            }
        });
    });

    // 新規作成ボタンクリック
    $('.add-entry').click(function() {
        var form_id = $('#form_id').val();
        var nonce = $(this).data('nonce');
        $('#create_form_id').val(form_id);
        $('#create_nonce').val(nonce);
        var tmpLabelName = '';
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'get_event_form_fields',
                form_id: form_id,
                nonce: nonce
            },
            success: function(response) {
                if (response.success) {
                    var content = '';
                    $.each(response.data.fields, function(key, label) {
                        tmpLabelName = FieldMap1[label] ?? label;
                        switch (label) {
                            case 'your-name':
                                  content += '<div class="form-field">\
                                                <label>' + tmpLabelName + '</label>\
                                                <input type="text" name="content[' + label + ']" value="">\
                                            </div>';
                                    break;
                            case 'your-email':
                                  content += '<div class="form-field">\
                                                <label>' + tmpLabelName + '</label>\
                                                <input type="text" name="content[' + label + ']" value="">\
                                            </div>';
                                    break;
                            case 'your-message':
                                  content += '<div class="form-field">\n\
                                                <label>' + tmpLabelName + '</label>\n\
                                                <textarea name="content[' + label + ']" rows="4" style="width: 100%; margin-top: 5px;"></textarea>\n\
                                            </div>';
                                    break;
                        }
                    });
                    console.log(content);
                    $('#entry-create-content').html(content);
                    $('#entry-create-modal').show();
                } else {
                    alert(response.data.message);
                }
            }
        });
    });

    // 新規作成フォーム送信
    $('#entry-create-form').submit(function(e) {
        var subBtn = $(this).find('.entry-create-content-btn');
        if (subBtn.attr('disabled')) {
            return;
        }
        subBtn.attr('disabled', true);
        e.preventDefault();
        var formData = $(this).serializeArray();
        var postDataContent = Object.fromEntries(
                formData
                    .filter(item => item.name.startsWith('content['))
                    .map(item => [item.name.match(/\[([\w-]+)\]/)[1], item.value.trim()])
            );
        var data = {
            action: 'event_form_create',
            form_id: $('#create_form_id').val(),
            content: postDataContent,
            nonce: $('#create_nonce').val()
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
            },
            complete: function() {
                subBtn.attr('disabled', false);
            }
        });
    });

    // 全選/取消全選の処理
    $('#cb-select-all').change(function() {
        $('input[name="entry[]"]').prop('checked', $(this).prop('checked'));
    });

    // 選択した項目を削除
    $('.delete-selected').click(function() {
        var selectedIds = $('input[name="entry[]"]:checked').map(function() {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) {
            alert('削除する項目を選択してください。');
            return;
        }

        if (!confirm('選択した' + selectedIds.length + '件のデータを削除してもよろしいですか？')) {
            return;
        }

        var nonce = $('.delete-selected').data('nonce');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'event_form_delete_bulk',
                ids: selectedIds,
                nonce: nonce
            },
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


