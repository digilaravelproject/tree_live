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
                        @if (session('warning'))
                            toastr.warning("{{ session('warning') }}");
                        @endif
                        @if (session('info'))
                            toastr.info("{{ session('info') }}");
                        @endif
                    });
                </script>
            @endpush

            <div class="row m-1">
                <div class="col-12 ">
                    <h4 class="main-title mb-3">Project List</h4>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card ">
                        <div class="card-body p-0">
                            <div class="app-datatable-default overflow-auto">

                                <table id="example" class="display app-data-table default-data-table text-nowrap w-100">
                                    <thead>
                                        <tr>
                                            <th>Project Name</th>
                                            <th>Client Name</th>
                                            <th>State</th>
                                            <th>Company Name</th>
                                            <th>Created</th>
                                            <th>Officer Name</th>
                                            <th>Project Setting</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($projects as $project)
                                            <tr>
                                                <td>{{ $project->project_name }}</td>
                                                <td>{{ $project->client_name ?? '-' }}</td>
                                                <td>{{ $project->state->state_name ?? '-' }}</td>
                                                <td>{{ $project->company_name ?? '-' }}</td>
                                                <td>{{ $project->created_at ? \Carbon\Carbon::parse($project->created_at)->format('Y-m-d') : '-' }}
                                                </td>

                                                <td>
                                                    @php
                                                        $officerIds = json_decode($project->field_officer_id, true);
                                                    @endphp

                                                    @if (is_array($officerIds) && count($officerIds) > 0)
                                                        @php
                                                            $officers = \App\Models\User::whereIn('id', $officerIds)
                                                                ->pluck('name')
                                                                ->toArray();
                                                        @endphp

                                                        <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                                                            @foreach ($officers as $name)
                                                                <span
                                                                    style="
                                                                    background-color: #7cb342;
                                                                    color: #fff;
                                                                    padding: 4px 8px;
                                                                    border-radius: 4px;
                                                                    font-size: 12px;
                                                                    white-space: nowrap;
                                                                    display: inline-block;
                                                                ">
                                                                    {{ $name }}
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        -
                                                    @endif
                                                </td>

                                                <td>
                                                    <div style="display: flex; gap: 5px;">
                                                        <a href="{{ route('projects.settings', $project->id) }}"
                                                            class="btn icon-btn b-r-4 btn-sm"
                                                            style="background-color: rgba(124, 179, 66, 0.1);"
                                                            title="Project Settings">
                                                            <i class="ti ti-settings" style="color: #7cb342;"></i>
                                                        </a>
                                                        <a href="{{ route('projects.viewSettings', $project->id) }}"
                                                            class="btn icon-btn b-r-4 btn-sm"
                                                            style="background-color: rgba(124, 179, 66, 0.1);"
                                                            title="View Settings">
                                                            <i class="ti ti-eye" style="color: #7cb342;"></i>
                                                        </a>
                                                    </div>
                                                </td>

                                                <td>
                                                    <div style="display: flex; gap: 5px;">
                                                        <a href="{{ route('projects.edit', $project->id) }}"
                                                            class="btn icon-btn b-r-4 btn-sm"
                                                            style="background-color: rgba(124, 179, 66, 0.1);">
                                                            <i class="ti ti-edit" style="color: #7cb342;"></i>
                                                        </a>

                                                        <form action="{{ route('projects.delete', $project->id) }}"
                                                            method="POST" id="delete-form-{{ $project->id }}"
                                                            style="display:inline-block;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="button"
                                                                class="btn btn-light-danger icon-btn b-r-4 btn-sm"
                                                                title="Delete Project"
                                                                onclick="confirmDelete({{ $project->id }}, '{{ addslashes($project->project_name) }}')">
                                                                <i class="ti ti-trash"></i>
                                                            </button>
                                                        </form>

                                                    </div>
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

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            function confirmDelete(id, name) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You are about to delete the project: " + name +
                        ". All related trees and settings will be permanently deleted!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('delete-form-' + id).submit();
                    }
                });
            }
        </script>
    </main>
@endsection
