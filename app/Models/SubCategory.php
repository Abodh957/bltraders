<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    use HasFactory;
    protected $guarded=[];
    public function fetchData($request, $columns)
    {

        $query = SubCategory::where('id', '!=', '');

        if (isset($request->from_date)) {
            $query->whereRaw('DATE_FORMAT(created_at, "%Y-%m-%d") >= "' . date("Y-m-d", strtotime($request->from_date)) . '"');
        }
        if (isset($request->end_date)) {
            $query->whereRaw('DATE_FORMAT(created_at, "%Y-%m-%d") <= "' . date("Y-m-d", strtotime($request->end_date)) . '"');
        }
        if (isset($request['search']['value']) && !empty($request['search']['value'])) {
            $searchValue = $request['search']['value'];

            $query->where(function ($q) use ($searchValue) {
                $q->where('sub_category', 'like', '%' . $searchValue . '%');
            });
        }

        if (isset($request->order_column)) {
            $subCategories = $query->orderBy($columns[$request->order_column], $request->order_dir);
        } else {
            $subCategories = $query->orderBy('created_at', 'desc');
        }
        return $subCategories;


    }
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
