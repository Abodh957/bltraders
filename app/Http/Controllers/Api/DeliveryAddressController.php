<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeliveryAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Customer delivery addresses.
 *
 * A customer can keep several addresses; exactly one of them is the default
 * whenever they have any. The first address saved becomes the default, and
 * removing the default promotes the most recently updated remaining address.
 */
class DeliveryAddressController extends Controller
{
    /** GET /api/addresses — default first, then newest. */
    public function index(Request $request)
    {
        $addresses = DeliveryAddress::where('user_id', $request->user()->id)
            ->orderByDesc('is_default')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn(DeliveryAddress $a) => $this->format($a));

        return response()->json(['status' => true, 'data' => $addresses]);
    }

    /** GET /api/addresses/default */
    public function defaultAddress(Request $request)
    {
        $address = DeliveryAddress::where('user_id', $request->user()->id)
            ->where('is_default', true)
            ->first();

        return response()->json([
            'status' => true,
            'data'   => $address ? $this->format($address) : null,
        ]);
    }

    /** GET /api/addresses/{id} */
    public function show(Request $request, string $id)
    {
        $address = $this->findOwned($request, $id);

        if (!$address) {
            return response()->json(['status' => false, 'message' => 'Address not found.'], 404);
        }

        return response()->json(['status' => true, 'data' => $this->format($address)]);
    }

    /** POST /api/addresses */
    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $user = $request->user();

        if (DeliveryAddress::where('user_id', $user->id)->count() >= DeliveryAddress::MAX_PER_USER) {
            return response()->json([
                'status'  => false,
                'message' => 'You can save up to ' . DeliveryAddress::MAX_PER_USER . ' addresses. Please remove one first.',
            ], 422);
        }

        $address = DB::transaction(function () use ($data, $user, $request) {
            $isFirst = !DeliveryAddress::where('user_id', $user->id)->lockForUpdate()->exists();
            $makeDefault = $isFirst || $request->boolean('is_default');

            if ($makeDefault) {
                DeliveryAddress::where('user_id', $user->id)->update(['is_default' => false]);
            }

            return DeliveryAddress::create(array_merge($this->payload($data), [
                'user_id'    => $user->id,
                'is_default' => $makeDefault,
            ]));
        });

        return response()->json([
            'status'  => true,
            'message' => 'Address saved successfully.',
            'data'    => $this->format($address->fresh()),
        ], 201);
    }

    /** POST /api/addresses/{id} — partial update, send only what changes. */
    public function update(Request $request, string $id)
    {
        $address = $this->findOwned($request, $id);

        if (!$address) {
            return response()->json(['status' => false, 'message' => 'Address not found.'], 404);
        }

        $data = $request->validate($this->rules(partial: true));

        DB::transaction(function () use ($address, $data, $request) {
            // Unchecking "default" on the only default is ignored — a customer
            // with addresses always keeps one default.
            if ($request->has('is_default') && $request->boolean('is_default')) {
                DeliveryAddress::where('user_id', $address->user_id)
                    ->whereKeyNot($address->id)
                    ->update(['is_default' => false]);
                $address->is_default = true;
            }

            $address->fill($this->payload($data, $address));
            $address->save();
        });

        return response()->json([
            'status'  => true,
            'message' => 'Address updated successfully.',
            'data'    => $this->format($address->fresh()),
        ]);
    }

    /** POST /api/addresses/{id}/default */
    public function makeDefault(Request $request, string $id)
    {
        $address = $this->findOwned($request, $id);

        if (!$address) {
            return response()->json(['status' => false, 'message' => 'Address not found.'], 404);
        }

        DB::transaction(function () use ($address) {
            DeliveryAddress::where('user_id', $address->user_id)
                ->whereKeyNot($address->id)
                ->update(['is_default' => false]);
            $address->update(['is_default' => true]);
        });

        return response()->json([
            'status'  => true,
            'message' => 'Default address updated.',
            'data'    => $this->format($address->fresh()),
        ]);
    }

    /** DELETE /api/addresses/{id} */
    public function destroy(Request $request, string $id)
    {
        $address = $this->findOwned($request, $id);

        if (!$address) {
            return response()->json(['status' => false, 'message' => 'Address not found.'], 404);
        }

        DB::transaction(function () use ($address) {
            $wasDefault = $address->is_default;
            $address->update(['is_default' => false]);
            $address->delete(); // soft delete — past orders keep resolving it

            if ($wasDefault) {
                $next = DeliveryAddress::where('user_id', $address->user_id)
                    ->orderByDesc('updated_at')
                    ->orderByDesc('id')
                    ->first();
                $next?->update(['is_default' => true]);
            }
        });

        return response()->json(['status' => true, 'message' => 'Address deleted successfully.']);
    }

    // ── Internals ─────────────────────────────────────────────────────────────

    private function findOwned(Request $request, string $id): ?DeliveryAddress
    {
        return DeliveryAddress::whereKey($id)
            ->where('user_id', $request->user()->id)
            ->first();
    }

    private function rules(bool $partial = false): array
    {
        $req = $partial ? 'sometimes|required' : 'required';

        return [
            'name'            => "$req|string|max:255",
            'phone'           => "$req|digits:10",
            'alternate_phone' => 'nullable|digits:10',
            'address_line1'   => "$req|string|max:500",
            'address_line2'   => 'nullable|string|max:500',
            'landmark'        => 'nullable|string|max:255',
            'city'            => "$req|string|max:100",
            'state'           => "$req|string|max:100",
            'country'         => 'nullable|string|max:100',
            'pincode'         => "$req|digits_between:4,10",
            'type'            => ['nullable', Rule::in(DeliveryAddress::TYPES)],
            'is_default'      => 'nullable|boolean',
        ];
    }

    /** Map validated input to columns; on update, untouched fields keep their value. */
    private function payload(array $data, ?DeliveryAddress $existing = null): array
    {
        $fields = ['name', 'phone', 'alternate_phone', 'address_line1', 'address_line2',
                   'landmark', 'city', 'state', 'country', 'pincode', 'type'];

        $out = [];
        foreach ($fields as $f) {
            if (array_key_exists($f, $data)) {
                $out[$f] = is_string($data[$f]) ? trim($data[$f]) : $data[$f];
            }
        }

        if (!$existing) {
            $out['country'] = filled($out['country'] ?? null) ? $out['country'] : 'India';
            $out['type']    = filled($out['type'] ?? null) ? $out['type'] : 'shop';
        } else {
            // Sending an empty country/type on update shouldn't blank a NOT NULL column.
            if (array_key_exists('country', $out) && blank($out['country'])) $out['country'] = 'India';
            if (array_key_exists('type', $out) && blank($out['type']))       unset($out['type']);
        }

        return $out;
    }

    private function format(DeliveryAddress $a): array
    {
        return [
            'id'              => $a->id,
            'name'            => $a->name,
            'phone'           => $a->phone,
            'alternate_phone' => $a->alternate_phone,
            'address_line1'   => $a->address_line1,
            'address_line2'   => $a->address_line2,
            'landmark'        => $a->landmark,
            'city'            => $a->city,
            'state'           => $a->state,
            'country'         => $a->country,
            'pincode'         => $a->pincode,
            'type'            => $a->type,
            'is_default'      => (bool) $a->is_default,
            'full_address'    => $a->fullAddress(),
            'created_at'      => $a->created_at,
            'updated_at'      => $a->updated_at,
        ];
    }
}
