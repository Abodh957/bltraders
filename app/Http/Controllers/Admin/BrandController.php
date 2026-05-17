<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BrandController extends Controller
{
    public function __construct()
    {
        $this->Model      = new Brand;
        $this->logoPath   = 'uploads/admin/Brand/logo/';
        $this->coverPath  = 'uploads/admin/Brand/cover/';
        $this->columns    = [
            'id', 'name', 'slug', 'logo', 'cover_image',
            'description', 'website_url', 'status', 'sort_order', 'created_at',
        ];
    }

    public function index()
    {
        return view('admin.brands.index');
    }

    public function create()
    {
        return view('admin.brands.create');
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
            'status'           => 'required|in:0,1',
            'sort_order'       => 'nullable|integer|min:0',
        ]);

        $slug      = $request->slug ? Str::slug($request->slug) : Str::slug($request->name);
        $logoName  = $this->uploadFile($request, 'logo', $this->logoPath);
        $coverName = $this->uploadFile($request, 'cover_image', $this->coverPath);

        Brand::create([
            'name'             => $request->name,
            'slug'             => $slug,
            'logo'             => $logoName,
            'cover_image'      => $coverName,
            'description'      => $request->description,
            'website_url'      => $request->website_url,
            'meta_title'       => $request->meta_title,
            'meta_description' => $request->meta_description,
            'status'           => $request->status,
            'sort_order'       => $request->sort_order ?? 0,
        ]);

        return redirect()->route('brands.index')->with('success', 'Brand created successfully.');
    }

    public function show(string $id)
    {
        $brand = Brand::findOrFail($id);
        $brand->logo_url  = $brand->logo  ? '/' . $this->logoPath  . $brand->logo  : null;
        $brand->cover_url = $brand->cover_image ? '/' . $this->coverPath . $brand->cover_image : null;
        return view('admin.brands.show', compact('brand'));
    }

    public function edit(string $id)
    {
        $brand = Brand::findOrFail($id);
        $brand->logo_url  = $brand->logo  ? '/' . $this->logoPath  . $brand->logo  : null;
        $brand->cover_url = $brand->cover_image ? '/' . $this->coverPath . $brand->cover_image : null;
        return view('admin.brands.edit', compact('brand'));
    }

    public function update(Request $request, Brand $brand)
    {
        $request->validate([
            'name'             => ['required', 'string', 'max:150', Rule::unique('brands', 'name')->ignore($brand->id)],
            'slug'             => ['nullable', 'string', 'max:160', Rule::unique('brands', 'slug')->ignore($brand->id)],
            'logo'             => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'cover_image'      => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'description'      => 'nullable|string',
            'website_url'      => 'nullable|url|max:255',
            'meta_title'       => 'nullable|string|max:160',
            'meta_description' => 'nullable|string',
            'status'           => 'required|in:0,1',
            'sort_order'       => 'nullable|integer|min:0',
        ]);

        $slug      = $request->slug ? Str::slug($request->slug) : Str::slug($request->name);
        $logoName  = $this->uploadFile($request, 'logo',        $this->logoPath,  $brand->logo);
        $coverName = $this->uploadFile($request, 'cover_image', $this->coverPath, $brand->cover_image);

        $brand->update([
            'name'             => $request->name,
            'slug'             => $slug,
            'logo'             => $logoName,
            'cover_image'      => $coverName,
            'description'      => $request->description,
            'website_url'      => $request->website_url,
            'meta_title'       => $request->meta_title,
            'meta_description' => $request->meta_description,
            'status'           => $request->status,
            'sort_order'       => $request->sort_order ?? 0,
        ]);

        return redirect()->route('brands.index')->with('success', 'Brand updated successfully.');
    }

    public function destroy(Brand $brand)
    {
        $this->deleteFile($this->logoPath,  $brand->logo);
        $this->deleteFile($this->coverPath, $brand->cover_image);
        $brand->delete();
        return redirect()->route('brands.index')->with('success', 'Brand deleted successfully.');
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

        return response()->json(['success' => true, 'message' => 'Selected brands deleted successfully.']);
    }

    public function getData(Request $request)
    {
        if (isset($request->order[0]['column'])) {
            $request->order_column = $request->order[0]['column'];
            $request->order_dir    = $request->order[0]['dir'];
        }

        $records = $this->Model->fetchData($request, $this->columns);
        $total   = $records->get();
        $brands  = isset($request->start)
            ? $records->offset($request->start)->limit($request->length)->get()
            : $records->offset(0)->limit(count($total))->get();

        $result = [];
        $i = 1;
        foreach ($brands as $value) {
            $logoHtml = $value->logo
                ? "<img src='/" . $this->logoPath . $value->logo . "' style='width:50px;height:50px;object-fit:contain;border-radius:4px;' />"
                : '<span class="text-muted">-</span>';

            $action  = '<div class="hstack gap-2 justify-content-end">';
            $action .= '<a href="' . route('brands.show', $value->id) . '" class="avatar-text avatar-md" title="View"><i class="feather feather-eye"></i></a>';
            $action .= '<a href="' . route('brands.edit', $value->id) . '" class="avatar-text avatar-md" title="Edit"><i class="feather feather-edit-3"></i></a>';
            $action .= '<a href="javascript:void(0)" class="avatar-text avatar-md text-danger delete-btn" data-id="' . $value->id . '" title="Delete"><i class="feather feather-trash-2"></i></a>';
            $action .= '</div>';

            $result[] = [
                'srno'        => $i++,
                'id'          => $value->id,
                'checkbox'    => '<input type="checkbox" class="row-checkbox" value="' . $value->id . '">',
                'logo'        => $logoHtml,
                'name'        => ucfirst($value->name),
                'slug'        => $value->slug,
                'website_url' => $value->website_url ? '<a href="' . $value->website_url . '" target="_blank">' . $value->website_url . '</a>' : '-',
                'sort_order'  => $value->sort_order,
                'status'      => isActiveInactive($value->status, route('brands.statusChange'), $value->id),
                'created_at'  => dateFormat($value->created_at),
                'actions'     => $action,
            ];
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

    // ── helpers ──────────────────────────────────────────────────────────────

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
