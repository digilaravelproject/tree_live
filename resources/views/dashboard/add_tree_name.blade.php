@extends('layouts.app')
@section('title')
    | Create Tree Name
@endsection

@section('content')
    <div class="py-5 px-3">
        <div class="container">

            {{-- Import Excel Section --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header text-white d-flex justify-content-between align-items-center" style="background-color: #7cb342;">
                    <h5 class="mb-0">Import Trees (Excel)</h5>
                    <button type="button" class="btn btn-light btn-sm fw-semibold" id="importBtn">
                        <i class="fa fa-file-excel me-1" style="color: #7cb342;"></i> Import Excel
                    </button>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <form id="importForm" action="{{ route('trees.import') }}" method="POST" enctype="multipart/form-data"
                        style="display: none;">
                        @csrf
                        <input type="file" name="file" id="fileInput" accept=".xls,.xlsx" required>
                    </form>
                </div>
            </div>

            {{-- Add New Tree Section --}}
            <div class="card shadow-sm border-0">
                <div class="card-header text-white" style="background-color: #7cb342;">
                    <h5 class="mb-0">Add New Tree</h5>
                </div>
                <div class="card-body px-4 py-4">
                    <form action="{{ route('tree.name.added') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                                <label class="form-label fw-semibold">Tree Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" placeholder="Enter tree name"
                                    required>
                            </div>

                            <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                                <label class="form-label fw-semibold">Scientific Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="scientific_name" class="form-control"
                                    placeholder="Enter scientific name" required>
                            </div>

                            <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                                <label class="form-label fw-semibold">Family Name <span class="text-danger">*</span></label>
                                <input type="text" name="family_name" class="form-control"
                                    placeholder="Enter family name" required>
                            </div>

                            <input type="hidden" name="height_ratio" value="1">
                            <input type="hidden" name="age_ratio" value="1">
                            <input type="hidden" name="canopy_ratio" value="1">
                        </div>

                        <div class="text-end mt-4">
                            <button type="submit" class="btn px-4" style="background-color: #7cb342; color: #ffffff;">Save</button>
                            <a href="" class="btn btn-secondary px-4">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    {{-- Script for auto import --}}
    <script>
        document.getElementById('importBtn').addEventListener('click', function() {
            document.getElementById('fileInput').click();
        });

        document.getElementById('fileInput').addEventListener('change', function() {
            if (this.files.length > 0) {
                document.getElementById('importForm').submit();
            }
        });
    </script>
@endsection