@extends('layouts.app')
@section('title')
    | {{ $page_title }}
@endsection

@section('content')
    <main>
        <div class="container-fluid">
            <!-- Notifications -->
            @push('scripts')
                <script>
                    $(document).ready(function() {
                        @if (session('success'))
                            toastr.success("{{ session('success') }}");
                        @endif
                        @if (session('error'))
                            toastr.error("{{ session('error') }}");
                        @endif
                        @if (session('warning'))
                            toastr.warning("{{ session('warning') }}");
                        @endif
                        @if (session('info'))
                            toastr.info("{{ session('info') }}");
                        @endif
                    });
                </script>
            @endpush

            <!-- Breadcrumb -->
            <div class="row m-1">
                <div class="col-12 ">
                    <h4 class="main-title mb-3">Project Report</h4>
                </div>
            </div>

            <!-- Filter Form -->
            <div class="card mb-3 p-3">
                <form method="GET" action="{{ route('project.report') }}" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label for="from_date" class="form-label">From Date</label>
                        <input type="date" name="from_date" id="from_date" value="{{ request('from_date') }}"
                            class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label for="to_date" class="form-label">To Date</label>
                        <input type="date" name="to_date" id="to_date" value="{{ request('to_date') }}"
                            class="form-control">
                    </div>
                    <div class="col-md-6 d-flex gap-2">
                        <button type="submit" class="btn mt-3" style="background-color: #7cb342; color: #ffffff;">Filter</button>
                        <button type="submit" name="download_pdf" value="1" class="btn btn-danger mt-3">Download
                            PDF</button>
                        <a href="{{ route('project.report') }}" class="btn btn-secondary mt-3">Reset</a>
                    </div>
                </form>
            </div>

            <!-- Data Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card ">
                        <div class="card-body p-0">
                            <div class="app-datatable-default overflow-auto">
                                <table id="example" class="display app-data-table default-data-table">
                                    <thead>
                                        <tr>
                                            <th>Project Name</th>
                                            <th>Client Name</th>
                                            <th>State</th>
                                            <th>Company Name</th>
                                            <th>Created</th>
                                            <th>Officer Name</th>
                                            {{-- <th>Action</th> --}}
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($projects as $project)
                                            <tr>
                                                <td>{{ $project->project_name }}</td>
                                                <td>{{ $project->client_name ?? '-' }}</td>
                                                <td>{{ $project->state->state_name ?? '-' }}</td>
                                                <td>{{ $project->company_name ?? '-' }}</td>
                                                <td>{{ $project->created_at->format('Y-m-d H:i:s') }}</td>
                                                <td>{{ $project->fieldOfficer->name ?? '-' }}</td>
                                                {{-- <td>
                                                    <a href="{{ route('trees.edit', $project->id) }}"
                                                        class="btn btn-success btn-sm d-inline-flex align-items-center gap-2">
                                                        <i class="ti ti-edit"></i>
                                                        <span>Edit Tree</span>
                                                    </a>
                                                </td> --}}
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center text-muted">No projects found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection