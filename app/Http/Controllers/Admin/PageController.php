<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PageController extends Controller
{
    protected $columns;

    public function __construct()
    {
        $this->columns = ['id', 'title', 'slug', 'status', 'created_at'];
    }

    public function index()
    {
        return view('admin.pages.index');
    }

    public function create()
    {
        return view('admin.pages.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'   => 'required|string|max:255',
            'slug'    => 'nullable|string|max:160|unique:pages,slug',
            'content' => 'nullable|string',
            'status'  => 'required|in:0,1',
        ]);

        Page::create([
            'title'   => trim($request->title),
            'slug'    => $this->uniqueSlug($request->slug ?: $request->title),
            'content' => $request->content,
            'status'  => $request->status,
        ]);

        return redirect()->route('pages.index')->with('success', 'Page created successfully.');
    }

    public function edit(string $id)
    {
        $page = Page::findOrFail($id);

        return view('admin.pages.edit', compact('page'));
    }

    public function update(Request $request, string $id)
    {
        $page = Page::findOrFail($id);

        $request->validate([
            'title'   => 'required|string|max:255',
            // The app links to terms-and-conditions / privacy-policy by slug,
            // so those two keep the slug they have.
            'slug'    => ['nullable', 'string', 'max:160', Rule::unique('pages', 'slug')->ignore($page->id)],
            'content' => 'nullable|string',
            'status'  => 'required|in:0,1',
        ]);

        $page->update([
            'title'   => trim($request->title),
            'slug'    => $page->isProtected()
                ? $page->slug
                : $this->uniqueSlug($request->slug ?: $request->title, $page->id),
            'content' => $request->content,
            'status'  => $request->status,
        ]);

        return redirect()->route('pages.index')->with('success', 'Page updated successfully.');
    }

    public function destroy(string $id)
    {
        $page = Page::findOrFail($id);

        if ($page->isProtected()) {
            return response()->json([
                'success' => false,
                'message' => 'Terms and Privacy Policy pages cannot be deleted. You can edit them instead.',
            ], 422);
        }

        $page->delete();

        return response()->json(['success' => true, 'message' => 'Page deleted successfully.']);
    }

    public function getData(Request $request)
    {
        if (isset($request->order[0]['column'])) {
            $request->order_column = $request->order[0]['column'];
            $request->order_dir    = $request->order[0]['dir'];
        }

        $records = (new Page)->fetchData($request, $this->columns);
        $total   = $records->get();

        $pages = isset($request->start)
            ? $records->offset($request->start)->limit($request->length)->get()
            : $records->get();

        $result = [];
        $i = ($request->start ?? 0) + 1;

        foreach ($pages as $page) {
            $result[] = [
                'srno'       => $i++,
                'id'         => $page->id,
                'title'      => $page->title,
                'slug'       => $page->slug,
                'status'     => isActiveInactive($page->status, route('pages.statusChange'), $page->id),
                'created_at' => dateFormat($page->created_at),
                'actions'    => $this->actionButtons($page),
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
        return statusChange($request, Page::class);
    }

    /** Edit for everyone; delete only for pages the app does not link to by slug. */
    private function actionButtons(Page $page): string
    {
        $html = '<div class="hstack gap-2 justify-content-end">';
        $html .= '<a href="' . route('pages.edit', $page->id) . '" class="avatar-text avatar-md" title="Edit">'
               . '<i class="feather feather-edit-3"></i></a>';

        if ($page->isProtected()) {
            $html .= '<span class="avatar-text avatar-md text-muted" title="Default page — cannot be deleted">'
                   . '<i class="feather feather-lock"></i></span>';
        } else {
            $html .= '<a href="javascript:void(0)" class="avatar-text avatar-md text-danger delete-btn" '
                   . 'data-id="' . $page->id . '" title="Delete"><i class="feather feather-trash-2"></i></a>';
        }

        return $html . '</div>';
    }

    /** Keeps slugs unique without ever colliding with an existing page. */
    private function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $slug = Str::slug($value) ?: 'page';
        $base = $slug;
        $i    = 1;

        while (Page::where('slug', $slug)->when($ignoreId, fn($q) => $q->whereKeyNot($ignoreId))->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
