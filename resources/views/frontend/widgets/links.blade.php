<div class="link-gallery-widget">
    <?php if (!empty($links) && count($links) > 0): ?>
        <ul class="link-gallery-list">
            <?php foreach ($links as $link): ?>
                <li class="link-gallery-item">
                    <a href="<?php echo esc_url($link->url); ?>" target="<?php echo esc_attr($link->target); ?>" title="<?php echo esc_attr($link->description); ?>">
                        <?php if ($link->image): ?>
                            <img src="<?php echo esc_url($link->image); ?>" alt="<?php echo esc_attr($link->name); ?>" class="link-gallery-image">
                        <?php endif; ?>
                        <span class="link-gallery-name"><?php echo esc_html($link->name); ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p class="link-gallery-empty">リンクはありません</p>
    <?php endif; ?>
</div>

<style>
.link-gallery-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.link-gallery-item {
    margin-bottom: 10px;
}

.link-gallery-item a {
    display: flex;
    align-items: center;
    text-decoration: none;
    background-color: #c0e4e4;
    padding: 10px;
    border-radius: 5px;
    transition: background-color 0.2s;
}

.link-gallery-item a:hover {
    background-color: #daf0f0;
}

.link-gallery-image {
    width: 24px;
    height: 24px;
    margin-right: 10px;
    border-radius: 3px;
}

.link-gallery-name {
    color: #333;
}
</style>
