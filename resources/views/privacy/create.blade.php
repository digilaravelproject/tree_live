@extends('layouts.app')
@section('title', isset($privacyPolicy) ? 'Edit Privacy Policy' : 'Add Privacy Policy')

@section('content')
    <div class="container py-5">
        <h3>{{ isset($privacyPolicy) ? 'Edit Policy' : 'Add Policy' }}</h3>
        <form method="POST"
            action="{{ isset($privacyPolicy) ? route('privacy.update', $privacyPolicy->id) : route('privacy.store') }}">
            @csrf
            @if (isset($privacyPolicy))
                @method('PUT')
            @endif


            <div class="mb-3">
                <label class="form-label">Title</label>
                <input type="text" name="title" class="form-control"
                    value="{{ old('title', $privacyPolicy->title ?? '') }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Content</label>
                <textarea name="content" id="content" rows="10" class="form-control">{{ old('content', $privacyPolicy->content ?? '') }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary">{{ isset($privacyPolicy) ? 'Update' : 'Save' }}</button>
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
