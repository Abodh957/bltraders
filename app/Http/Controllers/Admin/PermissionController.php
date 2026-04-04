<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use DB;

class PermissionController extends Controller
{
    function __construct()
    {
        $this->Model = new Permission;

        $this->columns = [
            "id",
            'name',
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $permissions = Permission::orderBy('id','DESC')->get();

        return view('admin.permissions.index',compact('permissions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.permissions.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Validate the request data
            $validatedData = $request->validate([
                'name.*' => 'required|min:3|distinct',
                'id.*' => 'nullable|integer|exists:permissions,id',
            ]);

            $names = $validatedData['name'];
            $ids = $validatedData['id'] ?? []; // Default to empty array if no ids are provided

            foreach ($names as $index => $name) {
                $id = $ids[$index] ?? null;

                // Create or update permission
                Permission::updateOrCreate(
                    ['id' => $id],
                    [
                        'name' =>ucwords(str_replace(' ', '-', $name))
                    ]
                );
            }

            // Redirect back to the permissions list with a success message
            return redirect()->route('permissions.index')->with('success', 'Permissions processed successfully.');
        } catch (\Exception $e) {
            // Log the exception
            \Log::error('Error processing permissions: ' . $e->getMessage());

            // Redirect back with an error message
            return redirect()->back()->with('error', 'An error occurred while processing permissions.');
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return view('admin.permissions.show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return view('admin.permissions.edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
    public function fetchData($request, $columns) {
        $query =Permission::where('name', '!=', '');

        if (isset($request->from_date)) {
            $query->whereRaw('DATE_FORMAT(created_at, "%Y-%m-%d") >= "' . date("Y-m-d", strtotime($request->from_date)) . '"');
        }
        if (isset($request->end_date)) {
            $query->whereRaw('DATE_FORMAT(created_at, "%Y-%m-%d") <= "' . date("Y-m-d", strtotime($request->end_date)) . '"');
        }

        if (isset($request['search']['value'])) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request['search']['value'] . '%');
            });
        }

        if (isset($request->order_column)) {
            $banners = $query->orderBy($columns[$request->order_column], $request->order_dir);
        } else {
            $banners = $query->orderBy('created_at', 'desc');
        }
        return $banners;
    }
    public function getData(Request $request)
    {

    $permissions = Permission::select(['id', 'name', 'created_at']);

    $request->search = $request->search;
    if (isset($request->order[0]['column'])) {
        $request->order_column = $request->order[0]['column'];
        $request->order_dir = $request->order[0]['dir'];
    }
    $records = $this->fetchData($request, $this->columns);
    $total = $records->get();
    if (isset($request->start)) {
        $banners = $records->offset($request->start)->limit($request->length)->get();
    } else {
        $banners = $records->offset($request->start)->limit(count($total))->get();
    }
    $result = [];
    $i = 1;
    foreach ($banners as $value) {
        $data = [];
        $data['id'] = $value->id;
        $data['name'] = ucfirst($value->name);
        $data['created_at'] = ucfirst($value->created_at);
        $action = `<div class="hstack gap-2 justify-content-end">
                                                        <a href="leads-view.html" class="avatar-text avatar-md">
                                                            <i class="feather feather-eye"></i>
                                                        </a>
                                                        <div class="dropdown">
                                                            <a href="javascript:void(0)" class="avatar-text avatar-md" data-bs-toggle="dropdown" data-bs-offset="0,21">
                                                                <i class="feather feather-more-horizontal"></i>
                                                            </a>
                                                            <ul class="dropdown-menu">
                                                                <li>
                                                                    <a class="dropdown-item" href="javascript:void(0)">
                                                                        <i class="feather feather-edit-3 me-3"></i>
                                                                        <span>Edit</span>
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <a class="dropdown-item printBTN" href="javascript:void(0)">
                                                                        <i class="feather feather-printer me-3"></i>
                                                                        <span>Print</span>
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <a class="dropdown-item" href="javascript:void(0)">
                                                                        <i class="feather feather-clock me-3"></i>
                                                                        <span>Remind</span>
                                                                    </a>
                                                                </li>
                                                                <li class="dropdown-divider"></li>
                                                                <li>
                                                                    <a class="dropdown-item" href="javascript:void(0)">
                                                                        <i class="feather feather-archive me-3"></i>
                                                                        <span>Archive</span>
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <a class="dropdown-item" href="javascript:void(0)">
                                                                        <i class="feather feather-alert-octagon me-3"></i>
                                                                        <span>Report Spam</span>
                                                                    </a>
                                                                </li>
                                                                <li class="dropdown-divider"></li>
                                                                <li>
                                                                    <a class="dropdown-item" href="javascript:void(0)">
                                                                        <i class="feather feather-trash-2 me-3"></i>
                                                                        <span>Delete</span>
                                                                    </a>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                `;
        $data['actions'] = $action;
        $result[] = $data;

    }
    $data = json_encode([
        'data' => $result,
        'recordsTotal' => count($total),
        'recordsFiltered' => count($total),
    ]);
    return $data;

}
}
