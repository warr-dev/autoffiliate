<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'product_title',
        'product_description',
        'product_price',
        'shop_name',
        'affiliate_url',
        'canonical_url',
        'caption',
        'media_files',
        'status',
        'facebook_post_id',
        'facebook_post_url',
        'tags',
    ];

    protected $casts = [
        'media_files' => 'array',
    ];
}
