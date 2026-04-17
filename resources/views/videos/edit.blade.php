@extends('layouts.app')
@section('title', $page_title)
@section('content')
    <main class="container py-5">
        <h4 class="mb-4" style="color: #7cb342;">{{ $page_title }}</h4>
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div class="card shadow-sm">
            <div class="card-body">
                <form action="{{ route('videos.update', $video->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label" style="color: #7cb342;">Video Title</label>
                        <input type="text" name="title" class="form-control" value="{{ $video->title }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color: #7cb342;">Current Video</label><br>
                        <video width="300" controls>
                            <source src="{{ asset('video/' . $video->video) }}" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color: #7cb342;">Replace Video (Optional)</label>
                        <input type="file" name="video" class="form-control" accept="video/*">
                    </div>
                    <button type="submit" class="btn btn-primary" style="background-color: #7cb342; border-color: #7cb342;">Update Video</button>
                    <a href="{{ route('videos.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </main>
@endsection