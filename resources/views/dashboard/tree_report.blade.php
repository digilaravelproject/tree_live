@extends('layouts.app')
@section('title')
    | {{ $page_title }}
@endsection

@section('content')


    <main>
        <div class="container-fluid">

            <!-- Page Title -->
            <div class="row m-1">
                <div class="col-12 ">
                    <h4 class="main-title mb-3">Tree Report</h4>
                </div>
            </div>

            <!-- Filter Form -->
            <div class="card mb-3 p-3">
                <form method="GET" action="{{ route('tree.report') }}" class="row g-3 align-items-end">
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
                        <a href="{{ route('tree.report') }}" class="btn btn-secondary mt-3">Reset</a>
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
                                            <th>ID</th>
                                            <th>Ward Plot No</th>
                                            <th>Tree No</th>
                                            <th>Tree Name</th>
                                            <th>Scientific Name</th>
                                            <th>Family</th>
                                            <th>Girth</th>
                                            <th>Height</th>
                                            <th>Condition</th>
                                            <th>Location</th>
                                            <th>Date Added</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($trees as $tree)
                                            <tr>
                                                <td>{{ $tree->id }}</td>
                                                <td>{{ $tree->ward_plot_no }}</td>
                                                <td>{{ $tree->tree_no ?? '-' }}</td>
                                                <td>{{ $tree->tree->name ?? '-' }}</td>
                                                <td>{{ $tree->scientific->scientific_name ?? '-' }}</td>
                                                <td>{{ $tree->familyRelation->family_name ?? '-' }}</td>
                                                <td>{{ $tree->girth ?? '-' }}</td>
                                                <td>{{ $tree->height ?? '-' }}</td>
                                                <td>{{ $tree->condition ?? '-' }}</td>
                                                <td>{{ $tree->address ?? '-' }}</td>
                                                <td>{{ $tree->created_at->format('Y-m-d H:i:s') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="11" class="text-center text-muted">No trees found</td>
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