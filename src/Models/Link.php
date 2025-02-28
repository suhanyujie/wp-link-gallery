<?php

namespace LinkGallery\Models;

class Link extends Model
{
    protected function setTable()
    {
        $this->table = $this->wpdb->prefix . 'link_gallery';
    }

    public static function active()
    {
        $instance = new static;
        $instance->where = "WHERE status = 'active'";
        return $instance;
    }

    public static function sorted()
    {
        $instance = new static;
        $instance->orderBy = "ORDER BY sort_order ASC";
        return $instance;
    }
}
