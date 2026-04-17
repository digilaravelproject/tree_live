@extends('layouts.app')
@section('title', '| Edit Project')

@section('content')
    <main>
        <div class="container-fluid">
            <h4 class="main-title mb-3">Edit Project</h4>

            <form action="{{ route('projects.update', $project->id) }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Project Name</label>
                        <input type="text" name="project_name" class="form-control"
                            value="{{ old('project_name', $project->project_name) }}" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Client Name</label>
                        <input type="text" name="client_name" class="form-control"
                            value="{{ old('client_name', $project->client_name) }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>State</label>
                        <select name="state_id" class="form-select" required>
                            <option value="">Select State</option>
                            @foreach ($states as $state)
                                <option value="{{ $state->id }}"
                                    {{ $project->state_id == $state->id ? 'selected' : '' }}>
                                    {{ $state->state_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Company Name</label>
                        <input type="text" name="company_name" class="form-control"
                            value="{{ old('company_name', $project->company_name) }}">
                    </div>

                    <!-- ✅ Multiple select for Field Officer -->
                    <div class="col-md-6 mb-3">
                        <label>Field Officer</label>
                        @php
                            $selectedOfficers = is_array(json_decode($project->field_officer_id, true))
                                ? json_decode($project->field_officer_id, true)
                                : [];
                        @endphp
                        <select id="fieldOfficerName" name="field_officer_id[]" class="form-select" multiple required>
                            @foreach ($officers as $officer)
                                <option value="{{ $officer->id }}"
                                    {{ in_array($officer->id, $selectedOfficers) ? 'selected' : '' }}>
                                    {{ $officer->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                      <div class="col-md-6">
                                    <label for="wardNo" class="form-label">Ward No <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="ward_no" class="form-control" id="wardNo"
                                        placeholder="Enter ward number" value="{{ old('ward_no', $project->ward_no) }}" required>
                                    <div class="invalid-feedback">Please enter ward number.</div>
                                </div>
                    
                </div>

                <button type="submit" class="btn" style="background-color: #7cb342; color: #ffffff;">Update Project</button>
            </form>
        </div>
    </main>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            new Choices('#fieldOfficerName', {
                removeItemButton: true,
                searchEnabled: true,
                placeholderValue: 'Select field officer(s)',
                searchPlaceholderValue: 'Type to search...',
            });
        });
    </script>
@endsection