<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Shop;
use Laravel\Sanctum\HasApiTokens;
class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, HasRoles;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function fetchCustomerData($request, $columns)
    {
        $query = User::query()
            ->leftJoin('shops', 'shops.user_id', '=', 'users.id')
            ->select([
                'users.id',
                'users.phone_no',
                'users.created_at',
                'shops.id as shop_id',
                'shops.shop_name',
                'shops.city',
                'shops.state',
                'shops.pincode',
                'shops.status as shop_status',
            ]);

        if (!empty($request->admin_ids)) {
            $query->whereNotIn('users.id', $request->admin_ids);
        }
        if (isset($request->from_date)) {
            $query->whereRaw('DATE_FORMAT(users.created_at, "%Y-%m-%d") >= "' . date("Y-m-d", strtotime($request->from_date)) . '"');
        }
        if (isset($request->end_date)) {
            $query->whereRaw('DATE_FORMAT(users.created_at, "%Y-%m-%d") <= "' . date("Y-m-d", strtotime($request->end_date)) . '"');
        }
        if (isset($request['search']['value']) && !empty($request['search']['value'])) {
            $searchValue = $request['search']['value'];

            $query->where(function ($q) use ($searchValue) {
                $q->where('users.phone_no', 'like', '%' . $searchValue . '%')
                    ->orWhere('shops.shop_name', 'like', '%' . $searchValue . '%')
                    ->orWhere('shops.city', 'like', '%' . $searchValue . '%')
                    ->orWhere('shops.state', 'like', '%' . $searchValue . '%')
                    ->orWhere('shops.pincode', 'like', '%' . $searchValue . '%');
            });
        }

        if (isset($request->order_column)) {
            $customers = $query->orderBy($columns[$request->order_column], $request->order_dir);
        } else {
            $customers = $query->orderBy('users.created_at', 'desc');
        }
        return $customers;
    }
    public function shops()
    {
        return $this->hasMany(Shop::class, 'user_id');
    }
}
