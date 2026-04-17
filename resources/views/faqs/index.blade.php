@extends('layouts.app')
@section('title')
    | {{ $page_title }}
@endsection

@section('content')
    <main>
        <div class="container-fluid py-3">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="main-title mb-0">FAQs</h4>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFaqModal" style="background-color: #7cb342; border-color: #7cb342;">
                    + Add FAQ
                </button>
            </div>

            <!-- FAQ Table -->
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle mb-0">
                            <thead style="background-color: #7cb342;">
                                <tr>
                                    <th style="width: 25%; color: white;">Question</th>
                                    <th style="width: 45%; color: white;">Answer</th>
                                    <th style="width: 15%; color: white;">Last Update</th>
                                    <th style="width: 15%; color: white;" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($faqs as $faq)
                                    <tr>
                                        <td class="text-wrap">{{ $faq->question }}</td>
                                        <td class="text-wrap">{{ $faq->answer }}</td>
                                        <td>{{ $faq->updated_at->format('d M, Y H:i') }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('faqs.edit', $faq->id) }}" class="btn btn-sm btn-info me-1" style="background-color: #7cb342; border-color: #7cb342; color: white;">
                                                Edit
                                            </a>
                                            <form action="{{ route('faqs.destroy', $faq->id) }}" method="POST"
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
                                        <td colspan="4" class="text-center py-3">No FAQs available.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add FAQ Modal -->
        <div class="modal fade" id="addFaqModal" tabindex="-1" aria-labelledby="addFaqModalLabel" aria-hidden="true">
            <div class="modal-dialog"> <!-- removed modal-lg -->
                <form method="POST" action="{{ route('faqs.store') }}">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header text-white" style="background-color: #7cb342;">
                            <h5 class="modal-title" style="color: white;">Add FAQ</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label" style="color: #7cb342;">Question</label>
                                <input type="text" name="question" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" style="color: #7cb342;">Answer</label>
                                <textarea name="answer" class="form-control" rows="5" required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary" style="background-color: #7cb342; border-color: #7cb342;">Save FAQ</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </main>
@endsection