<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportContact;
use Illuminate\Http\Request;

class SupportContactController extends Controller
{
    protected $columns;

    public function __construct()
    {
        $this->columns = ['id', 'name', 'phone', 'designation', 'sort_order', 'status', 'created_at'];
    }

    public function index()
    {
        return view('admin.supportContacts.index');
    }

    public function create()
    {
        return view('admin.supportContacts.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());

        SupportContact::create([
            'name'        => trim($data['name']),
            'phone'       => trim($data['phone']),
            'designation' => isset($data['designation']) ? trim($data['designation']) : null,
            'sort_order'  => $data['sort_order'] ?? 0,
            'status'      => $data['status'],
        ]);

        return redirect()->route('support-contacts.index')->with('success', 'Support contact added successfully.');
    }

    public function edit(string $id)
    {
        $contact = SupportContact::findOrFail($id);

        return view('admin.supportContacts.edit', compact('contact'));
    }

    public function update(Request $request, string $id)
    {
        $contact = SupportContact::findOrFail($id);
        $data    = $request->validate($this->rules());

        $contact->update([
            'name'        => trim($data['name']),
            'phone'       => trim($data['phone']),
            'designation' => isset($data['designation']) ? trim($data['designation']) : null,
            'sort_order'  => $data['sort_order'] ?? 0,
            'status'      => $data['status'],
        ]);

        return redirect()->route('support-contacts.index')->with('success', 'Support contact updated successfully.');
    }

    public function destroy(string $id)
    {
        SupportContact::findOrFail($id)->delete();

        return response()->json(['success' => true, 'message' => 'Support contact deleted successfully.']);
    }

    public function getData(Request $request)
    {
        if (isset($request->order[0]['column'])) {
            $request->order_column = $request->order[0]['column'];
            $request->order_dir    = $request->order[0]['dir'];
        }

        $records = (new SupportContact)->fetchData($request, $this->columns);
        $total   = $records->get();

        $contacts = isset($request->start)
            ? $records->offset($request->start)->limit($request->length)->get()
            : $records->get();

        $result = [];
        $i = ($request->start ?? 0) + 1;

        foreach ($contacts as $c) {
            $result[] = [
                'srno'        => $i++,
                'id'          => $c->id,
                'name'        => $c->name,
                'phone'       => $c->phone,
                'designation' => $c->designation ?: '-',
                'sort_order'  => $c->sort_order,
                'status'      => isActiveInactive($c->status, route('support-contacts.statusChange'), $c->id),
                'created_at'  => dateFormat($c->created_at),
                'actions'     => '<div class="hstack gap-2 justify-content-end">'
                    . '<a href="' . route('support-contacts.edit', $c->id) . '" class="avatar-text avatar-md" title="Edit">'
                    . '<i class="feather feather-edit-3"></i></a>'
                    . '<a href="javascript:void(0)" class="avatar-text avatar-md text-danger delete-btn" data-id="' . $c->id . '" title="Delete">'
                    . '<i class="feather feather-trash-2"></i></a></div>',
            ];
        }

        return json_encode([
            'data'            => $result,
            'recordsTotal'    => count($total),
            'recordsFiltered' => count($total),
        ]);
    }

    public function statusChange(Request $request)
    {
        return statusChange($request, SupportContact::class);
    }

    private function rules(): array
    {
        return [
            'name'        => 'required|string|max:255',
            'phone'       => 'required|string|max:20',
            'designation' => 'nullable|string|max:255',
            'sort_order'  => 'nullable|integer|min:0',
            'status'      => 'required|in:0,1',
        ];
    }
}
