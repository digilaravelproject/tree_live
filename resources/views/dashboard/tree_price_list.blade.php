@extends('layouts.app')

@section('title')
    | {{ $page_title }}
@endsection

@section('content')
    <main>
        <div class="container-fluid">

            <!-- Header -->
            <div class="row m-1 mb-3 align-items-center">
                <div class="col-6">
                    <h4 class="main-title mb-0">{{ $page_title }}</h4>
                </div>
                <div class="col-6 text-end">
                    <a href="{{ route('tree.price.create') }}" class="btn" style="background-color: #7cb342; color: #ffffff;">
                        <i class="bi bi-plus-lg"></i> Update Price
                    </a>
                </div>
            </div>

            <!-- Table Card -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body p-0">
                            <div class="app-datatable-default overflow-auto">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Tree Price Amount</th>
                                            <th>Status</th>
                                            <th>Date Set</th>
                                            <th class="text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($prices as $key => $item)
                                            <tr class="{{ $item->is_active ? 'table-success' : '' }}">
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    <h5 class="mb-0 fw-bold">₹ {{ number_format($item->price, 2) }}</h5>
                                                </td>
                                                <td>
                                                    @if ($item->is_active == 1)
                                                        <span class="badge" style="background-color: #7cb342;">Current Active</span>
                                                    @else
                                                        <span class="badge bg-secondary">History</span>
                                                    @endif
                                                </td>
                                                <td>{{ $item->created_at->format('d M Y, h:i A') }}</td>
                                                <td class="text-end">
                                                    <div class="d-flex justify-content-end gap-2">
                                                        <!-- Switch Active Button (Only if Inactive) -->
                                                        @if ($item->is_active == 0)
                                                            <form action="{{ route('tree.price.active', $item->id) }}"
                                                                method="POST">
                                                                @csrf
                                                                <button type="submit"
                                                                    class="btn btn-sm"
                                                                    style="border: 1px solid #7cb342; color: #7cb342;"
                                                                    title="Re-activate this price">
                                                                    Make Active
                                                                </button>
                                                            </form>
                                                        @else
                                                            <button class="btn btn-sm disabled" style="background-color: #7cb342; color: #ffffff;">Active</button>
                                                        @endif

                                                        <!-- Delete Button -->
                                                        <form action="{{ route('tree.price.delete', $item->id) }}"
                                                            method="POST"
                                                            onsubmit="return confirm('Delete this price record?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger">
                                                                <i class="ti ti-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-4">
                                                    No price set yet. Please click "Update Price" to set the first price.
                                                </td>
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