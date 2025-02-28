<div class="wrap">
    <?php
    // 在管理页面中加载 Thickbox
    add_thickbox();
    ?>
    <h1 class="wp-heading-inline">友情リンク管理</h1>
    <a href="#TB_inline?width=600&height=550&inlineId=link-gallery-modal" class="page-title-action thickbox">新規リンク追加</a>

    <?php if (isset($_GET['message'])): ?>
        <?php
        $messages = [
            'created' => 'リンクを作成しました',
            'updated' => 'リンクを更新しました',
            'deleted' => 'リンクを削除しました'
        ];
        $message = $messages[$_GET['message']] ?? '';
        ?>
        <?php if ($message): ?>
            <div class="notice notice-success is-dismissible">
                <p><?php echo esc_html($message); ?></p>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th scope="col" class="manage-column">名称</th>
                <th scope="col" class="manage-column">URL</th>
                <th scope="col" class="manage-column">説明</th>
                <th scope="col" class="manage-column">状態</th>
                <th scope="col" class="manage-column">並び順</th>
                <th scope="col" class="manage-column">操作</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($links)): ?>
                <?php foreach ($links as $link): ?>
                    <tr>
                        <td>
                            <?php if ($link->image): ?>
                                <img src="<?php echo esc_url($link->image); ?>" alt="<?php echo esc_attr($link->name); ?>" style="width: 20px; height: 20px; vertical-align: middle; margin-right: 5px;">
                            <?php endif; ?>
                            <?php echo esc_html($link->name); ?>
                        </td>
                        <td><a href="<?php echo esc_url($link->url); ?>" target="_blank"><?php echo esc_url($link->url); ?></a></td>
                        <td><?php echo esc_html($link->description); ?></td>
                        <td><?php echo esc_html($link->status); ?></td>
                        <td><?php echo esc_html($link->sort_order); ?></td>
                        <td>
                            <a href="#TB_inline?width=600&height=550&inlineId=link-gallery-edit-modal-<?php echo esc_attr($link->id); ?>" class="button button-small thickbox">編集</a>
                            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="display: inline;" onsubmit="return confirm('このリンクを削除してもよろしいですか？');">
                                <input type="hidden" name="action" value="link_gallery_delete">
                                <input type="hidden" name="id" value="<?php echo esc_attr($link->id); ?>">
                                <?php wp_nonce_field('link_gallery_delete'); ?>
                                <button type="submit" class="button button-small button-link-delete">削除</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">友情リンクはありません</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- 编辑模态窗口内容 -->
    <?php foreach ($links as $link): ?>
    <div id="link-gallery-edit-modal-<?php echo esc_attr($link->id); ?>" style="display:none;">
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" class="link-gallery-form">
            <input type="hidden" name="action" value="link_gallery_update">
            <input type="hidden" name="id" value="<?php echo esc_attr($link->id); ?>">
            <?php wp_nonce_field('link_gallery_update'); ?>

            <table class="form-table">
                <tr>
                    <th scope="row"><label for="name-<?php echo esc_attr($link->id); ?>">名称 <span class="required">*</span></label></th>
                    <td>
                        <input name="name" type="text" id="name-<?php echo esc_attr($link->id); ?>" class="regular-text" value="<?php echo esc_attr($link->name); ?>" required>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="url-<?php echo esc_attr($link->id); ?>">URL <span class="required">*</span></label></th>
                    <td>
                        <input name="url" type="url" id="url-<?php echo esc_attr($link->id); ?>" class="regular-text" value="<?php echo esc_url($link->url); ?>" required>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="description-<?php echo esc_attr($link->id); ?>">説明</label></th>
                    <td>
                        <textarea name="description" id="description-<?php echo esc_attr($link->id); ?>" class="large-text" rows="3"><?php echo esc_textarea($link->description); ?></textarea>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="image-<?php echo esc_attr($link->id); ?>">画像URL</label></th>
                    <td>
                        <input name="image" type="url" id="image-<?php echo esc_attr($link->id); ?>" class="regular-text" value="<?php echo esc_url($link->image); ?>">
                        <p class="description">サイトのロゴやアイコンのURLを入力してください</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="target-<?php echo esc_attr($link->id); ?>">リンクの開き方</label></th>
                    <td>
                        <select name="target" id="target-<?php echo esc_attr($link->id); ?>">
                            <option value="_blank" <?php selected($link->target, '_blank'); ?>>新しいタブで開く</option>
                            <option value="_self" <?php selected($link->target, '_self'); ?>>同じタブで開く</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="status-<?php echo esc_attr($link->id); ?>">状態</label></th>
                    <td>
                        <select name="status" id="status-<?php echo esc_attr($link->id); ?>">
                            <option value="active" <?php selected($link->status, 'active'); ?>>有効</option>
                            <option value="inactive" <?php selected($link->status, 'inactive'); ?>>無効</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="sort_order-<?php echo esc_attr($link->id); ?>">並び順</label></th>
                    <td>
                        <input name="sort_order" type="number" id="sort_order-<?php echo esc_attr($link->id); ?>" class="small-text" value="<?php echo esc_attr($link->sort_order); ?>">
                        <p class="description">数字が小さいほど前に表示されます</p>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <button type="submit" class="button button-primary">更新</button>
            </p>
        </form>
    </div>
    <?php endforeach; ?>

    <!-- 新規追加模态窗口内容 -->
    <div id="link-gallery-modal" style="display:none;">
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" class="link-gallery-form">
            <input type="hidden" name="action" value="link_gallery_create">
            <?php wp_nonce_field('link_gallery_create'); ?>

            <table class="form-table">
                <tr>
                    <th scope="row"><label for="name">名称 <span class="required">*</span></label></th>
                    <td>
                        <input name="name" type="text" id="name" class="regular-text" required>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="url">URL <span class="required">*</span></label></th>
                    <td>
                        <input name="url" type="url" id="url" class="regular-text" required>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="description">説明</label></th>
                    <td>
                        <textarea name="description" id="description" class="large-text" rows="3"></textarea>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="image">画像URL</label></th>
                    <td>
                        <input name="image" type="url" id="image" class="regular-text">
                        <p class="description">サイトのロゴやアイコンのURLを入力してください</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="target">リンクの開き方</label></th>
                    <td>
                        <select name="target" id="target">
                            <option value="_blank">新しいタブで開く</option>
                            <option value="_self">同じタブで開く</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="status">状態</label></th>
                    <td>
                        <select name="status" id="status">
                            <option value="active">有効</option>
                            <option value="inactive">無効</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="sort_order">並び順</label></th>
                    <td>
                        <input name="sort_order" type="number" id="sort_order" class="small-text" value="0">
                        <p class="description">数字が小さいほど前に表示されます</p>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <button type="submit" class="button button-primary">保存</button>
            </p>
        </form>
    </div>

    <style>
    .link-gallery-form .required {
        color: #d63638;
    }
    </style>
</div>
