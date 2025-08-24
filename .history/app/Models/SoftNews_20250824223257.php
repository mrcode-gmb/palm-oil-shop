<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoftNews extends Model
{
    use HasFactory;

    protected $fillable [
        'name_title',
        'new_content',
    ];
}
