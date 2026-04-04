<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use  App\Models\User;
use Illuminate\Support\Arr;
use DB;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    function __construct(){
        $this->middleware('permission:Customer-Management', ['only' => ['index','store','create','edit','destroy','update']]);
        $this->Model = new User;
        $this->customer_id = DB::table('model_has_roles')->where("role_id",2)->pluck('model_id')->toArray();
        $this->columns = [
            "id",
            'first_name',
            'last_name',
            'email',
            'phone_no',
            'created_at'
        ];

    }

    public function index(Request $request)
    {
        return view('admin.customers.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::where("id","<>",1)->get();
        return view('admin.customers.create',compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email',
                'phone_no' => 'required|numeric|digits:10', // Ensures only numeric characters are allowed
                'password' => 'required|string|min:8|confirmed', // 'confirmed' handles password confirmation
                'password.confirmed' => 'The password confirmation does not match.',
                'role_id' => 'required',
            ]);

            // Hash the password
            $validated['password'] = Hash::make($validated['password']);
            unset($validated['roles']);
            // Create a new user
           $user= User::create($validated);

           $roles = Role::where("id",$request->role_id)->first();

           // Assign roles
           $user->assignRole($roles->name);


            // Redirect back with success message
            return redirect()->back()->with('success', 'Customer created successfully!');

        } catch (\Exception $e) {

            // Redirect back with a generic error message
            return redirect()->back()->withInput()->withErrors(['error' => 'An error occurred while creating the customer.']);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = User::find($id);

        $roles = Role::where("id","<>",1)->pluck('name','name')->all();
        $userRole = $user->roles->pluck('name','name')->all();

        return view('admin.customers.edit',compact('user','roles','userRole'));

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        try {
            // Validate the request
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,'.$id,
                'phone_no' => 'required|numeric|digits:10', // Ensures only numeric characters are allowed
                'password' => 'required|string|min:8|confirmed', // 'confirmed' handles password confirmation
                'password.confirmed' => 'The password confirmation does not match.',
                'role_id' => 'required',
            ]);

            // Hash the password
            $input = $request->all();
            if(!empty($input['password'])){
                $input['password'] = Hash::make($input['password']);
            }else{
                $input = Arr::except($input,array('password'));
            }

            $user = User::find($id);
            $user->update($input);
            DB::table('model_has_roles')->where('model_id',$id)->delete();


            // Assign roles
            $user->assignRole($request->role_id);
            // Redirect back with success message
            return redirect()->back()->with('success', 'Customer updated successfully!');

        } catch (\Exception $e) {
            // Redirect back with a generic error message
            return redirect()->back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
    public function getData(Request $request)
    {
        $request->search = $request->search;
        if (isset($request->order[0]['column'])) {
            $request->order_column = $request->order[0]['column'];
            $request->order_dir = $request->order[0]['dir'];
        }
        $request->customer_id = $this->customer_id;

        $records = $this->Model->fetchCustomerData($request, $this->columns);
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
            $data['first_name'] = ucfirst($value->first_name);
            $data['last_name'] = ucfirst($value->last_name);
            $data['email'] = ucfirst($value->email);
            $data['phone_no'] = ucfirst($value->phone_no);
            $data['created_at'] = dateFormat($value->created_at); // Assuming created_at is a Carbon instance
            $action = actions([
                'edit' => route('customers.edit', $value->id),
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
