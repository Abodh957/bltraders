<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use  App\Models\MainCategory;

class MainCategoryController extends Controller
{
    function __construct(){
        $this->middleware('permission:Main-Category-Management', ['only' => ['index','store','create','edit','destroy','update']]);
        $this->Model = new MainCategory;
        $this->uploadPath = 'uploads/admin/mainCategory/';
        $this->columns = [
            "id",
            'name',
            'image',
            'description',
            'status',
            'created_at'
        ];

    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.mainCategories.index');

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.mainCategories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:main_categories,name',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string',
        ]);

        // Define the upload path
        $uploadPath = public_path($this->uploadPath);

        // Check if the directory exists, if not create it with permissions
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0775, true); // 0775 allows read/write/execute for owner and group, and read/execute for others
        }

        // Handle file upload
        if ($request->hasFile('image')) {
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move($uploadPath, $imageName);
        }

        // Create new main category
        MainCategory::create([
            'name' => $request->name,
            'description' => $request->description,
            'image' => $imageName,
            'status' => 1, // default to active
        ]);

        return redirect()->route('main-categories.index')->with('success', 'Main Category created successfully.');
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
        $mainCategory = MainCategory::where("id",$id)->first();
        $mainCategory->image ="/". $this->uploadPath.$mainCategory->image;
        //$mainCategoryimage =$mainCategory->image ? config('custom.public_path'). '/'.$this->uploadPath.$mainCategory->image :'';
        return view('admin.mainCategories.edit',compact('mainCategory'));
    }

    /**
     * Update the specified resource in storage.
     */
    // public function update(Request $request, string $id)
    // {
    //     dd($request->all());
    // }
    public function update(Request $request, MainCategory $mainCategory)
    {
    // Validate the form input
    $request->validate([
        'name' => 'required|string|max:255|unique:main_categories,name,' . $mainCategory->id,
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'description' => 'nullable|string',
    ]);

    // Define the upload path
    $uploadPath = public_path($this->uploadPath);

    // Check if the directory exists, if not create it with permissions
    if (!file_exists($uploadPath)) {
        mkdir($uploadPath, 0775, true);
    }

    // Handle file upload
    if ($request->hasFile('image')) {
        // Delete the old image if a new one is uploaded
        if ($mainCategory->image) {
            $oldImagePath = $uploadPath . '/' . $mainCategory->image;
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
        }

        $imageName = time() . '.' . $request->image->extension();
        $request->image->move($uploadPath, $imageName);
    } else {
        $imageName = $mainCategory->image; // Keep the old image if no new image is uploaded
    }

    // Update the main category with the new data
    $mainCategory->update([
        'name' => $request->name,
        'description' => $request->description,
        'image' => $imageName,
    ]);

    return redirect()->route('main-categories.index')->with('success', 'Main Category updated successfully.');
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

        $records = $this->Model->fetchData($request, $this->columns);
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
            $data['status']=isActiveInactive($value->status,route('main-categories.statusChange'),$value->id);
            $data['image'] ="<img class='avatar-image avatar-md bg-warning text-white' src='".config('custom.public_path'). '/'.$this->uploadPath.'/'.$value->image."'/>";
            $data['description'] = $value->description;
            $data['created_at'] = dateFormat($value->created_at); // Assuming created_at is a Carbon instance
            $action = actions([
                'edit' => route('main-categories.edit', $value->id),
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

    public function statusChange(Request $request)
    {
       return statusChange($request,$this->Model);
    }
}
