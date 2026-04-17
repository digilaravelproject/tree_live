@extends('layouts.app')
@section('title')
    | {{ $page_title }}
@endsection

@section('content')
    <main>
        <div class="container-fluid">

            {{-- ✅ Breadcrumb --}}
            <div class="row m-1">
                <div class="col-12">
                    <h4 class="main-title mb-3">District List</h4>
                </div>
            </div>

            {{-- ✅ Filter and Export --}}
            <form method="GET" class="row mb-3">
                <div class="col-md-4">
                    <select name="state_id" class="form-select" onchange="this.form.submit()">
                        <option value="">All States</option>
                        @foreach ($states as $state)
                            <option value="{{ $state->id }}" {{ $state_id == $state->id ? 'selected' : '' }}>
                                {{ $state->state_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('district.export', ['state_id' => $state_id]) }}" class="btn w-100" style="background-color: #7cb342; color: #ffffff;">
                        Excel
                    </a>
                </div>
            </form>

            {{-- ✅ Data Table --}}
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header text-white d-flex justify-content-between align-items-center" style="background-color: #7cb342;">
                            <h5 class="mb-0">All Districts</h5>
                            <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addDistrictModal">
                                + Add District
                            </button>
                        </div>

                        <div class="card-body p-0">
                            <div class="app-datatable-default overflow-auto">
                                <table id="example" class="display app-data-table default-data-table w-100">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>District Name</th>
                                            <th>State Name</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($districts as $index => $district)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $district->district_name ?? '-' }}</td>
                                                <td>{{ $district->state->state_name ?? '-' }}</td>
                                                <td>
                                                    <a href="#" class="btn icon-btn b-r-4"
                                                        style="background-color: rgba(124, 179, 66, 0.1);"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editDistrictModal{{ $district->id }}">
                                                        <i class="ti ti-edit" style="color: #7cb342;"></i>
                                                    </a>

                                                    <form action="{{ route('district.destroy', $district->id) }}"
                                                        method="POST" style="display:inline-block;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-light-danger icon-btn b-r-4"
                                                            onclick="return confirm('Are you sure to delete this district?')">
                                                            <i class="ti ti-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>

                                            {{-- ✅ Edit Modal --}}
                                            @push('modals')
                                                <div class="modal fade" id="editDistrictModal{{ $district->id }}"
                                                    tabindex="-1">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <form action="{{ route('district.update', $district->id) }}"
                                                            method="POST" class="modal-content">
                                                            @csrf
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Edit District</h5>
                                                                <button type="button" class="btn-close"
                                                                    data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label>State</label>
                                                                    <select name="state_id" class="form-select" required>
                                                                        @foreach ($states as $state)
                                                                            <option value="{{ $state->id }}"
                                                                                {{ $district->state_id == $state->id ? 'selected' : '' }}>
                                                                                {{ $state->state_name }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label>District Name</label>
                                                                    <input type="text" name="district_name"
                                                                        class="form-control"
                                                                        value="{{ $district->district_name }}" required>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button class="btn btn-secondary"
                                                                    data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="btn" style="background-color: #7cb342; color: #ffffff;">Update</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            @endpush
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ✅ Add District Modal --}}
        <div class="modal fade" id="addDistrictModal" tabindex="-1">
            <div class="modal-dialog">
                <form action="{{ route('district.store') }}" method="POST" class="modal-content">
                    @csrf
                    <div class="modal-header">
                        <h5>Add New District</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label>State</label>
                            <select name="state_id" class="form-select" required>
                                @foreach ($states as $state)
                                    <option value="{{ $state->id }}">{{ $state->state_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>District Name</label>
                            <input type="text" name="district_name" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn" style="background-color: #7cb342; color: #ffffff;">Save</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ✅ Keep modals INSIDE main --}}
        @stack('modals')
    </main>
@endsection

{{-- ✅ Scripts --}}
@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            try {
                if (window.initAppLayout) window.initAppLayout(); // loader trigger

                // Initialize DataTable after small delay
                setTimeout(() => {
                    if (typeof $ !== "undefined" && $.fn.DataTable) {
                        $('#example').DataTable({
                            pageLength: 10,
                            responsive: true,
                            ordering: true,
                            language: {
                                search: "Search District:"
                            }
                        });
                    }
                }, 300);

                // Prevent broken JS from global files
                window.ApexCharts = window.ApexCharts || function() {};
                window.FullCalendar = window.FullCalendar || function() {};
            } catch (e) {
                console.warn("District page script error:", e);
            }
        });
    </script>
@endpush