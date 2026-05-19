<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SubCategory;
use Illuminate\Http\Request;

class SubCategoryController extends Controller
{
    private string $uploadPath = 'uploads/admin/subCategory/';

    public function index(Request $request)
    {
        $query = SubCategory::with(['store', 'category'])->where('status', 1);

        if ($request->store_id) {
            $query->where('store_id', $request->store_id);
        }
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        $subCategories = $query->orderBy('sub_category')->get()->map(fn($s) => $this->format($s));

        return response()->json(['status' => true, 'data' => $subCategories]);
    }

    public function show(string $id)
    {
        $subCategory = SubCategory::with(['store', 'category'])->find($id);
        if (!$subCategory) {
            return response()->json(['status' => false, 'message' => 'Sub category not found'], 404);
        }
        return response()->json(['status' => true, 'data' => $this->format($subCategory)]);
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

        $subCategory = SubCategory::create([
            'store_id'     => $request->store_id,
            'category_id'  => $request->category_id,
            'sub_category' => $request->sub_category,
            'slug'         => \Illuminate\Support\Str::slug($request->sub_category),
            'description'  => $request->description,
            'image'        => $imageName,
            'status'       => $request->status ?? 1,
        ]);

        return response()->json(['status' => true, 'message' => 'Sub category created successfully.', 'data' => $this->format($subCategory->load(['store', 'category']))], 201);
    }

    public function update(Request $request, string $id)
    {
        $subCategory = SubCategory::find($id);
        if (!$subCategory) {
            return response()->json(['status' => false, 'message' => 'Sub category not found'], 404);
        }

        $request->validate([
            'sub_category' => ['sometimes', 'required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('sub_categories', 'sub_category')->ignore($id)],
            'store_id'     => 'sometimes|required|exists:stores,id',
            'category_id'  => 'sometimes|required|exists:categories,id',
            'image'        => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'description'  => 'nullable|string',
        ]);

        $uploadPath = public_path($this->uploadPath);
        $imageName  = $subCategory->image;

        if ($request->hasFile('image')) {
            if ($subCategory->image && file_exists($uploadPath . '/' . $subCategory->image)) {
                unlink($uploadPath . '/' . $subCategory->image);
            }
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0775, true);
            }
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move($uploadPath, $imageName);
        }

        $subCategory->update([
            'store_id'     => $request->store_id    ?? $subCategory->store_id,
            'category_id'  => $request->category_id ?? $subCategory->category_id,
            'sub_category' => $request->sub_category ?? $subCategory->sub_category,
            'slug'         => $request->sub_category ? \Illuminate\Support\Str::slug($request->sub_category) : $subCategory->slug,
            'description'  => $request->description ?? $subCategory->description,
            'image'        => $imageName,
            'status'       => $request->status ?? $subCategory->status,
        ]);

        return response()->json(['status' => true, 'message' => 'Sub category updated successfully.', 'data' => $this->format($subCategory->fresh()->load(['store', 'category']))]);
    }

    public function destroy(string $id)
    {
        $subCategory = SubCategory::find($id);
        if (!$subCategory) {
            return response()->json(['status' => false, 'message' => 'Sub category not found'], 404);
        }

        $uploadPath = public_path($this->uploadPath);
        if ($subCategory->image && file_exists($uploadPath . '/' . $subCategory->image)) {
            unlink($uploadPath . '/' . $subCategory->image);
        }

        $subCategory->delete();
        return response()->json(['status' => true, 'message' => 'Sub category deleted successfully.']);
    }

    private function format(SubCategory $subCategory): array
    {
        return [
            'id'           => $subCategory->id,
            'store_id'     => $subCategory->store_id,
            'store'        => $subCategory->store ? ['id' => $subCategory->store->id, 'name' => $subCategory->store->name, 'slug' => $subCategory->store->slug] : null,
            'category_id'  => $subCategory->category_id,
            'category'     => $subCategory->category ? ['id' => $subCategory->category->id, 'name' => $subCategory->category->name, 'slug' => $subCategory->category->slug] : null,
            'name'         => $subCategory->sub_category,
            'slug'         => $subCategory->slug,
            'description'  => $subCategory->description,
            'image'        => url('public/' . $this->uploadPath . $subCategory->image),
            'status'       => $subCategory->status,
            'created_at'   => $subCategory->created_at,
            'updated_at'   => $subCategory->updated_at,
        ];
    }
}
