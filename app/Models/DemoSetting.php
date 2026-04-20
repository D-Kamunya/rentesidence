<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class DemoSetting extends Model
{
    protected $fillable = [
        'demo_login_url',
        'demo_email', 
        'demo_password',
        'demo_notes',
    ];

    // Always work with the single settings row
    public static function current(): self
    {
        return static::firstOrCreate([], [
            'demo_login_url' => '',
            'demo_email'     => '',
            'demo_password'  => '',
            'demo_notes'     => '',
        ]);
    }
}