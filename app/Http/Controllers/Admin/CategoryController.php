<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:Category-Management', ['only' => ['index', 'store', 'create', 'edit', 'destroy', 'update']]);
        $this->Model      = new Category;
        $this->uploadPath = 'uploads/admin/Category/';
        $this->columns    = ['id', 'store_id', 'name', 'slug', 'image', 'description', 'status', 'created_at'];
    }

    public function index()
    {
        return view('admin.categories.index');
    }

    public function create()
    {
        $stores = Store::where('status', 1)->orderBy('name')->get();
        return view('admin.categories.create', compact('stores'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255|unique:categories,name',
            'store_id' => 'required|exists:stores,id',
            'image'    => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'description' => 'nullable|string',
        ]);

        $uploadPath = public_path($this->uploadPath);
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0775, true);
        }

        $imageName = time() . '.' . $request->image->extension();
        $request->image->move($uploadPath, $imageName);

        Category::create([
            'store_id'    => $request->store_id,
            'name'        => $request->name,
            'slug'        => Str::slug($request->name),
            'description' => $request->description,
            'image'       => $imageName,
            'status'      => 1,
        ]);

        return redirect()->route('categories.index')->with('success', 'Category created successfully.');
    }

    public function show(string $id)
    {
        $category = Category::with('store')->findOrFail($id);
        $category->image_url = config('custom.public_path') . '/' . $this->uploadPath . $category->image;
        return view('admin.categories.show', compact('category'));
    }

    public function edit(string $id)
    {
        $stores   = Store::where('status', 1)->orderBy('name')->get();
        $category = Category::with('store')->findOrFail($id);
        $category->image_url = config('custom.public_path') . '/' . $this->uploadPath . $category->image;
        return view('admin.categories.edit', compact('category', 'stores'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->ignore($category->id)],
            'store_id' => 'required|exists:stores,id',
            'image'    => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'description' => 'nullable|string',
        ]);

        $uploadPath = public_path($this->uploadPath);
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0775, true);
        }

        $imageName = $category->image;
        if ($request->hasFile('image')) {
            if ($category->image && file_exists($uploadPath . '/' . $category->image)) {
                unlink($uploadPath . '/' . $category->image);
            }
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move($uploadPath, $imageName);
        }

        $category->update([
            'store_id'    => $request->store_id,
            'name'        => $request->name,
            'slug'        => Str::slug($request->name),
            'description' => $request->description,
            'image'       => $imageName,
        ]);

        return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        $uploadPath = public_path($this->uploadPath);
        if ($category->image && file_exists($uploadPath . '/' . $category->image)) {
            unlink($uploadPath . '/' . $category->image);
        }
        $category->delete();
        return response()->json(['success' => true, 'message' => 'Category deleted successfully.']);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);

        $uploadPath  = public_path($this->uploadPath);
        $categories  = Category::whereIn('id', $request->ids)->get();

        foreach ($categories as $category) {
            if ($category->image && file_exists($uploadPath . '/' . $category->image)) {
                unlink($uploadPath . '/' . $category->image);
            }
            $category->delete();
        }

        return response()->json(['success' => true, 'message' => 'Selected categories deleted successfully.']);
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
            $categories = $records->offset($request->start)->limit($request->length)->get();
        } else {
            $categories = $records->offset(0)->limit(count($total))->get();
        }

        $result = [];
        $i = 1;
        foreach ($categories as $value) {
            $data               = [];
            $data['srno']       = $i++;
            $data['id']         = $value->id;
            $data['checkbox']   = '<input type="checkbox" class="row-checkbox" value="' . $value->id . '">';
            $data['name']       = ucfirst($value->name);
            $data['store']      = $value->store ? ucfirst($value->store->name) : '-';
            $data['image']      = "<img class='avatar-image avatar-md bg-warning text-white' src='" . config('custom.public_path') . '/' . $this->uploadPath . $value->image . "' style='width:50px;height:50px;object-fit:cover;border-radius:4px;'/>";
            $data['description'] = $value->description ?? '-';
            $data['status']     = isActiveInactive($value->status, route('categories.statusChange'), $value->id);
            $data['created_at'] = dateFormat($value->created_at);

            $action  = '<div class="hstack gap-2 justify-content-end">';
            $action .= '<a href="' . route('categories.show', $value->id) . '" class="avatar-text avatar-md" title="View"><i class="feather feather-eye"></i></a>';
            $action .= '<a href="' . route('categories.edit', $value->id) . '" class="avatar-text avatar-md" title="Edit"><i class="feather feather-edit-3"></i></a>';
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
