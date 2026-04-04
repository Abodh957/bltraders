<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class MainCategory extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'image',
        'status',
    ];
    protected $dates = ['deleted_at'];

    public function fetchData($request, $columns) {
        $query =MainCategory::where('id', '!=', '');

        if (isset($request->from_date)) {
            $query->whereRaw('DATE_FORMAT(created_at, "%Y-%m-%d") >= "' . date("Y-m-d", strtotime($request->from_date)) . '"');
        }
        if (isset($request->end_date)) {
            $query->whereRaw('DATE_FORMAT(created_at, "%Y-%m-%d") <= "' . date("Y-m-d", strtotime($request->end_date)) . '"');
        }
        if (isset($request['search']['value']) && !empty($request['search']['value'])) {
            $searchValue = $request['search']['value'];

            $query->where(function ($q) use ($searchValue) {
                $q->where('name', 'like', '%' . $searchValue . '%');
            });
        }

        if (isset($request->order_column)) {
            $customers = $query->orderBy($columns[$request->order_column], $request->order_dir);
        } else {
            $customers = $query->orderBy('created_at', 'desc');
        }
        return $customers;
    }

    public function categories()
{
    return $this->hasMany(Category::class, 'main_category_id');
}

}
