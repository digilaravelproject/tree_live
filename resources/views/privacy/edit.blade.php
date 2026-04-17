@extends('layouts.app')
@section('title', 'Edit Privacy Policy')

@section('content')
    <div class="container py-5">
        <h3>Edit Privacy Policy</h3>

        <form method="POST" action="{{ route('privacy.update', $privacy->id) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Title</label>
                <input type="text" name="title" class="form-control" value="{{ old('title', $privacy->title) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Content</label>
                <textarea name="content" id="content" rows="10" class="form-control">{{ old('content', $privacy->content) }}</textarea>
            </div>

            <button type="submit" class="btn btn-success">Update</button>
            <a href="{{ route('privacy.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>

    <script src="https://cdn.ckeditor.com/ckeditor5/41.2.1/classic/ckeditor.js"></script>
    <script>
        ClassicEditor
            .create(document.querySelector('#content'))
            .catch(error => console.error(error));
    </script>
@endsection
