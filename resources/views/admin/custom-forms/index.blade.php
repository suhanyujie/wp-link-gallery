<div class="wrap">
    <h1>カスタムフォーム管理</h1>

    <div class="card">
        <h2>リンク管理</h2>
        <p>友達リンクの追加、編集、削除を管理します。</p>
        <a href="<?php echo admin_url('admin.php?page=link-gallery'); ?>" class="button button-primary">リンク管理へ</a>
    </div>

    <div class="card" style="margin-top: 20px;">
        <h2>問い合わせダータ管理</h2>
        <p>ユーザーの問い合わせの管理。</p>
        <a href="<?php echo admin_url('admin.php?page=ask-question-form'); ?>" class="button button-primary">問い合わせダータ管理へ</a>
    </div>
</div>

<style>
.card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.card h2 {
    margin-top: 0;
    margin-bottom: 15px;
    font-size: 1.3em;
}

.card p {
    margin-bottom: 15px;
}
</style>
