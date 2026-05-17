<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brand extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function fetchData($request, $columns)
    {
        $query = Brand::where('id', '!=', '');

        if (!empty($request->from_date)) {
            $query->whereRaw('DATE_FORMAT(created_at, "%Y-%m-%d") >= "' . date('Y-m-d', strtotime($request->from_date)) . '"');
        }
        if (!empty($request->end_date)) {
            $query->whereRaw('DATE_FORMAT(created_at, "%Y-%m-%d") <= "' . date('Y-m-d', strtotime($request->end_date)) . '"');
        }
        if (!empty($request['search']['value'])) {
            $searchValue = $request['search']['value'];
            $query->where(function ($q) use ($searchValue) {
                $q->where('name', 'like', '%' . $searchValue . '%')
                  ->orWhere('slug', 'like', '%' . $searchValue . '%')
                  ->orWhere('description', 'like', '%' . $searchValue . '%');
            });
        }

        if (isset($request->order_column)) {
            $query->orderBy($columns[$request->order_column], $request->order_dir);
        } else {
            $query->orderBy('sort_order', 'asc');
        }

        return $query;
    }
}
