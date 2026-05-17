<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    private string $uploadPath = 'uploads/admin/Banner/';

    public function index()
    {
        $banners = Banner::where('status', 1)
            ->orderBy('order', 'asc')
            ->get()
            ->map(fn($b) => $this->format($b));

        return response()->json(['status' => true, 'data' => $banners]);
    }

    public function show(string $id)
    {
        $banner = Banner::find($id);
        if (!$banner) {
            return response()->json(['status' => false, 'message' => 'Banner not found'], 404);
        }
        return response()->json(['status' => true, 'data' => $this->format($banner)]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'   => 'required|string|max:255',
            'heading' => 'nullable|string|max:255',
            'image'   => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'order'   => 'nullable|integer|min:0',
            'status'  => 'nullable|in:0,1',
        ]);

        $uploadPath = public_path($this->uploadPath);
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0775, true);
        }

        $imageName = time() . '.' . $request->image->extension();
        $request->image->move($uploadPath, $imageName);

        $banner = Banner::create([
            'title'   => $request->title,
            'heading' => $request->heading,
            'image'   => $imageName,
            'order'   => $request->order ?? 0,
            'status'  => $request->status ?? 1,
        ]);

        return response()->json(['status' => true, 'message' => 'Banner created successfully.', 'data' => $this->format($banner)], 201);
    }

    public function update(Request $request, string $id)
    {
        $banner = Banner::find($id);
        if (!$banner) {
            return response()->json(['status' => false, 'message' => 'Banner not found'], 404);
        }

        $request->validate([
            'title'   => 'sometimes|required|string|max:255',
            'heading' => 'nullable|string|max:255',
            'image'   => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'order'   => 'nullable|integer|min:0',
            'status'  => 'nullable|in:0,1',
        ]);

        $uploadPath = public_path($this->uploadPath);
        $imageName  = $banner->image;

        if ($request->hasFile('image')) {
            if ($banner->image && file_exists($uploadPath . '/' . $banner->image)) {
                unlink($uploadPath . '/' . $banner->image);
            }
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0775, true);
            }
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move($uploadPath, $imageName);
        }

        $banner->update([
            'title'   => $request->title   ?? $banner->title,
            'heading' => $request->heading ?? $banner->heading,
            'image'   => $imageName,
            'order'   => $request->order   ?? $banner->order,
            'status'  => $request->status  ?? $banner->status,
        ]);

        return response()->json(['status' => true, 'message' => 'Banner updated successfully.', 'data' => $this->format($banner->fresh())]);
    }

    public function destroy(string $id)
    {
        $banner = Banner::find($id);
        if (!$banner) {
            return response()->json(['status' => false, 'message' => 'Banner not found'], 404);
        }

        $uploadPath = public_path($this->uploadPath);
        if ($banner->image && file_exists($uploadPath . '/' . $banner->image)) {
            unlink($uploadPath . '/' . $banner->image);
        }

        $banner->delete();

        return response()->json(['status' => true, 'message' => 'Banner deleted successfully.']);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);

        $uploadPath = public_path($this->uploadPath);
        $banners    = Banner::whereIn('id', $request->ids)->get();

        foreach ($banners as $banner) {
            if ($banner->image && file_exists($uploadPath . '/' . $banner->image)) {
                unlink($uploadPath . '/' . $banner->image);
            }
            $banner->delete();
        }

        return response()->json(['status' => true, 'message' => 'Selected banners deleted successfully.']);
    }

    private function format(Banner $banner): array
    {
        return [
            'id'         => $banner->id,
            'title'      => $banner->title,
            'heading'    => $banner->heading,
            'image'      => url($this->uploadPath . $banner->image),
            'order'      => $banner->order,
            'status'     => $banner->status,
            'created_at' => $banner->created_at,
            'updated_at' => $banner->updated_at,
        ];
    }
}
