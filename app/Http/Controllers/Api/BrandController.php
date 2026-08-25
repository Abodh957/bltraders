<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use App\Support\StoreContext;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BrandController extends Controller
{
    private string $logoPath  = 'uploads/admin/Brand/logo/';
    private string $coverPath = 'uploads/admin/Brand/cover/';

    public function index(Request $request)
    {
        $query = Brand::where('status', 1);

        // Brands with a null store_id are global — shown in every store.
        StoreContext::apply($query, StoreContext::resolve($request), 'store_id', true);

        $brands = $query->orderBy('sort_order', 'asc')
            ->get()
            ->map(fn($b) => $this->format($b));

        return response()->json(['status' => true, 'data' => $brands]);
    }

    public function show(string $id)
    {
        $brand = Brand::find($id);
        if (!$brand) {
            return response()->json(['status' => false, 'message' => 'Brand not found'], 404);
        }
        return response()->json(['status' => true, 'data' => $this->format($brand)]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'             => 'required|string|max:150|unique:brands,name',
            'slug'             => 'nullable|string|max:160|unique:brands,slug',
            'logo'             => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'cover_image'      => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'description'      => 'nullable|string',
            'website_url'      => 'nullable|url|max:255',
            'meta_title'       => 'nullable|string|max:160',
            'meta_description' => 'nullable|string',
            'status'           => 'nullable|in:0,1',
            'sort_order'       => 'nullable|integer|min:0',
        ]);

        $slug      = $request->slug ? Str::slug($request->slug) : Str::slug($request->name);
        $logoName  = $this->uploadFile($request, 'logo',        $this->logoPath);
        $coverName = $this->uploadFile($request, 'cover_image', $this->coverPath);

        $brand = Brand::create([
            'name'             => $request->name,
            'slug'             => $slug,
            'logo'             => $logoName,
            'cover_image'      => $coverName,
            'description'      => $request->description,
            'website_url'      => $request->website_url,
            'meta_title'       => $request->meta_title,
            'meta_description' => $request->meta_description,
            'status'           => $request->status ?? 1,
            'sort_order'       => $request->sort_order ?? 0,
        ]);

        return response()->json(['status' => true, 'message' => 'Brand created successfully.', 'data' => $this->format($brand)], 201);
    }

    public function update(Request $request, string $id)
    {
        $brand = Brand::find($id);
        if (!$brand) {
            return response()->json(['status' => false, 'message' => 'Brand not found'], 404);
        }

        $request->validate([
            'name'             => ['sometimes', 'required', 'string', 'max:150', Rule::unique('brands', 'name')->ignore($brand->id)],
            'slug'             => ['nullable', 'string', 'max:160', Rule::unique('brands', 'slug')->ignore($brand->id)],
            'logo'             => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'cover_image'      => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'description'      => 'nullable|string',
            'website_url'      => 'nullable|url|max:255',
            'meta_title'       => 'nullable|string|max:160',
            'meta_description' => 'nullable|string',
            'status'           => 'nullable|in:0,1',
            'sort_order'       => 'nullable|integer|min:0',
        ]);

        $logoName  = $this->uploadFile($request, 'logo',        $this->logoPath,  $brand->logo);
        $coverName = $this->uploadFile($request, 'cover_image', $this->coverPath, $brand->cover_image);

        $slug = $request->slug
            ? Str::slug($request->slug)
            : ($request->name ? Str::slug($request->name) : $brand->slug);

        $brand->update([
            'name'             => $request->name             ?? $brand->name,
            'slug'             => $slug,
            'logo'             => $logoName,
            'cover_image'      => $coverName,
            'description'      => $request->description      ?? $brand->description,
            'website_url'      => $request->website_url      ?? $brand->website_url,
            'meta_title'       => $request->meta_title       ?? $brand->meta_title,
            'meta_description' => $request->meta_description ?? $brand->meta_description,
            'status'           => $request->status           ?? $brand->status,
            'sort_order'       => $request->sort_order       ?? $brand->sort_order,
        ]);

        return response()->json(['status' => true, 'message' => 'Brand updated successfully.', 'data' => $this->format($brand->fresh())]);
    }

    public function destroy(string $id)
    {
        $brand = Brand::find($id);
        if (!$brand) {
            return response()->json(['status' => false, 'message' => 'Brand not found'], 404);
        }

        $this->deleteFile($this->logoPath,  $brand->logo);
        $this->deleteFile($this->coverPath, $brand->cover_image);
        $brand->delete();

        return response()->json(['status' => true, 'message' => 'Brand deleted successfully.']);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);

        $brands = Brand::whereIn('id', $request->ids)->get();
        foreach ($brands as $brand) {
            $this->deleteFile($this->logoPath,  $brand->logo);
            $this->deleteFile($this->coverPath, $brand->cover_image);
            $brand->delete();
        }

        return response()->json(['status' => true, 'message' => 'Selected brands deleted successfully.']);
    }

    private function format(Brand $brand): array
    {
        return [
            'id'               => $brand->id,
            'name'             => $brand->name,
            'slug'             => $brand->slug,
            // APP_URL points at the app root while the files live under public/,
            // so the "public/" segment has to be included — same as banners,
            // categories and sub-categories.
            'logo'             => $brand->logo  ? url('public/' . $this->logoPath  . $brand->logo)  : null,
            'cover_image'      => $brand->cover_image ? url('public/' . $this->coverPath . $brand->cover_image) : null,
            'description'      => $brand->description,
            'website_url'      => $brand->website_url,
            'meta_title'       => $brand->meta_title,
            'meta_description' => $brand->meta_description,
            'status'           => $brand->status,
            'sort_order'       => $brand->sort_order,
            'created_at'       => $brand->created_at,
            'updated_at'       => $brand->updated_at,
        ];
    }

    private function uploadFile(Request $request, string $field, string $path, ?string $existing = null): ?string
    {
        if (!$request->hasFile($field)) {
            return $existing;
        }
        $dir = public_path($path);
        if (!file_exists($dir)) {
            mkdir($dir, 0775, true);
        }
        if ($existing && file_exists($dir . '/' . $existing)) {
            unlink($dir . '/' . $existing);
        }
        $name = time() . '_' . uniqid() . '.' . $request->file($field)->extension();
        $request->file($field)->move($dir, $name);
        return $name;
    }

    private function deleteFile(string $path, ?string $filename): void
    {
        if ($filename) {
            $full = public_path($path) . '/' . $filename;
            if (file_exists($full)) {
                unlink($full);
            }
        }
    }
}
