<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupportContact extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function fetchData($request, $columns)
    {
        $query = SupportContact::where('id', '!=', '');

        if (isset($request['search']['value']) && !empty($request['search']['value'])) {
            $search = $request['search']['value'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('phone', 'like', '%' . $search . '%')
                  ->orWhere('designation', 'like', '%' . $search . '%');
            });
        }

        if (isset($request->order_column)) {
            $query->orderBy($columns[$request->order_column], $request->order_dir);
        } else {
            $query->orderBy('sort_order')->orderBy('created_at', 'desc');
        }

        return $query;
    }
}
