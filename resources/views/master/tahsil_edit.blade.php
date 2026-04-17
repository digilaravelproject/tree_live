@extends('layouts.app')
@section('title')
    | Edit Tahsil
@endsection

@section('content')
    <div class="container py-5">
        <h4 class="mb-4">Edit Tahsil</h4>

        <form action="{{ route('tahsil.update', $tahsil->id) }}" method="POST">
            @csrf
            <div class="card">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">State</label>
                            <select name="state_id" id="stateSelect" class="form-select" required>
                                <option value="">Select State</option>
                                @foreach ($states as $state)
                                    <option value="{{ $state->id }}"
                                        {{ $tahsil->state_id == $state->id ? 'selected' : '' }}>
                                        {{ $state->state_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">District</label>
                            <select name="district_id" id="districtSelect" class="form-select" required>
                                @foreach ($districts as $district)
                                    <option value="{{ $district->id }}"
                                        {{ $tahsil->district_id == $district->id ? 'selected' : '' }}>
                                        {{ $district->district_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tahsil Name</label>
                        <input type="text" name="tahsil_name" class="form-control" value="{{ $tahsil->tahsil_name }}"
                            required>
                    </div>
                </div>

                <div class="card-footer d-flex justify-content-between">
                    <a href="{{ route('tahsil.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-success">Update</button>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            $('#stateSelect').on('change', function() {
                let stateId = $(this).val();
                $('#districtSelect').html('<option value="">Loading...</option>');
                if (stateId) {
                    $.get(`/districts/by-state/${stateId}`, function(data) {
                        $('#districtSelect').html('<option value="">Select District</option>');
                        data.forEach(function(d) {
                            $('#districtSelect').append(
                                `<option value="${d.id}">${d.district_name}</option>`);
                        });
                    });
                }
            });
        </script>
    @endpush
@endsection
