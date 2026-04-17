@extends('layouts.app')
@section('title')
    | {{ $page_title }}
@endsection

@section('content')
    <main>
        <div class="container-fluid">

            @push('scripts')
                <script>
                    $(document).ready(function() {
                        @if (session('success'))
                            toastr.success("{{ session('success') }}");
                        @endif
                        @if (session('error'))
                            toastr.error("{{ session('error') }}");
                        @endif
                    });
                </script>
            @endpush

            <div class="row m-1">
                <div class="col-12">
                    <h4 class="main-title mb-3">Tree List</h4>
                </div>
            </div>

            {{-- FILTER SECTION --}}
            <div class="row m-1 mb-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form action="{{ route('tree.list') }}" method="GET" id="filterForm">
                                <div class="row align-items-end">
                                    {{-- Project Filter --}}
                                    <div class="col-md-3 mb-2">
                                        <label for="project_id" class="form-label">Project</label>
                                        <select name="project_id" class="form-control">
                                            <option value="">-- All Projects --</option>
                                            @foreach ($projects as $proj)
                                                <option value="{{ $proj->id }}"
                                                    {{ request('project_id') == $proj->id ? 'selected' : '' }}>
                                                    {{ $proj->project_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Tree No Filter --}}
                                    <div class="col-md-2 mb-2">
                                        <label for="tree_no" class="form-label">Tree No</label>
                                        <input type="text" name="tree_no" class="form-control"
                                            value="{{ request('tree_no') }}" placeholder="Search Tree No">
                                    </div>

                                    {{-- Date From --}}
                                    <div class="col-md-2 mb-2">
                                        <label for="from_date" class="form-label">From Date</label>
                                        <input type="date" name="from_date" class="form-control"
                                            value="{{ request('from_date') }}">
                                    </div>

                                    {{-- Date To --}}
                                    <div class="col-md-2 mb-2">
                                        <label for="to_date" class="form-label">To Date</label>
                                        <input type="date" name="to_date" class="form-control"
                                            value="{{ request('to_date') }}">
                                    </div>

                                    {{-- Buttons --}}
                                    <div class="col-md-3 mb-2 d-flex gap-2">
                                        <button type="submit" class="btn w-100" style="background-color: #7cb342; color: #ffffff;">
                                            <i class="ti ti-filter"></i> Filter
                                        </button>
                                        <a href="{{ route('tree.list') }}" class="btn btn-secondary" title="Reset">
                                            <i class="ti ti-refresh"></i>
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- EXPORT BUTTONS SECTION --}}
            <div class="row m-1 mb-3">
                
            
                <div class="col-12 text-end">
                        {{-- Zip Images Export --}}
<a href="{{ route('export.tree.zip', request()->all()) }}" class="btn btn-sm" style="background-color: #7cb342; color: #ffffff;">
    <i class="ti ti-file-zip"></i> Download Images (Zip)
</a>
                    
                    {{-- KML Export (Submits Filter Form to KML Route via JS or uses current URL params) --}}
                    <a href="{{ route('generate.all.kml', request()->all()) }}" class="btn btn-sm" style="background-color: #7cb342; color: #ffffff;">
                        <i class="ti ti-map"></i> Export KML
                    </a>

                    {{-- Excel Export --}}
                    <a href="{{ route('export.tree.excel', request()->all()) }}" class="btn btn-sm" style="background-color: #558b2f; color: #ffffff;">
                        <i class="ti ti-file-spreadsheet"></i> Export Excel
                    </a>

                    {{-- PDF Export --}}
                    <a href="{{ route('export.tree.pdf', request()->all()) }}" class="btn btn-danger btn-sm">
                        <i class="ti ti-file-type-pdf"></i> Export PDF
                    </a>
                </div>
            </div>

            {{-- DATA TABLE --}}
            <div class="row">
                <div class="col-12">
                    <div class="card ">
                        <div class="card-body p-0">
                            <div class="app-datatable-default overflow-auto">
                                <table id="example" class="display app-data-table default-data-table">
                                    <thead>
                                        <tr>
                                            <th>Tree No</th>
                                            <th>Image</th>
                                            <th>Common Name</th>
                                            <th>Scientific Name</th>
                                            <th>Project Name</th>
                                            <th>Ward/Plot No</th>
                                            <th>Girth / Height</th>
                                            <th>Condition</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($trees as $tree)
                                            <tr>
                                                <td>{{ $tree->tree_no }}</td>
                                                <td>
                                                    @if (!empty($tree->all_captured_images))
                                                        @php
                                                            $images = is_string($tree->all_captured_images)
                                                                ? json_decode($tree->all_captured_images, true)
                                                                : $tree->all_captured_images;
                                                        @endphp

                                                        @if (is_array($images) && count($images) > 0)
                                                            <div class="d-flex gap-1 flex-wrap">
                                                                @foreach ($images as $image)
                                                                    <a href="{{ asset($image) }}" target="_blank">
                                                                        <img src="{{ asset($image) }}" alt="Tree"
                                                                            style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                                                    </a>
                                                                @endforeach
                                                            </div>
                                                        @else
                                                            <span>-</span>
                                                        @endif
                                                    @else
                                                        <span>-</span>
                                                    @endif
                                                </td>
                                                <td>{{ $tree->tree->name ?? $tree->tree_name }}</td>
                                                <td>{{ $tree->scientific->scientific_name ?? '-' }}</td>
                                                <td>{{ $tree->project->project_name ?? '-' }}</td>
                                                <td>{{ $tree->ward_plot_no ?? '-' }}</td>
                                                <td>
                                                    G: {{ $tree->girth ?? '-' }} <br>
                                                    H: {{ $tree->height ?? '-' }}
                                                </td>
                                                <td>
                                                    @if ($tree->condition == 'Healthy')
                                                        <span class="badge" style="background-color: #7cb342;">Healthy</span>
                                                    @else
                                                        <span
                                                            class="badge bg-warning text-dark">{{ $tree->condition ?? '-' }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('trees.edit', $tree->id) }}"
                                                        class="btn btn-sm d-inline-flex align-items-center gap-2" style="background-color: #7cb342; color: #ffffff;">
                                                        <i class="ti ti-edit"></i>
                                                        <span>Edit</span>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
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