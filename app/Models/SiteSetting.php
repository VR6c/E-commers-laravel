<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    use HasFactory;

    protected $table = 'site_settings';

    protected $fillable = [
        'site_name',
        'tagline',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'logo',
        'favicon',
        'contact_email',
        'contact_phone',
        'address',
        'footer_text',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
