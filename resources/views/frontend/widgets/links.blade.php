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
