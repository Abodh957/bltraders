<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_colors');
    }

    public function fetchData($request, $columns)
    {
        $query = Color::where('id', '!=', '');

        if (isset($request['search']['value']) && !empty($request['search']['value'])) {
            $searchValue = $request['search']['value'];
            $query->where(function ($q) use ($searchValue) {
                $q->where('name', 'like', '%' . $searchValue . '%')
                  ->orWhere('hex_code', 'like', '%' . $searchValue . '%');
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
