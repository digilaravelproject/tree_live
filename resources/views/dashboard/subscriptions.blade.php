@extends('layouts.app')
@section('title') | {{ $page_title }} @endsection

@section('content')
<main>
    <div class="container-fluid">
        <div class="row m-1">
            <div class="col-12">
                <h4 class="main-title mb-3">Tree Subscription History</h4>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body p-0">
                        <div class="app-datatable-default overflow-auto">
                            <table id="example" class="display app-data-table default-data-table text-nowrap w-100">
                                <thead>
                                    <tr>
                                        <th>User Name</th>
                                        <th>Tree No</th>
                                        <th>Tree Name</th>
                                        <th>Payment ID</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                  @foreach ($subscriptions as $sub)
    <tr>
        <td>{{ $sub->user->name ?? 'N/A' }}</td>
        <td>{{ $sub->tree->tree_no ?? 'N/A' }}</td>
        
        {{-- Yahan ID (3) ki jagah Name (Banyan) aayega --}}
        <td>{{ $sub->tree->treeDetail->name ?? ($sub->tree->tree_name ?? 'N/A') }}</td>
        
        <td><span class="badge bg-success">{{ $sub->payment_id }}</span></td>
        <td>₹{{ number_format($sub->amount, 2) }}</td>
        <td>{{ $sub->created_at->format('Y-m-d H:i') }}</td>
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