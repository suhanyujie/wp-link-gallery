<div class="wrap">
    <h1>学習者申請管理</h1>

    <div id="status-message" style="display: none;" class="notice is-dismissible">
        <p></p>
    </div>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>申請日</th>
                <th>氏名</th>
                <th>メール</th>
                <th>ステータス</th>
                <th>詳細内容</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($applications)): ?>
                <?php foreach ($applications as $application):

                ?>
                    <tr>
                        <td><?php echo esc_html(date('Y-m-d H:i', strtotime($application['date']))); ?></td>
                        <td><?php echo esc_html($application['name']); ?></td>
                        <td><?php echo esc_html($application['email']); ?></td>
                        <td><?php echo esc_html($application['status']); ?></td>
                        <td>
                            <?php
                            if (!empty($application['details'])) {
                                foreach ($application['details'] as $key => $value) {
                                    if (!in_array($key, ['your-name', 'your-email', 'status'])) {
                                      switch ($key) {
                                        case 'checkbox-241': $key = '時間';break;
                                        case 'your-message': $key = 'message';break;
                                      }
                                      $showStr = esc_html($value);
                                      if (is_array($value)) {
                                        $showStr = implode(', ', $value);
                                      }
                                      echo '<div><strong>' . esc_html($key) . ':</strong> ' . $showStr . '</div>';
                                    }
                                }
                            }
                            ?>
                        </td>
                        <td>
                            <?php if ($application['status'] === '未審査'): ?>
                                <button type="button" class="button button-small button-primary update-status"
                                        data-id="<?php echo esc_attr($application['id']); ?>"
                                        data-status="1"
                                        data-nonce="<?php echo wp_create_nonce('learner_form_update'); ?>">
                                    審査通過
                                </button>
                                <button type="button" class="button button-small update-status"
                                        data-id="<?php echo esc_attr($application['id']); ?>"
                                        data-status="2"
                                        data-nonce="<?php echo wp_create_nonce('learner_form_update'); ?>">
                                    審査不通過
                                </button>
                            <?php else: ?>
                                <span class="status-text">
                                    <?php echo $application['status'] === '審査通過' ? '審査通過' : '審査不通過'; ?>
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">申請データがありません</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</div>

<script>
jQuery(document).ready(function($) {
    $('.update-status').on('click', function() {
        const $button = $(this);
        if ($button.prop('disabled')) return;

        // 禁用所有审核按钮
        $('.update-status').prop('disabled', true).addClass('updating');
        const $messageDiv = $('#status-message');
        const id = $button.data('id');
        const status = $button.data('status');
        const nonce = $button.data('nonce');

        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'learner_form_update',
                id: id,
                status: status,
                _wpnonce: nonce
            },
            success: function() {
                $messageDiv
                    .removeClass()
                    .addClass('notice notice-success is-dismissible')
                    .find('p')
                    .text('ステータスが更新されました')
                    .end()
                    .show();

                setTimeout(function() {
                    window.location.reload();
                }, 1000);
            },
            error: function() {
                $messageDiv
                    .removeClass()
                    .addClass('notice notice-error is-dismissible')
                    .find('p')
                    .text('エラーが発生しました')
                    .end()
                    .show();
                // 发生错误时启用所有按钮
                $('.update-status').prop('disabled', false).removeClass('updating');
            },
            complete: function() {
                if (!$messageDiv.hasClass('notice-success')) {
                    // 请求失败时启用所有按钮
                    $('.update-status').prop('disabled', false).removeClass('updating');
                }
            }
        });
    });
});

</script>
<style>
.update-status.updating {
    cursor: not-allowed;
    opacity: 0.6;
}
</style>
