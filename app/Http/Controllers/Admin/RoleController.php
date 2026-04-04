<?php

namespace App\Http\Controllers\Admin;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Auth;
class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:Role-Management', ['only' => ['index','store','create','edit','destroy','update']]);
        $this->Model = new Role;

        $this->columns = [
            "id",
            'name',
            'created_at'
        ];

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request): View
    {

        $roles = Role::orderBy('id','DESC')->paginate(5);
        return view('admin.roles.index',compact('roles'))
            ->with('i', ($request->input('page', 1) - 1) * 5);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(): View
    {
        $permission = Permission::get();
        return view('admin.roles.create',compact('permission'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'name' => 'required|unique:roles,name',
            'permission' => 'required|array|exists:permissions,id', // Ensure each permission ID exists
        ]);

        // Convert permission IDs to integers (if not already)
        $permissionsID = array_map('intval', $validatedData['permission']);
        // Create the role
        $role = Role::create(['name' => str_replace(" ","-",ucwords(str_replace('-', ' ', strtolower($validatedData['name']))))]);

        // Sync permissions with the role
        $role->syncPermissions($permissionsID);

        // Redirect with success message
        return redirect()->route('roles.index')
                         ->with('success', 'Role created successfully');
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id): View
    {
        $role = Role::find($id);
        $rolePermissions = Permission::join("role_has_permissions","role_has_permissions.permission_id","=","permissions.id")
            ->where("role_has_permissions.role_id",$id)
            ->get();
        return view('roles.show',compact('role','rolePermissions'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id): View
    {

        $role = Role::find($id);
        $permission = Permission::get();
        $rolePermissions = DB::table("role_has_permissions")->where("role_has_permissions.role_id",$id)
            ->pluck('role_has_permissions.permission_id','role_has_permissions.permission_id')
            ->all();

        return view('admin.roles.edit',compact('role','permission','rolePermissions'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id): RedirectResponse
{
    // Validate the request
    $validatedData = $request->validate([
        'name' => 'required',
        'permission' => 'required|array',
        'permission.*' => 'integer|exists:permissions,id', // Ensure permissions exist
    ]);

    // Find the role or handle if not found
    $role = Role::find($id);
    if (!$role) {
        return redirect()->route('roles.index')->with('error', 'Role not found');
    }

    // Format the role name
    $formattedName = str_replace(' ', '-', ucwords(str_replace('-', ' ', strtolower($validatedData['name']))));
    $role->name = $formattedName;
    $role->save();

    // Convert permission IDs to integers
    $permissionsID = array_map('intval', $validatedData['permission']);

    // Sync permissions with the role
    $role->syncPermissions($permissionsID);

    // Redirect with success message
    return redirect()->route('roles.index')->with('success', 'Role updated successfully');
}
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id): RedirectResponse
    {
        DB::table("roles")->where('id',$id)->delete();
        return redirect()->route('roles.index')
                        ->with('success','Role deleted successfully');
    }
    public function fetchData($request, $columns) {
        $query =Role::where('name', '!=', '');

        if (isset($request->from_date)) {
            $query->whereRaw('DATE_FORMAT(created_at, "%Y-%m-%d") >= "' . date("Y-m-d", strtotime($request->from_date)) . '"');
        }
        if (isset($request->end_date)) {
            $query->whereRaw('DATE_FORMAT(created_at, "%Y-%m-%d") <= "' . date("Y-m-d", strtotime($request->end_date)) . '"');
        }
        $query->where('id',"!=",1);
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
    public function getPermissionById($id){
        $permissions =Permission::wherein('id',DB::table("role_has_permissions")->where("role_has_permissions.role_id",$id)
        ->pluck('role_has_permissions.permission_id','role_has_permissions.permission_id')
        ->all())->pluck('name')->toArray();
         $permissionsStr = "";
         if(isset($permissions)){
            foreach($permissions as $val){
               $permissionsStr.='<span class="badge bg-gray-200 text-dark me-2">'.str_replace("-"," ",$val).'</span><br/>';

            }
         }
         return $permissionsStr;
    }
    public function getData(Request $request)
    {

    $permissions = Role::select(['id', 'name', 'created_at']);

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

        $data['srno'] = $i++;
        $data['id'] = $value->id;
        $data['name'] = ucfirst($value->name);
        $data['permissions']=$this->getPermissionById($value->id);
        $data['created_at'] = dateFormat($value->created_at); // Assuming created_at is a Carbon instance
        $action = actions([
            'edit' => route('roles.edit', $value->id),
            'view' => '',   // You may consider removing this if it's not used
            'delete' => ''  // You may consider removing this if it's not used
        ]);

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
