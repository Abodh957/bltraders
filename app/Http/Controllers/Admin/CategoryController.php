<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MainCategory;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;



class CategoryController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:Category-Management', ['only' => ['index', 'store', 'create', 'edit', 'destroy', 'update']]);
        $this->Model = new Category;
        $this->uploadPath = 'uploads/admin/Category/';
        $this->columns = [
            "id", 
            'name',
            'image',
            'description',
            'main_category_id',
            'status',
            'created_at'
        ];

    }

    public function index()
    {
        return view('admin.categories.index');

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $mainCategories = MainCategory::all();
        return view('admin.categories.create', compact('mainCategories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required|string|max:255|unique:categories,name',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string',
            'main_category' => 'required|string',
        ], [
            'main_category.required' => 'Please select main category',
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
        Category::create([
            'name' => $request->category,
            'slug' => Str::slug($request->category),
            'main_category_id' => $request->main_category,
            'description' => $request->description,
            'image' => $imageName,
            'status' => 1, // default to active
        ]);

        return redirect()->route('categories.index')->with('success', 'Category created successfully.');
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
        $mainCategories = MainCategory::all();
        $category = Category::with('mainCategory')->where("id", $id)->first();
        $category->image = "/" . $this->uploadPath . $category->image;
        //$mainCategoryimage =$mainCategory->image ? config('custom.public_path'). '/'.$this->uploadPath.$mainCategory->image :'';
        return view('admin.categories.edit', compact(['category', 'mainCategories']));
    }

    /**
     * Update the specified resource in storage.
     */
    // public function update(Request $request, string $id)
    // {
    //     dd($request->all());
    // }
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'category' => 'required','string','max:255',Rule::unique('categories')->ignore($category->id),
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string',
            'main_category' => 'required|string',
        ], [
            'main_category.required' => 'Please select main category',
        ]);


        // Define the upload path
        $uploadPath = public_path($this->uploadPath);

        // Check if the directory exists, if not create it with permissions
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0775, true);
        }

        if ($request->hasFile('image')) {
            if ($category->image) {
                $oldImagePath = $uploadPath . '/' . $category->image;
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }

            $imageName = time() . '.' . $request->image->extension();
            $request->image->move($uploadPath, $imageName);
        }

        $category->update([
            'name' => $request->category,
            'slug' => Str::slug($request->category),
            'main_category_id' => $request->main_category,
            'description' => $request->description,
        ]);

        return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
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
            $categories = $records->offset($request->start)->limit($request->length)->get();
        } else {
            $categories = $records->offset($request->start)->limit(count($total))->get();
        }
        $result = [];


        $i = 1;
        foreach ($categories as $value) {
            $data = [];
            $data['srno'] = $i++;
            $data['id'] = $value->id;
            $data['name'] = ucfirst($value->name);
            $data['status'] = isActiveInactive($value->status, route('categories.statusChange'), $value->id);
            $data['image'] = "<img class='avatar-image avatar-md bg-warning text-white' src='" . config('custom.public_path') . '/' . $this->uploadPath . '/' . $value->image . "'/>";
            $data['description'] = $value->description;
            $data['created_at'] = dateFormat($value->created_at); // Assuming created_at is a Carbon instance
            $action = actions([
                'edit' => route('categories.edit', $value->id),
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
        return statusChange($request, $this->Model);
    }
}
