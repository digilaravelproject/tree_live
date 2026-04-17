@extends('layouts.app')

@section('title')
    | {{ $page_title }}
@endsection

@section('content')
    <main>
        <div class="container-fluid">
            <div class="row justify-content-center mt-5">
                <div class="col-md-6">
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Set New Global Tree Price</h5>
                        </div>
                        <div class="card-body p-4">

                            {{-- Alert Messages --}}
                            @if (session('error'))
                                <div class="alert alert-danger">{{ session('error') }}</div>
                            @endif

                            <form action="{{ route('tree.price.store') }}" method="POST">
                                @csrf

                                <div class="mb-4">
                                    <label for="price" class="form-label fw-bold">Enter New Price (₹)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₹</span>
                                        <input type="number" step="0.01" name="price" id="price"
                                            class="form-control form-control-lg" placeholder="e.g. 500" required>
                                    </div>
                                    <div class="form-text text-muted mt-2">
                                        <i class="bi bi-info-circle"></i> This will become the new <strong>Active
                                            Price</strong> for ALL trees. Previous prices will be moved to history.
                                    </div>
                                    @error('price')
                                        <small class="text-danger fw-bold">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <a href="{{ route('tree.price.list') }}" class="btn btn-light me-md-2">Cancel</a>
                                    <button type="submit" class="btn btn-success px-4">Save & Activate</button>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
