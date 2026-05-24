<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Color;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ColorController extends Controller
{
    public function __construct()
    {
        $this->Model   = new Color;
        $this->columns = ['id', 'name', 'hex_code', 'status', 'created_at'];
    }

    public function index()
    {
        return view('admin.colors.index');
    }

    public function create()
    {
        return view('admin.colors.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:100|unique:colors,name',
            'hex_code' => 'required|string|max:10',
            'status'   => 'required|in:0,1',
        ]);

        Color::create([
            'name'     => $request->name,
            'hex_code' => $request->hex_code,
            'status'   => $request->status,
        ]);

        return redirect()->route('colors.index')->with('success', 'Color added successfully.');
    }

    public function edit(string $id)
    {
        $color = Color::findOrFail($id);
        return view('admin.colors.edit', compact('color'));
    }

    public function update(Request $request, string $id)
    {
        $color = Color::findOrFail($id);

        $request->validate([
            'name'     => ['required', 'string', 'max:100', Rule::unique('colors', 'name')->ignore($id)],
            'hex_code' => 'required|string|max:10',
            'status'   => 'required|in:0,1',
        ]);

        $color->update([
            'name'     => $request->name,
            'hex_code' => $request->hex_code,
            'status'   => $request->status,
        ]);

        return redirect()->route('colors.index')->with('success', 'Color updated successfully.');
    }

    public function destroy(string $id)
    {
        Color::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Color deleted successfully.']);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);
        Color::whereIn('id', $request->ids)->delete();
        return response()->json(['success' => true, 'message' => 'Selected colors deleted successfully.']);
    }

    public function getData(Request $request)
    {
        if (isset($request->order[0]['column'])) {
            $request->order_column = $request->order[0]['column'];
            $request->order_dir    = $request->order[0]['dir'];
        }

        $records = $this->Model->fetchData($request, $this->columns);
        $total   = $records->get();
        $colors  = isset($request->start)
            ? $records->offset($request->start)->limit($request->length)->get()
            : $records->offset(0)->limit(count($total))->get();

        $result = [];
        $i = 1;
        foreach ($colors as $value) {
            $swatch = '<span style="display:inline-block;width:28px;height:28px;border-radius:50%;background:' . $value->hex_code . ';border:1px solid #ccc;vertical-align:middle;"></span>';

            $action  = '<div class="hstack gap-2 justify-content-end">';
            $action .= '<a href="' . route('colors.edit', $value->id) . '" class="avatar-text avatar-md" title="Edit"><i class="feather feather-edit-3"></i></a>';
            $action .= '<a href="javascript:void(0)" class="avatar-text avatar-md text-danger delete-btn" data-id="' . $value->id . '" title="Delete"><i class="feather feather-trash-2"></i></a>';
            $action .= '</div>';

            $result[] = [
                'srno'       => $i++,
                'id'         => $value->id,
                'checkbox'   => '<input type="checkbox" class="row-checkbox" value="' . $value->id . '">',
                'swatch'     => $swatch,
                'name'       => ucfirst($value->name),
                'hex_code'   => '<code>' . $value->hex_code . '</code>',
                'status'     => isActiveInactive($value->status, route('colors.statusChange'), $value->id),
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

    public function statusChange(Request $request)
    {
        return statusChange($request, $this->Model);
    }
}
