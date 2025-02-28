<?php

namespace LinkGallery\Models;

use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    protected $table = 'wp_link_gallery';
    protected $fillable = [
        'name',
        'url',
        'description',
        'image',
        'target',
        'status',
        'sort_order'
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeSorted($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }
}