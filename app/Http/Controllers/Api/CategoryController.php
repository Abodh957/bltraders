<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Support\StoreContext;

class CategoryController extends Controller
{
    private string $uploadPath = 'uploads/admin/Category/';

    public function index(Request $request)
    {
        $query = Category::with('store')->where('status', 1);

        // ?store_id= wins; otherwise the customer's selected store applies.
        StoreContext::apply($query, StoreContext::resolve($request));

        $categories = $query->orderBy('name')->get()->map(fn($c) => $this->format($c));

        return response()->json(['status' => true, 'data' => $categories]);
    }

    public function show(string $id)
    {
        $category = Category::with('store')->find($id);
        if (!$category) {
            return response()->json(['status' => false, 'message' => 'Category not found'], 404);
        }
        return response()->json(['status' => true, 'data' => $this->format($category)]);
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

        $category = Category::create([
            'store_id'    => $request->store_id,
            'name'        => $request->name,
            'slug'        => \Illuminate\Support\Str::slug($request->name),
            'description' => $request->description,
            'image'       => $imageName,
            'status'      => $request->status ?? 1,
        ]);

        return response()->json(['status' => true, 'message' => 'Category created successfully.', 'data' => $this->format($category->load('store'))], 201);
    }

    public function update(Request $request, string $id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['status' => false, 'message' => 'Category not found'], 404);
        }

        $request->validate([
            'name'     => ['sometimes', 'required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('categories', 'name')->ignore($id)],
            'store_id' => 'sometimes|required|exists:stores,id',
            'image'    => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'description' => 'nullable|string',
        ]);

        $uploadPath = public_path($this->uploadPath);
        $imageName  = $category->image;

        if ($request->hasFile('image')) {
            if ($category->image && file_exists($uploadPath . '/' . $category->image)) {
                unlink($uploadPath . '/' . $category->image);
            }
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0775, true);
            }
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move($uploadPath, $imageName);
        }

        $category->update([
            'store_id'    => $request->store_id    ?? $category->store_id,
            'name'        => $request->name        ?? $category->name,
            'slug'        => $request->name        ? \Illuminate\Support\Str::slug($request->name) : $category->slug,
            'description' => $request->description ?? $category->description,
            'image'       => $imageName,
            'status'      => $request->status      ?? $category->status,
        ]);

        return response()->json(['status' => true, 'message' => 'Category updated successfully.', 'data' => $this->format($category->fresh()->load('store'))]);
    }

    public function destroy(string $id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['status' => false, 'message' => 'Category not found'], 404);
        }

        $uploadPath = public_path($this->uploadPath);
        if ($category->image && file_exists($uploadPath . '/' . $category->image)) {
            unlink($uploadPath . '/' . $category->image);
        }

        $category->delete();
        return response()->json(['status' => true, 'message' => 'Category deleted successfully.']);
    }

    private function format(Category $category): array
    {
        return [
            'id'          => $category->id,
            'store_id'    => $category->store_id,
            'store'       => $category->store ? ['id' => $category->store->id, 'name' => $category->store->name, 'slug' => $category->store->slug] : null,
            'name'        => $category->name,
            'slug'        => $category->slug,
            'description' => $category->description,
            'image'       => url('public/' . $this->uploadPath . $category->image),
            'status'      => $category->status,
            'created_at'  => $category->created_at,
            'updated_at'  => $category->updated_at,
        ];
    }
}
