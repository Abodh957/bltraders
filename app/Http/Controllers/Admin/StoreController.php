<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreController extends Controller
{
    public function __construct()
    {
        $this->Model   = new Store;
        $this->columns = ['id', 'name', 'slug', 'status', 'created_at'];
    }

    public function index()
    {
        return view('admin.stores.index');
    }

    public function create()
    {
        return view('admin.stores.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'   => 'required|string|max:100|unique:stores,name',
            'status' => 'required|in:0,1',
        ]);

        Store::create([
            'name'   => $request->name,
            'slug'   => Str::slug($request->name),
            'status' => $request->status,
        ]);

        return redirect()->route('stores.index')->with('success', 'Store created successfully.');
    }

    public function show(string $id)
    {
        $store = Store::withCount(['categories', 'subCategories'])->findOrFail($id);
        return view('admin.stores.show', compact('store'));
    }

    public function edit(string $id)
    {
        $store = Store::findOrFail($id);
        return view('admin.stores.edit', compact('store'));
    }

    public function update(Request $request, Store $store)
    {
        $request->validate([
            'name'   => ['required', 'string', 'max:100', Rule::unique('stores', 'name')->ignore($store->id)],
            'status' => 'required|in:0,1',
        ]);

        $store->update([
            'name'   => $request->name,
            'slug'   => Str::slug($request->name),
            'status' => $request->status,
        ]);

        return redirect()->route('stores.index')->with('success', 'Store updated successfully.');
    }

    public function destroy(Store $store)
    {
        $store->delete();
        return response()->json(['success' => true, 'message' => 'Store deleted successfully.']);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);

        Store::whereIn('id', $request->ids)->delete();

        return response()->json(['success' => true, 'message' => 'Selected stores deleted successfully.']);
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
            $stores = $records->offset($request->start)->limit($request->length)->get();
        } else {
            $stores = $records->offset(0)->limit(count($total))->get();
        }

        $result = [];
        $i = 1;
        foreach ($stores as $value) {
            $data             = [];
            $data['srno']     = $i++;
            $data['id']       = $value->id;
            $data['checkbox'] = '<input type="checkbox" class="row-checkbox" value="' . $value->id . '">';
            $data['name']     = ucfirst($value->name);
            $data['slug']     = $value->slug;
            $data['status']   = isActiveInactive($value->status, route('stores.statusChange'), $value->id);
            $data['created_at'] = dateFormat($value->created_at);

            $action  = '<div class="hstack gap-2 justify-content-end">';
            $action .= '<a href="' . route('stores.show', $value->id) . '" class="avatar-text avatar-md" title="View"><i class="feather feather-eye"></i></a>';
            $action .= '<a href="' . route('stores.edit', $value->id) . '" class="avatar-text avatar-md" title="Edit"><i class="feather feather-edit-3"></i></a>';
            $action .= '<a href="javascript:void(0)" class="avatar-text avatar-md text-danger delete-btn" data-id="' . $value->id . '" title="Delete"><i class="feather feather-trash-2"></i></a>';
            $action .= '</div>';
            $data['actions'] = $action;

            $result[] = $data;
        }

        return response()->json([
            'data'            => $result,
            'recordsTotal'    => count($total),
            'recordsFiltered' => count($total),
        ]);
    }

    public function statusChange(Request $request)
    {
        return statusChange($request, $this->Model);
    }
}
