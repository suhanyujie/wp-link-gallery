<div class="wrap">
    <h1 class="wp-heading-inline">新規リンク追加</h1>
    <a href="<?php echo admin_url('admin.php?page=link-gallery'); ?>" class="page-title-action">リンク一覧に戻る</a>

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