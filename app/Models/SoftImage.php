<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoftImage extends Model
{
    use HasFactory;

    protected $fillable = [
        "name",
        "image_path",
    ];
    // include this field in API response
    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        return asset('storage/' . $this->image_path);
    }
}
