<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use HasFactory;

    protected $guarded = [];

    /** Where product images are stored, relative to public/. */
    public const UPLOAD_PATH = 'uploads/admin/products/';

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Absolute URL of the image. APP_URL points at the app root while the
     * files live under public/, so the "public/" segment has to be included.
     */
    public function getUrlAttribute(): ?string
    {
        return $this->image_path ? self::urlFor($this->image_path) : null;
    }

    public static function urlFor(?string $imagePath): ?string
    {
        return $imagePath ? url('public/' . self::UPLOAD_PATH . $imagePath) : null;
    }
}
