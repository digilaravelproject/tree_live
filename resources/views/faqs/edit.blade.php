@extends('layouts.app')
@section('title', '| ' . $page_title)
@section('content')
    <main>
        <div class="container-fluid py-3">
            <h4 class="mb-4" style="color: #7cb342;">{{ $page_title }}</h4>

            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="{{ route('faqs.update', $faq->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label" style="color: #7cb342;">Question</label>
                            <input type="text" name="question" class="form-control" value="{{ $faq->question }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" style="color: #7cb342;">Answer</label>
                            <textarea name="answer" class="form-control" rows="5" required>{{ $faq->answer }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary" style="background-color: #7cb342; border-color: #7cb342;">Update FAQ</button>
                        <a href="{{ route('faqs.index') }}" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </main>
@endsection