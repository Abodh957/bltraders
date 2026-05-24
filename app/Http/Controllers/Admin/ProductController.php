<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductSpecification;
use App\Models\Store;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    private string $imagePath = 'uploads/admin/products/';

    public function __construct()
    {
        $this->columns = [
            'id', 'name', 'slug', 'price', 'stock', 'status', 'created_at',
        ];
    }

    public function index()
    {
        return view('admin.products.index');
    }

    public function create()
    {
        $stores     = Store::where('status', 1)->get();
        $colors     = Color::where('status', 1)->orderBy('name')->get();
        $categories = collect();
        $subCategories = collect();
        return view('admin.products.create', compact('stores', 'colors', 'categories', 'subCategories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'store_id'       => 'required|exists:stores,id',
            'category_id'    => 'nullable|exists:categories,id',
            'sub_category_id'=> 'nullable|exists:sub_categories,id',
            'price'          => 'required|numeric|min:0',
            'sale_price'     => 'nullable|numeric|min:0',
            'stock'          => 'required|integer|min:0',
            'sku'            => 'nullable|string|max:100|unique:products,sku',
            'is_gst_paid'    => 'nullable|boolean',
            'gst_percentage' => 'nullable|numeric|min:0|max:100',
            'status'         => 'required|in:0,1',
            'is_featured'    => 'nullable|boolean',
            'description'    => 'nullable|string',
            'images.*'       => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:3072',
            'colors'         => 'nullable|array',
            'colors.*'       => 'exists:colors,id',
            'spec_key'       => 'nullable|array',
            'spec_key.*'     => 'nullable|string|max:100',
            'spec_value'     => 'nullable|array',
            'spec_value.*'   => 'nullable|string|max:255',
        ]);

        try {
            $slug = Str::slug($request->name);
            $base = $slug;
            $i    = 1;
            while (Product::where('slug', $slug)->exists()) {
                $slug = $base . '-' . $i++;
            }

            $product = Product::create([
                'name'           => $request->name,
                'slug'           => $slug,
                'store_id'       => $request->store_id,
                'category_id'    => $request->category_id,
                'sub_category_id'=> $request->sub_category_id,
                'description'    => $request->description,
                'price'          => $request->price,
                'sale_price'     => $request->sale_price,
                'stock'          => $request->stock,
                'sku'            => $request->sku,
                'is_gst_paid'    => $request->boolean('is_gst_paid'),
                'gst_percentage' => $request->gst_percentage,
                'status'         => $request->status,
                'is_featured'    => $request->boolean('is_featured'),
                'detail'         => $request->description ?? '',
            ]);

            if ($request->hasFile('images')) {
                $dir = public_path($this->imagePath);
                if (!file_exists($dir)) {
                    mkdir($dir, 0775, true);
                }
                foreach ($request->file('images') as $index => $file) {
                    $name = time() . '_' . uniqid() . '.' . $file->extension();
                    $file->move($dir, $name);
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => $name,
                        'is_primary' => $index === 0,
                        'sort_order' => $index,
                    ]);
                }
            }

            $product->colors()->sync($request->colors ?? []);

            if ($request->has('spec_key')) {
                foreach ($request->spec_key as $i => $key) {
                    $key = trim($key);
                    $val = trim($request->spec_value[$i] ?? '');
                    if ($key !== '' && $val !== '') {
                        ProductSpecification::create([
                            'product_id' => $product->id,
                            'spec_key'   => $key,
                            'spec_value' => $val,
                            'sort_order' => $i,
                        ]);
                    }
                }
            }

            return redirect()->route('products.index')->with('success', 'Product created successfully.');

        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    public function edit(string $id)
    {
        $product       = Product::with(['images', 'specifications', 'colors'])->findOrFail($id);
        $stores        = Store::where('status', 1)->get();
        $colors        = Color::where('status', 1)->orderBy('name')->get();
        $categories    = Category::where('store_id', $product->store_id)->get();
        $subCategories = SubCategory::where('category_id', $product->category_id)->get();
        $selectedColors = $product->colors->pluck('id')->toArray();

        return view('admin.products.edit', compact(
            'product', 'stores', 'colors', 'categories', 'subCategories', 'selectedColors'
        ));
    }

    public function update(Request $request, string $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'name'           => 'required|string|max:255',
            'store_id'       => 'required|exists:stores,id',
            'category_id'    => 'nullable|exists:categories,id',
            'sub_category_id'=> 'nullable|exists:sub_categories,id',
            'price'          => 'required|numeric|min:0',
            'sale_price'     => 'nullable|numeric|min:0',
            'stock'          => 'required|integer|min:0',
            'sku'            => ['nullable', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($id)],
            'is_gst_paid'    => 'nullable|boolean',
            'gst_percentage' => 'nullable|numeric|min:0|max:100',
            'status'         => 'required|in:0,1',
            'is_featured'    => 'nullable|boolean',
            'description'    => 'nullable|string',
            'images.*'       => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:3072',
            'colors'         => 'nullable|array',
            'colors.*'       => 'exists:colors,id',
            'spec_key'       => 'nullable|array',
            'spec_key.*'     => 'nullable|string|max:100',
            'spec_value'     => 'nullable|array',
            'spec_value.*'   => 'nullable|string|max:255',
        ]);

        try {
            $product->update([
                'name'           => $request->name,
                'store_id'       => $request->store_id,
                'category_id'    => $request->category_id,
                'sub_category_id'=> $request->sub_category_id,
                'description'    => $request->description,
                'price'          => $request->price,
                'sale_price'     => $request->sale_price,
                'stock'          => $request->stock,
                'sku'            => $request->sku,
                'is_gst_paid'    => $request->boolean('is_gst_paid'),
                'gst_percentage' => $request->gst_percentage,
                'status'         => $request->status,
                'is_featured'    => $request->boolean('is_featured'),
                'detail'         => $request->description ?? '',
            ]);

            if ($request->hasFile('images')) {
                $dir = public_path($this->imagePath);
                if (!file_exists($dir)) {
                    mkdir($dir, 0775, true);
                }
                $lastOrder = $product->images()->max('sort_order') ?? -1;
                $isPrimary = $product->images()->count() === 0;
                foreach ($request->file('images') as $index => $file) {
                    $name = time() . '_' . uniqid() . '.' . $file->extension();
                    $file->move($dir, $name);
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => $name,
                        'is_primary' => $isPrimary && $index === 0,
                        'sort_order' => $lastOrder + $index + 1,
                    ]);
                }
            }

            $product->colors()->sync($request->colors ?? []);

            $product->specifications()->delete();
            if ($request->has('spec_key')) {
                foreach ($request->spec_key as $i => $key) {
                    $key = trim($key);
                    $val = trim($request->spec_value[$i] ?? '');
                    if ($key !== '' && $val !== '') {
                        ProductSpecification::create([
                            'product_id' => $product->id,
                            'spec_key'   => $key,
                            'spec_value' => $val,
                            'sort_order' => $i,
                        ]);
                    }
                }
            }

            return redirect()->route('products.index')->with('success', 'Product updated successfully.');

        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        $product = Product::with('images')->findOrFail($id);
        $dir     = public_path($this->imagePath);
        foreach ($product->images as $img) {
            $full = $dir . '/' . $img->image_path;
            if (file_exists($full)) {
                unlink($full);
            }
        }
        $product->delete();
        return response()->json(['success' => true, 'message' => 'Product deleted successfully.']);
    }

    public function deleteImage(Request $request)
    {
        $img = ProductImage::findOrFail($request->image_id);
        $full = public_path($this->imagePath) . '/' . $img->image_path;
        if (file_exists($full)) {
            unlink($full);
        }
        // If deleting primary, promote the next image
        if ($img->is_primary) {
            $next = ProductImage::where('product_id', $img->product_id)
                ->where('id', '!=', $img->id)
                ->orderBy('sort_order')
                ->first();
            if ($next) {
                $next->update(['is_primary' => true]);
            }
        }
        $img->delete();
        return response()->json(['success' => true]);
    }

    public function setPrimaryImage(Request $request)
    {
        $img = ProductImage::findOrFail($request->image_id);
        ProductImage::where('product_id', $img->product_id)->update(['is_primary' => false]);
        $img->update(['is_primary' => true]);
        return response()->json(['success' => true]);
    }

    public function getData(Request $request)
    {
        if (isset($request->order[0]['column'])) {
            $request->order_column = $request->order[0]['column'];
            $request->order_dir    = $request->order[0]['dir'];
        }

        $records  = (new Product)->fetchData($request, $this->columns);
        $total    = $records->get();
        $products = isset($request->start)
            ? $records->offset($request->start)->limit($request->length)->get()
            : $records->offset(0)->limit(count($total))->get();

        $result = [];
        $i = 1;
        foreach ($products as $value) {
            $primaryImg = $value->primaryImage;
            $imgHtml = $primaryImg
                ? "<img src='" . config('custom.public_path') . '/' . $this->imagePath . $primaryImg->image_path . "' style='width:50px;height:50px;object-fit:cover;border-radius:4px;' />"
                : '<span class="text-muted">-</span>';

            $action  = '<div class="hstack gap-2 justify-content-end">';
            $action .= '<a href="' . route('products.edit', $value->id) . '" class="avatar-text avatar-md" title="Edit"><i class="feather feather-edit-3"></i></a>';
            $action .= '<a href="javascript:void(0)" class="avatar-text avatar-md text-danger delete-btn" data-id="' . $value->id . '" title="Delete"><i class="feather feather-trash-2"></i></a>';
            $action .= '</div>';

            $result[] = [
                'srno'       => $i++,
                'id'         => $value->id,
                'checkbox'   => '<input type="checkbox" class="row-checkbox" value="' . $value->id . '">',
                'image'      => $imgHtml,
                'name'       => ucfirst($value->name),
                'category'   => $value->category->name ?? '-',
                'price'      => '₹' . number_format($value->price, 2),
                'stock'      => $value->stock,
                'status'     => isActiveInactive($value->status, route('products.statusChange'), $value->id),
                'created_at' => dateFormat($value->created_at),
                'actions'    => $action,
            ];
        }

        return response()->json([
            'data'            => $result,
            'recordsTotal'    => count($total),
            'recordsFiltered' => count($total),
        ]);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);
        $products = Product::with('images')->whereIn('id', $request->ids)->get();
        $dir = public_path($this->imagePath);
        foreach ($products as $product) {
            foreach ($product->images as $img) {
                $full = $dir . '/' . $img->image_path;
                if (file_exists($full)) {
                    unlink($full);
                }
            }
            $product->delete();
        }
        return response()->json(['success' => true, 'message' => 'Selected products deleted successfully.']);
    }

    public function statusChange(Request $request)
    {
        return statusChange($request, new Product);
    }

    public function getCategories(Request $request)
    {
        $categories = Category::where('store_id', $request->store_id)->get(['id', 'name']);
        return response()->json($categories);
    }

    public function getSubCategories(Request $request)
    {
        $subCategories = SubCategory::where('category_id', $request->category_id)->get(['id', 'sub_category as name']);
        return response()->json($subCategories);
    }
}
