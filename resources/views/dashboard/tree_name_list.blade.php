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
                    <h4 class="main-title mb-3">Tree List</h4>
                </div>
            </div>

            {{-- ✅ Data Table --}}
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body p-0">
                            <div class="app-datatable-default overflow-auto">
                                <table id="example" class="display app-data-table default-data-table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Tree Name</th>
                                            <th>Scientific Name</th>
                                            <th>Family Name</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($trees as $index => $tree)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $tree->name ?? '-' }}</td>
                                                <td>{{ $tree->scientific->scientific_name ?? '-' }}</td>
                                                <td>{{ $tree->family->family_name ?? '-' }}</td>
                                                <td>
                                                    <a href="{{ route('list.trees.edit', $tree->id) }}"
                                                        class="btn btn-light-success icon-btn b-r-4">
                                                        <i class="ti ti-edit text-success"></i>
                                                    </a>

                                                    <form action="{{ route('list.trees.destroy', $tree->id) }}"
                                                        method="POST" style="display:inline-block;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-light-danger icon-btn b-r-4"
                                                            onclick="return confirm('Are you sure to delete this tree?')">
                                                            <i class="ti ti-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>

                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div> {{-- /datatable --}}
                        </div> {{-- /card-body --}}
                    </div> {{-- /card --}}
                </div>
            </div>
        </div>
    </main>
@endsection
