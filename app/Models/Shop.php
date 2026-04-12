<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    protected $table = "shops";
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function fetchData($request, $columns)
    {
        $query = Shop::with('user')->where('shops.id', '!=', '');

        if (isset($request->from_date)) {
            $query->whereRaw('DATE_FORMAT(shops.created_at, "%Y-%m-%d") >= "' . date("Y-m-d", strtotime($request->from_date)) . '"');
        }
        if (isset($request->end_date)) {
            $query->whereRaw('DATE_FORMAT(shops.created_at, "%Y-%m-%d") <= "' . date("Y-m-d", strtotime($request->end_date)) . '"');
        }

        if (isset($request['search']['value']) && !empty($request['search']['value'])) {
            $searchValue = $request['search']['value'];

            $query->where(function ($q) use ($searchValue) {
                $q->where('shop_name', 'like', '%' . $searchValue . '%')
                    ->orWhere('city', 'like', '%' . $searchValue . '%')
                    ->orWhere('state', 'like', '%' . $searchValue . '%')
                    ->orWhere('country', 'like', '%' . $searchValue . '%')
                    ->orWhereHas('user', function ($q2) use ($searchValue) {
                        $q2->where('phone_no', 'like', '%' . $searchValue . '%');
                    });
            });
        }

        if (isset($request->order_column)) {
            $query->orderBy($columns[$request->order_column], $request->order_dir);
        } else {
            $query->orderBy('shops.created_at', 'desc');
        }
        return $query;
    }
}
