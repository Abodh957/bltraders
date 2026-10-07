@extends('admin.layouts.app')
@section('content')
<link rel="stylesheet" href="{{ config('custom.public_path').'/adminAssets/assets/vendors/css/quill.min.css' }}">
<style>
    /* Quill's toolbar and body are two stacked boxes — join them into one and
       leave room before the Back/Save row. */
    .quill-wrap { margin-bottom: 1.25rem; }
    .quill-wrap .ql-toolbar.ql-snow { border-radius: 6px 6px 0 0; border-color: #e5e7eb; background: #f8fafc; }
    .quill-wrap .ql-container.ql-snow { border-radius: 0 0 6px 6px; border-color: #e5e7eb; }
    .quill-wrap .ql-editor { min-height: 280px; font-size: 13px; }
</style>
<main class="nxl-container">
    <div class="nxl-content">
        <div class="page-header">
            <div class="page-header-left d-flex align-items-center">
                <div class="page-header-title"><h5 class="m-b-10">Edit Page</h5></div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('pages.index') }}">Pages</a></li>
                    <li class="breadcrumb-item">Edit</li>
                </ul>
            </div>
        </div>

        <div class="main-content">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card stretch stretch-full">
                        <div class="card-body">
                            <form action="{{ route('pages.update', $page->id) }}" method="POST" id="pageForm">
                                @csrf
                                @method('PUT')
                                <div class="row">
                                    <div class="col-lg-6 mb-4">
                                        <label class="form-label">Title <span class="text-danger">*</span></label>
                                        <input type="text" name="title" value="{{ old('title', $page->title) }}"
                                            class="form-control @error('title') is-invalid @enderror" />
                                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-lg-3 mb-4">
                                        <label class="form-label">Slug</label>
                                        <input type="text" name="slug" value="{{ old('slug', $page->slug) }}" @if($page->isProtected()) readonly @endif
                                            class="form-control @error('slug') is-invalid @enderror"
                                            placeholder="auto from title" />
                                        @if($page->isProtected())<small class="text-muted">Ye default page hai — slug nahi badal sakta.</small>@endif
                                        @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-lg-3 mb-4">
                                        <label class="form-label">Status <span class="text-danger">*</span></label>
                                        <select name="status" class="form-control @error('status') is-invalid @enderror">
                                            <option value="1" {{ old('status', $page->status) == 1 ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ old('status', $page->status) == 0 ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-lg-12">
                                        <label class="form-label">Content</label>
                                        <div class="quill-wrap">
                                        <div id="editor" style="height: 320px;">{!! old('content', $page->content) !!}</div>
                                        </div>
                                        <input type="hidden" name="content" id="contentInput">
                                        @error('content')<div class="text-danger fs-12 mt-1">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-lg-6 d-flex justify-content-start">
                                        <a href="{{ route('pages.index') }}" class="btn btn-secondary">
                                            <i class="feather-arrow-left me-2"></i><span>Back</span>
                                        </a>
                                    </div>
                                    <div class="col-lg-6 d-flex justify-content-end">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="feather-save me-2"></i><span>Save</span>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('script')
    <script src="{{ config('custom.public_path').'/adminAssets/assets/vendors/js/quill.min.js' }}"></script>
    <script>
        // Quill ships with the theme — no extra library needed.
        var quill = new Quill('#editor', {
            theme: 'snow',
            placeholder: 'Write the page content here...',
            modules: {
                toolbar: [
                    [{ header: [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ list: 'ordered' }, { list: 'bullet' }],
                    [{ align: [] }],
                    ['link', 'blockquote'],
                    ['clean']
                ]
            }
        });

        document.getElementById('pageForm').addEventListener('submit', function () {
            // Empty editor posts as empty, not as "<p><br></p>".
            var html = quill.getText().trim().length ? quill.root.innerHTML : '';
            document.getElementById('contentInput').value = html;
        });
    </script>
    @endpush

    @include('admin.layouts.footer')
</main>
@endsection
