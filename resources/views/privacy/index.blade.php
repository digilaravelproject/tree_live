@extends('layouts.app')
@section('title', 'Privacy Policies')
@section('content')
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 style="color: #7cb342;">Privacy Policies</h3>
            <a href="{{ route('privacy.create') }}" class="btn btn-primary" style="background-color: #7cb342; border-color: #7cb342;">Add New Policy</a>
        </div>
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <table class="table table-bordered align-middle mb-0">
                    <thead style="background-color: #7cb342;">
                        <tr>
                            <th style="color: white;">Title</th>
                            <th width="30%" style="color: white;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($policies as $policy)
                            <tr>
                                <td>{{ $policy->title }}</td>
                                <td>
                                    <a href="{{ route('privacy.edit', $policy->id) }}" class="btn btn-info btn-sm" style="background-color: #7cb342; border-color: #7cb342; color: white;">Edit</a>
                                    <form action="{{ route('privacy.destroy', $policy->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm"
                                            onclick="return confirm('Delete this policy?')">Delete</button>
                                    </form>
                                    <button class="btn btn-secondary btn-sm"
                                        onclick="printPolicy({{ $policy->id }})">Print</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center py-3">No privacy policies found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script>
        function printPolicy(id) {
            const printWindow = window.open(`/privacy/${id}/print`, '_blank', 'width=800,height=600');
            printWindow.focus();
        }
    </script>
@endsection