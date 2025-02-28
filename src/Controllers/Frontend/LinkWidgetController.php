<?php

namespace LinkGallery\Controllers\Frontend;

use LinkGallery\Models\Link;
use WP_Widget;

class LinkWidgetController extends WP_Widget
{
    public function __construct()
    {
        parent::__construct(
            'link_gallery_widget',
            '友情链接',
            ['description' => '在侧边栏显示友情链接列表']
        );

        add_action('widgets_init', function() {
            register_widget(self::class);
        });
    }

    public function widget($args, $instance)
    {
        $title = apply_filters('widget_title', $instance['title'] ?? '');
        $limit = intval($instance['limit'] ?? 10);

        $links = Link::active()
            ->sorted()
            ->limit($limit)
            ->get();

        echo $args['before_widget'];
        if (!empty($title)) {
            echo $args['before_title'] . $title . $args['after_title'];
        }

        view('frontend.widgets.links', compact('links'));

        echo $args['after_widget'];
    }

    public function form($instance)
    {
        $title = $instance['title'] ?? '友情链接';
        $limit = $instance['limit'] ?? 10;
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">标题：</label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
                   name="<?php echo $this->get_field_name('title'); ?>"
                   type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('limit'); ?>">显示数量：</label>
            <input class="widefat" id="<?php echo $this->get_field_id('limit'); ?>"
                   name="<?php echo $this->get_field_name('limit'); ?>"
                   type="number" min="1" max="50"
                   value="<?php echo esc_attr($limit); ?>">
        </p>
        <?php
    }

    public function update($new_instance, $old_instance)
    {
        $instance = [];
        $instance['title'] = sanitize_text_field($new_instance['title'] ?? '');
        $instance['limit'] = intval($new_instance['limit'] ?? 10);
        return $instance;
    }
}