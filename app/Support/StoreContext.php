<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Works out which store an API request belongs to.
 *
 * Priority:
 *   1. an explicit ?store_id= on the request  (handy for testing / deep links)
 *   2. the store the logged-in customer selected via POST /api/store/select
 *   3. null — no store context, so nothing is filtered
 *
 * Step 3 is what keeps this backwards compatible: guests, and customers who
 * have not picked a store yet, keep seeing exactly what they saw before.
 */
class StoreContext
{
    /** Resolve the store id for this request, or null when there is none. */
    public static function resolve(Request $request): ?int
    {
        if ($request->filled('store_id')) {
            return (int) $request->store_id;
        }

        // These endpoints are public (no auth middleware), so $request->user()
        // would always be null even when a valid token is sent. Asking the
        // sanctum guard directly resolves the token when there is one, and
        // quietly returns null when there isn't.
        $user = auth('sanctum')->user();

        return $user && $user->selected_store_id ? (int) $user->selected_store_id : null;
    }

    /**
     * Scope a query to the resolved store.
     *
     * $nullIsGlobal is for tables where a null store_id means "show in every
     * store" (brands, banners). For products/categories/sub-categories a null
     * store_id is just unassigned data, so it stays hidden once a store is
     * chosen — otherwise one store's screen would leak stray rows.
     */
    public static function apply($query, ?int $storeId, string $column = 'store_id', bool $nullIsGlobal = false)
    {
        if ($storeId === null) {
            return $query;
        }

        return $nullIsGlobal
            ? $query->where(fn($q) => $q->where($column, $storeId)->orWhereNull($column))
            : $query->where($column, $storeId);
    }
}
