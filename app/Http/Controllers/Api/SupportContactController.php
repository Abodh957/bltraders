<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SupportContact;
use Illuminate\Http\Request;

/**
 * Support numbers shown on the app's "Help / Contact us" screen. Public.
 */
class SupportContactController extends Controller
{
    /** GET /api/support-contacts?per_page=15&page=1&search= */
    public function index(Request $request)
    {
        $request->validate([
            'per_page' => 'nullable|integer|min:1|max:100',
            'search'   => 'nullable|string|max:100',
        ]);

        $query = SupportContact::where('status', 1)
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->search;
                $q->where(fn($qb) => $qb->where('name', 'like', "%$s%")
                    ->orWhere('phone', 'like', "%$s%")
                    ->orWhere('designation', 'like', "%$s%"));
            })
            ->orderBy('sort_order')
            ->orderBy('name');

        $contacts = $query->paginate((int) $request->get('per_page', 15));

        return response()->json([
            'status' => true,
            'data'   => $contacts->getCollection()->map(fn(SupportContact $c) => [
                'id'          => $c->id,
                'name'        => $c->name,
                'phone'       => $c->phone,
                'designation' => $c->designation,
            ])->values(),
            'meta'   => [
                'total'        => $contacts->total(),
                'per_page'     => $contacts->perPage(),
                'current_page' => $contacts->currentPage(),
                'last_page'    => $contacts->lastPage(),
            ],
        ]);
    }
}
