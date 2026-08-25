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

    /**
     * POST /api/store/select   { store_id }
     *
     * Remembers the store for the logged-in customer. Every other API then
     * returns that store's data without needing ?store_id= on each call.
     */
    public function select(Request $request)
    {
        $request->validate([
            'store_id' => 'required|integer|exists:stores,id',
        ]);

        $store = Store::where('id', $request->store_id)->where('status', 1)->first();

        if (!$store) {
            return response()->json(['status' => false, 'message' => 'This store is not available.'], 422);
        }

        $user = $request->user();
        $user->selected_store_id = $store->id;
        $user->save();

        return response()->json([
            'status'  => true,
            'message' => 'Store selected successfully.',
            'data'    => $this->format($store),
        ]);
    }

    /**
     * GET /api/store/selected — the customer's current store, if any.
     */
    public function selected(Request $request)
    {
        $user  = $request->user();
        $store = $user->selected_store_id
            ? Store::where('id', $user->selected_store_id)->where('status', 1)->first()
            : null;

        return response()->json([
            'status' => true,
            'data'   => $store ? $this->format($store) : null,
        ]);
    }

    /**
     * DELETE /api/store/selected — clear it, back to seeing everything.
     */
    public function clearSelected(Request $request)
    {
        $user = $request->user();
        $user->selected_store_id = null;
        $user->save();

        return response()->json(['status' => true, 'message' => 'Store selection cleared.']);
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
