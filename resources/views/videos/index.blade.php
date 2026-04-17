@extends('layouts.app')
@section('title', $page_title)
@section('content')
    <main class="container py-5">
        <div class="d-flex justify-content-between mb-4">
            <h4 style="color: #7cb342;">{{ $page_title }}</h4>
            <a href="{{ route('videos.create') }}" class="btn btn-primary" style="background-color: #7cb342; border-color: #7cb342;">Upload Video</a>
        </div>
        <div class="card shadow-sm">
            <div class="card-body table-responsive">
                <table class="table table-hover table-bordered align-middle">
                    <thead style="background-color: #7cb342;">
                        <tr>
                            <th style="color: white;">Title</th>
                            <th style="color: white;">Video</th>
                            <th style="color: white;">Uploaded At</th>
                            <th class="text-center" style="color: white;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($videos as $video)
                            <tr>
                                <td>{{ $video->title }}</td>
                                <td>
                                    <video width="200" controls>
                                        <source src="{{ asset('video/' . $video->video) }}" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                </td>
                                <td>{{ $video->created_at->format('d M, Y H:i') }}</td>
                                <td class="text-center">
                                    <a href="{{ route('videos.edit', $video->id) }}"
                                        class="btn btn-sm btn-info me-1" style="background-color: #7cb342; border-color: #7cb342; color: white;">Edit</a>
                                    <form action="{{ route('videos.destroy', $video->id) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Are you sure?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">No videos uploaded.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>
@endsection