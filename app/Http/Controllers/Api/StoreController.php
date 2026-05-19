<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function index()
    {
        $stores = Store::where('status', 1)
            ->orderBy('name')
            ->get()
            ->map(fn($s) => $this->format($s));

        return response()->json(['status' => true, 'data' => $stores]);
    }

    public function show(string $id)
    {
        $store = Store::find($id);
        if (!$store) {
            return response()->json(['status' => false, 'message' => 'Store not found'], 404);
        }
        return response()->json(['status' => true, 'data' => $this->format($store)]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'   => 'required|string|max:100|unique:stores,name',
            'status' => 'nullable|in:0,1',
        ]);

        $store = Store::create([
            'name'   => $request->name,
            'slug'   => \Illuminate\Support\Str::slug($request->name),
            'status' => $request->status ?? 1,
        ]);

        return response()->json(['status' => true, 'message' => 'Store created successfully.', 'data' => $this->format($store)], 201);
    }

    public function update(Request $request, string $id)
    {
        $store = Store::find($id);
        if (!$store) {
            return response()->json(['status' => false, 'message' => 'Store not found'], 404);
        }

        $request->validate([
            'name'   => ['sometimes', 'required', 'string', 'max:100', \Illuminate\Validation\Rule::unique('stores', 'name')->ignore($id)],
            'status' => 'nullable|in:0,1',
        ]);

        $store->update([
            'name'   => $request->name   ?? $store->name,
            'slug'   => $request->name   ? \Illuminate\Support\Str::slug($request->name) : $store->slug,
            'status' => $request->status ?? $store->status,
        ]);

        return response()->json(['status' => true, 'message' => 'Store updated successfully.', 'data' => $this->format($store->fresh())]);
    }

    public function destroy(string $id)
    {
        $store = Store::find($id);
        if (!$store) {
            return response()->json(['status' => false, 'message' => 'Store not found'], 404);
        }
        $store->delete();
        return response()->json(['status' => true, 'message' => 'Store deleted successfully.']);
    }

    private function format(Store $store): array
    {
        return [
            'id'         => $store->id,
            'name'       => $store->name,
            'slug'       => $store->slug,
            'status'     => $store->status,
            'created_at' => $store->created_at,
            'updated_at' => $store->updated_at,
        ];
    }
}
