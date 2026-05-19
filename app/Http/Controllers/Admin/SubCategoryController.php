<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Store;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SubCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:Sub-Category-Management', ['only' => ['index', 'store', 'create', 'edit', 'destroy', 'update']]);
        $this->Model      = new SubCategory;
        $this->uploadPath = 'uploads/admin/subCategory/';
        $this->columns    = ['id', 'store_id', 'category_id', 'sub_category', 'slug', 'image', 'description', 'status', 'created_at'];
    }

    public function index()
    {
        return view('admin.subCategories.index');
    }

    public function create()
    {
        $stores     = Store::where('status', 1)->orderBy('name')->get();
        $categories = Category::where('status', 1)->orderBy('name')->get();
        return view('admin.subCategories.create', compact('stores', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'sub_category' => 'required|string|max:255|unique:sub_categories,sub_category',
            'store_id'     => 'required|exists:stores,id',
            'category_id'  => 'required|exists:categories,id',
            'image'        => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'description'  => 'nullable|string',
        ]);

        $uploadPath = public_path($this->uploadPath);
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0775, true);
        }

        $imageName = time() . '.' . $request->image->extension();
        $request->image->move($uploadPath, $imageName);

        SubCategory::create([
            'store_id'    => $request->store_id,
            'category_id' => $request->category_id,
            'sub_category' => $request->sub_category,
            'slug'        => Str::slug($request->sub_category),
            'description' => $request->description,
            'image'       => $imageName,
            'status'      => 1,
        ]);

        return redirect()->route('sub-categories.index')->with('success', 'Sub Category created successfully.');
    }

    public function show(string $id)
    {
        $subCategory = SubCategory::with(['store', 'category'])->findOrFail($id);
        $subCategory->image_url = config('custom.public_path') . '/' . $this->uploadPath . $subCategory->image;
        return view('admin.subCategories.show', compact('subCategory'));
    }

    public function edit(string $id)
    {
        $stores      = Store::where('status', 1)->orderBy('name')->get();
        $categories  = Category::where('status', 1)->orderBy('name')->get();
        $subCategory = SubCategory::with(['store', 'category'])->findOrFail($id);
        $subCategory->image_url = config('custom.public_path') . '/' . $this->uploadPath . $subCategory->image;
        return view('admin.subCategories.edit', compact('subCategory', 'stores', 'categories'));
    }

    public function update(Request $request, SubCategory $subCategory)
    {
        $request->validate([
            'sub_category' => ['required', 'string', 'max:255', Rule::unique('sub_categories', 'sub_category')->ignore($subCategory->id)],
            'store_id'     => 'required|exists:stores,id',
            'category_id'  => 'required|exists:categories,id',
            'image'        => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'description'  => 'nullable|string',
        ]);

        $uploadPath = public_path($this->uploadPath);
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0775, true);
        }

        $imageName = $subCategory->image;
        if ($request->hasFile('image')) {
            if ($subCategory->image && file_exists($uploadPath . '/' . $subCategory->image)) {
                unlink($uploadPath . '/' . $subCategory->image);
            }
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move($uploadPath, $imageName);
        }

        $subCategory->update([
            'store_id'     => $request->store_id,
            'category_id'  => $request->category_id,
            'sub_category' => $request->sub_category,
            'slug'         => Str::slug($request->sub_category),
            'description'  => $request->description,
            'image'        => $imageName,
        ]);

        return redirect()->route('sub-categories.index')->with('success', 'Sub Category updated successfully.');
    }

    public function destroy(SubCategory $subCategory)
    {
        $uploadPath = public_path($this->uploadPath);
        if ($subCategory->image && file_exists($uploadPath . '/' . $subCategory->image)) {
            unlink($uploadPath . '/' . $subCategory->image);
        }
        $subCategory->delete();
        return response()->json(['success' => true, 'message' => 'Sub Category deleted successfully.']);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);

        $uploadPath    = public_path($this->uploadPath);
        $subCategories = SubCategory::whereIn('id', $request->ids)->get();

        foreach ($subCategories as $subCategory) {
            if ($subCategory->image && file_exists($uploadPath . '/' . $subCategory->image)) {
                unlink($uploadPath . '/' . $subCategory->image);
            }
            $subCategory->delete();
        }

        return response()->json(['success' => true, 'message' => 'Selected sub categories deleted successfully.']);
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
            $subCategories = $records->offset($request->start)->limit($request->length)->get();
        } else {
            $subCategories = $records->offset(0)->limit(count($total))->get();
        }

        $result = [];
        $i = 1;
        foreach ($subCategories as $value) {
            $data                 = [];
            $data['srno']         = $i++;
            $data['id']           = $value->id;
            $data['checkbox']     = '<input type="checkbox" class="row-checkbox" value="' . $value->id . '">';
            $data['sub_category'] = ucfirst($value->sub_category);
            $data['store']        = $value->store ? ucfirst($value->store->name) : '-';
            $data['category']     = $value->category ? ucfirst($value->category->name) : '-';
            $data['image']        = "<img class='avatar-image avatar-md bg-warning text-white' src='" . config('custom.public_path') . '/' . $this->uploadPath . $value->image . "' style='width:50px;height:50px;object-fit:cover;border-radius:4px;'/>";
            $data['description']  = $value->description ?? '-';
            $data['status']       = isActiveInactive($value->status, route('subCategories.statusChange'), $value->id);
            $data['created_at']   = dateFormat($value->created_at);

            $action  = '<div class="hstack gap-2 justify-content-end">';
            $action .= '<a href="' . route('sub-categories.show', $value->id) . '" class="avatar-text avatar-md" title="View"><i class="feather feather-eye"></i></a>';
            $action .= '<a href="' . route('sub-categories.edit', $value->id) . '" class="avatar-text avatar-md" title="Edit"><i class="feather feather-edit-3"></i></a>';
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
