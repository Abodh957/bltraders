<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    /** Pages the app links to by slug — these cannot be deleted from the admin. */
    public const PROTECTED_SLUGS = ['terms-and-conditions', 'privacy-policy'];

    public function isProtected(): bool
    {
        return in_array($this->slug, self::PROTECTED_SLUGS, true);
    }

    public function fetchData($request, $columns)
    {
        $query = Page::where('id', '!=', '');

        if (isset($request['search']['value']) && !empty($request['search']['value'])) {
            $search = $request['search']['value'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('slug', 'like', '%' . $search . '%');
            });
        }

        if (isset($request->order_column)) {
            $query->orderBy($columns[$request->order_column], $request->order_dir);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query;
    }
}
