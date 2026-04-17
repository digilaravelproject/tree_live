@extends('layouts.app')
@section('title')
    | Taluka Master
@endsection

@section('content')
    <div class="container-fluid py-5">
        <h4 class="mb-4">Taluka Master</h4>

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
            <div class="col-md-4">
                <select name="district_id" class="form-select" onchange="this.form.submit()">
                    <option value="">All Districts</option>
                    @foreach ($districts as $district)
                        <option value="{{ $district->id }}" {{ $district_id == $district->id ? 'selected' : '' }}>
                            {{ $district->district_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <a href="{{ route('tahsil.export', ['state_id' => $state_id, 'district_id' => $district_id]) }}"
                    class="btn w-100" style="background-color: #7cb342; color: #ffffff;">
                    Excel
                </a>
            </div>
        </form>

        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5>Taluka List</h5>
                <button class="btn" style="background-color: #7cb342; color: #ffffff;" data-bs-toggle="modal" data-bs-target="#addTahsilModal">+ Add
                    Taluka</button>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Taluka Name</th>
                            <th>District</th>
                            <th>State</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tahsils as $index => $tahsil)
                            <tr>
                                <td>{{ $tahsils->firstItem() + $index }}</td>
                                <td>{{ $tahsil->tahsil_name }}</td>
                                <td>{{ $tahsil->district->district_name ?? 'N/A' }}</td>
                                <td>{{ $tahsil->state->state_name ?? 'N/A' }}</td>
                                <td>
                                    <a href="{{ route('tahsil.edit', $tahsil->id) }}"
                                        class="btn btn-sm" style="background-color: #9ccc65; color: #ffffff;">Edit</a>


                                    <form action="{{ route('tahsil.destroy', $tahsil->id) }}" method="POST"
                                        class="d-inline">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-danger"
                                            onclick="return confirm('Are you sure?')">Delete</button>
                                    </form>
                                </td>
                            </tr>

                            {{-- ✅ Edit Modal --}}
                            <div class="modal fade" id="editTahsilModal{{ $tahsil->id }}" tabindex="-1"
                                aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <form action="{{ route('tahsil.update', $tahsil->id) }}" method="POST"
                                        class="modal-content">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Taluka</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label>State</label>
                                                <select name="state_id" class="form-select state-select"
                                                    data-id="{{ $tahsil->id }}" required>
                                                    <option value="">Select State</option>
                                                    @foreach ($states as $state)
                                                        <option value="{{ $state->id }}"
                                                            {{ $state->id == $tahsil->state_id ? 'selected' : '' }}>
                                                            {{ $state->state_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label>District</label>
                                                <select name="district_id"
                                                    class="form-select district-select district-list-{{ $tahsil->id }}"
                                                    required>
                                                    @foreach ($districts->where('state_id', $tahsil->state_id) as $district)
                                                        <option value="{{ $district->id }}"
                                                            {{ $district->id == $tahsil->district_id ? 'selected' : '' }}>
                                                            {{ $district->district_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label>Taluka Name</label>
                                                <input type="text" name="tahsil_name" value="{{ $tahsil->tahsil_name }}"
                                                    class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn" style="background-color: #7cb342; color: #ffffff;">Update</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No Taluka's found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="d-flex justify-content-center mt-3">
                    {{ $tahsils->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ Add Modal -->
    <div class="modal fade" id="addTahsilModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('tahsil.store') }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Taluka</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>State</label>
                        <select name="state_id" class="form-select" id="addStateSelect" required>
                            <option value="">Select State</option>
                            @foreach ($states as $state)
                                <option value="{{ $state->id }}">{{ $state->state_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>District</label>
                        <select name="district_id" class="form-select" id="addDistrictSelect" required>
                            <option value="">Select District</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Taluka Name</label>
                        <input type="text" name="tahsil_name" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn" style="background-color: #7cb342; color: #ffffff;">Save</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ✅ JS for Dynamic District Loading in Edit Modal --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const allDistricts = @json($districts);

            // Handle Edit Modal State Change
            document.querySelectorAll('.state-select').forEach(select => {
                select.addEventListener('change', function() {
                    const stateId = this.value;
                    const tahsilId = this.dataset.id;
                    const districtDropdown = document.querySelector('.district-list-' + tahsilId);

                    districtDropdown.innerHTML = '<option value="">Select District</option>';

                    const filteredDistricts = allDistricts.filter(d => d.state_id == stateId);
                    filteredDistricts.forEach(d => {
                        const opt = document.createElement('option');
                        opt.value = d.id;
                        opt.textContent = d.district_name;
                        districtDropdown.appendChild(opt);
                    });
                });
            });

            // Handle Add Modal State Change
            const addStateSelect = document.getElementById('addStateSelect');
            const addDistrictSelect = document.getElementById('addDistrictSelect');

            if (addStateSelect && addDistrictSelect) {
                addStateSelect.addEventListener('change', function() {
                    const stateId = this.value;
                    addDistrictSelect.innerHTML = '<option value="">Select District</option>';

                    const filteredDistricts = allDistricts.filter(d => d.state_id == stateId);
                    filteredDistricts.forEach(d => {
                        const opt = document.createElement('option');
                        opt.value = d.id;
                        opt.textContent = d.district_name;
                        addDistrictSelect.appendChild(opt);
                    });
                });
            }
        });
    </script>
@endsection