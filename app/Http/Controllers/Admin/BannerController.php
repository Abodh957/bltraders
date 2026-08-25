<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BannerController extends Controller
{
    public function __construct()
    {
        $this->Model = new Banner;
        $this->uploadPath = 'uploads/admin/Banner/';
        $this->columns = [
            'id',
            'title',
            'heading',
            'image',
            'order',
            'status',
            'created_at',
        ];
    }

    public function index()
    {
        return view('admin.banners.index');
    }

    public function create()
    {
        $stores = Store::where('status', 1)->orderBy('name')->get();
        return view('admin.banners.create', compact('stores'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'   => 'required|string|max:255',
            'heading' => 'nullable|string|max:255',
            'image'   => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'order'   => 'nullable|integer|min:0',
            'store_id' => 'nullable|exists:stores,id',
            'status'  => 'required|in:0,1',
        ]);

        $uploadPath = public_path($this->uploadPath);
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0775, true);
        }

        $imageName = time() . '.' . $request->image->extension();
        $request->image->move($uploadPath, $imageName);

        Banner::create([
            'title'   => $request->title,
            'heading' => $request->heading,
            'image'   => $imageName,
            'order'   => $request->order ?? 0,
            // null = global banner, shown in every store
            'store_id' => $request->store_id ?: null,
            'status'  => $request->status,
        ]);

        return redirect()->route('banners.index')->with('success', 'Banner created successfully.');
    }

    public function show(string $id)
    {
        $banner = Banner::findOrFail($id);
        $banner->image_url = '/' . $this->uploadPath . $banner->image;
        return view('admin.banners.show', compact('banner'));
    }

    public function edit(string $id)
    {
        $banner = Banner::findOrFail($id);
        $banner->image_url = '/' . $this->uploadPath . $banner->image;
        $stores = Store::where('status', 1)->orderBy('name')->get();
        return view('admin.banners.edit', compact('banner', 'stores'));
    }

    public function update(Request $request, Banner $banner)
    {
        $request->validate([
            'title'   => 'required|string|max:255',
            'heading' => 'nullable|string|max:255',
            'image'   => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'order'   => 'nullable|integer|min:0',
            'store_id' => 'nullable|exists:stores,id',
            'status'  => 'required|in:0,1',
        ]);

        $uploadPath = public_path($this->uploadPath);
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0775, true);
        }

        $imageName = $banner->image;
        if ($request->hasFile('image')) {
            if ($banner->image && file_exists($uploadPath . '/' . $banner->image)) {
                unlink($uploadPath . '/' . $banner->image);
            }
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move($uploadPath, $imageName);
        }

        $banner->update([
            'title'   => $request->title,
            // null = global banner, shown in every store
            'store_id' => $request->store_id ?: null,
            'heading' => $request->heading,
            'image'   => $imageName,
            'order'   => $request->order ?? 0,
            'status'  => $request->status,
        ]);

        return redirect()->route('banners.index')->with('success', 'Banner updated successfully.');
    }

    public function destroy(Banner $banner)
    {
        $uploadPath = public_path($this->uploadPath);
        if ($banner->image && file_exists($uploadPath . '/' . $banner->image)) {
            unlink($uploadPath . '/' . $banner->image);
        }
        $banner->delete();
        return response()->json(['success' => true, 'message' => 'Banner deleted successfully.']);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);

        $uploadPath = public_path($this->uploadPath);
        $banners = Banner::whereIn('id', $request->ids)->get();

        foreach ($banners as $banner) {
            if ($banner->image && file_exists($uploadPath . '/' . $banner->image)) {
                unlink($uploadPath . '/' . $banner->image);
            }
            $banner->delete();
        }

        return response()->json(['success' => true, 'message' => 'Selected banners deleted successfully.']);
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
            $banners = $records->offset($request->start)->limit($request->length)->get();
        } else {
            $banners = $records->offset(0)->limit(count($total))->get();
        }

        $result = [];
        $i = 1;
        foreach ($banners as $value) {
            $data             = [];
            $data['srno']     = $i++;
            $data['id']       = $value->id;
            $data['checkbox'] = '<input type="checkbox" class="row-checkbox" value="' . $value->id . '">';
            $data['title']    = ucfirst($value->title);
            $data['heading']  = $value->heading ?? '-';
            $data['order']    = $value->order;
            $data['image']    = "<img class='avatar-image avatar-md bg-warning text-white' src='" . config('custom.public_path') . '/' . $this->uploadPath . $value->image . "' style='width:60px;height:40px;object-fit:cover;border-radius:4px;'/>";
            $data['status']   = isActiveInactive($value->status, route('banners.statusChange'), $value->id);
            $data['created_at'] = dateFormat($value->created_at);

            $action = '<div class="hstack gap-2 justify-content-end">';
            $action .= '<a href="' . route('banners.show', $value->id) . '" class="avatar-text avatar-md" title="View"><i class="feather feather-eye"></i></a>';
            $action .= '<a href="' . route('banners.edit', $value->id) . '" class="avatar-text avatar-md" title="Edit"><i class="feather feather-edit-3"></i></a>';
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
