<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ShopController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:Shop-Management', ['only' => ['index', 'store', 'create', 'edit', 'destroy', 'update', 'getData', 'statusChange']]);
        $this->Model = new Shop;
        $this->uploadPath = 'uploads/admin/Shop/';
        $this->columns = [
            "id",
            'shop_name',
            'user_id',
            'city',
            'state',
            'country',
            'status',
            'created_at',
        ];
    }

    public function index()
    {
        return view('admin.shops.index');
    }

    public function create()
    {
        $users = User::orderBy('first_name')->get();
        return view('admin.shops.create', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id'       => 'required|exists:users,id',
            'shop_name'     => 'required|string|max:255',
            'city'          => 'nullable|string|max:100',
            'state'         => 'nullable|string|max:100',
            'country'       => 'nullable|string|max:100',
            'pincode'       => 'nullable|string|max:20',
            'shop_address'  => 'nullable|string',
            'status'        => 'required|in:pending,approved,rejected',
            'shop_document' => 'nullable|image|mimes:jpeg,png,jpg,gif,pdf|max:2048',
        ]);

        $documentName = null;
        if ($request->hasFile('shop_document')) {
            $uploadPath = public_path($this->uploadPath);
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0775, true);
            }
            $documentName = time() . '.' . $request->shop_document->extension();
            $request->shop_document->move($uploadPath, $documentName);
        }

        Shop::create([
            'user_id'       => $request->user_id,
            'shop_name'     => $request->shop_name,
            'city'          => $request->city,
            'state'         => $request->state,
            'country'       => $request->country,
            'pincode'       => $request->pincode,
            'shop_address'  => $request->shop_address,
            'status'        => $request->status,
            'shop_document' => $documentName,
        ]);

        return redirect()->route('user-shops.index')->with('success', 'Shop created successfully.');
    }

    public function edit(string $id)
    {
        $shop  = Shop::findOrFail($id);
        $users = User::orderBy('first_name')->get();
        return view('admin.shops.edit', compact('shop', 'users'));
    }

    public function update(Request $request, string $id)
    {
        $shop = Shop::findOrFail($id);

        $request->validate([
            'user_id'       => 'required|exists:users,id',
            'shop_name'     => 'required|string|max:255',
            'city'          => 'nullable|string|max:100',
            'state'         => 'nullable|string|max:100',
            'country'       => 'nullable|string|max:100',
            'pincode'       => 'nullable|string|max:20',
            'shop_address'  => 'nullable|string',
            'status'        => 'required|in:pending,approved,rejected',
            'shop_document' => 'nullable|image|mimes:jpeg,png,jpg,gif,pdf|max:2048',
        ]);

        $data = $request->only([
            'user_id', 'shop_name', 'city', 'state', 'country',
            'pincode', 'shop_address', 'status',
        ]);

        if ($request->hasFile('shop_document')) {
            $uploadPath = public_path($this->uploadPath);
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0775, true);
            }
            if ($shop->shop_document) {
                $oldPath = $uploadPath . '/' . $shop->shop_document;
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
            $documentName = time() . '.' . $request->shop_document->extension();
            $request->shop_document->move($uploadPath, $documentName);
            $data['shop_document'] = $documentName;
        }

        $shop->update($data);

        return redirect()->route('user-shops.index')->with('success', 'Shop updated successfully.');
    }

    public function destroy(string $id)
    {
        $shop = Shop::findOrFail($id);
        $shop->delete();
        return redirect()->route('user-shops.index')->with('success', 'Shop deleted successfully.');
    }

    public function getData(Request $request)
    {
        if (isset($request->order[0]['column'])) {
            $request->order_column = $request->order[0]['column'];
            $request->order_dir    = $request->order[0]['dir'];
        }

        $records = $this->Model->fetchData($request, $this->columns);
        $total   = $records->get();
        if (isset($request->start)) {
            $rows = $records->offset($request->start)->limit($request->length)->get();
        } else {
            $rows = $records->offset($request->start)->limit(count($total))->get();
        }

        $result = [];
        $i = 1;
        foreach ($rows as $value) {
            $data = [];
            $data['srno']        = $i++;
            $data['id']          = $value->id;
            $data['shop_name']   = ucfirst($value->shop_name);
            $data['user_number'] = $value->user ? $value->user->phone_no : '-';
            $data['city']        = $value->city ?: '-';
            $data['state']       = $value->state ?: '-';
            $data['country']     = $value->country ?: '-';
            $data['status']      = $this->renderStatusToggle($value->status, $value->id);
            $data['created_at']  = dateFormat($value->created_at);
            $data['actions']     = actions([
                'edit'   => route('user-shops.edit', $value->id),
                'view'   => '',
                'delete' => '',
            ]);
            $result[] = $data;
        }

        return json_encode([
            'data'            => $result,
            'recordsTotal'    => count($total),
            'recordsFiltered' => count($total),
        ]);
    }

    public function statusChange(Request $request)
    {
        $request->validate([
            'id'     => 'required|exists:shops,id',
            'status' => 'required|in:0,1',
        ]);

        $shop = Shop::find($request->input('id'));
        if (!$shop) {
            return response()->json(['success' => false, 'message' => 'Record not found.'], 404);
        }

        $shop->status = $request->input('status') == 1 ? 'approved' : 'rejected';
        $shop->save();

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully.',
        ]);
    }

    private function renderStatusToggle($status, $id)
    {
        $route     = route('user-shops.statusChange');
        $isChecked = $status === 'approved' ? 'checked' : '';
        $label     = $status === 'approved' ? 'Enabled' : ($status === 'rejected' ? 'Disabled' : ucfirst($status));

        return '
        <div class="form-check form-switch form-switch-sm">
            <input class="form-check-input c-pointer statusChange" type="checkbox" id="formSwitch' . $id . '" ' . $isChecked . ' data-id="' . $id . '" data-url="' . $route . '">
            <label class="form-check-label fw-500 text-dark c-pointer" for="formSwitch' . $id . '">
                ' . $label . '
            </label>
        </div>';
    }
}
