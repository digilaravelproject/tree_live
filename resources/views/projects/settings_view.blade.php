@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-5">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>Configuration View: {{ $project->project_name }}</h5>
                        <a href="{{ route('projects.settings', $project->id) }}" class="btn btn-sm" style="background-color: #7cb342; color: #ffffff;">
                            <i class="ti ti-pencil"></i> Edit Settings
                        </a>
                    </div>
                    <div class="card-body">

                        {{-- 
                        Display Basic Project Info (Accuracy & Add Second)
                    --}}
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="p-3 border rounded bg-light">
                                    <strong>Accuracy:</strong> {{ $project->accuracy ?? 'Default (10)' }} meters
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 border rounded bg-light">
                                    <strong>Add Second:</strong> {{ $project->add_second ?? 'N/A' }}
                                </div>
                            </div>
                        </div>

                        {{-- 
                        Configuration Table
                    --}}
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="text-white" style="background-color: #7cb342;">
                                    <tr>
                                        <th style="width: 30%;">Field Name</th>
                                        <th style="width: 20%;">Status</th>
                                        <th style="width: 25%;">Minimum Value</th>
                                        <th style="width: 25%;">Maximum Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        // Wahi list dobara use karenge taki label sahi dikhe
                                        $fields = [
                                            ['key' => 'ward_plot_no', 'label' => 'Plot/Property Number'],
                                            ['key' => 'all_captured_images', 'label' => 'Image (Count)'],
                                            ['key' => 'ward_number', 'label' => 'Ward Number'],
                                            ['key' => 'tree_name', 'label' => 'Tree Name'],
                                            ['key' => 'girth', 'label' => 'Girth'],
                                            ['key' => 'height', 'label' => 'Height'],
                                            ['key' => 'age', 'label' => 'Tree Age'],
                                            ['key' => 'canopy', 'label' => 'Canopy'],
                                            ['key' => 'condition', 'label' => 'Condition'],
                                            ['key' => 'address', 'label' => 'Address'],
                                            ['key' => 'landmark', 'label' => 'Landmark'],
                                            ['key' => 'concern_person', 'label' => 'Concern Person'],
                                            ['key' => 'concern_person_email', 'label' => 'Concern Person Email'],
                                            ['key' => 'concern_person_phone', 'label' => 'Concern Person Phone'],
                                            ['key' => 'ownership', 'label' => 'Ownership'],
                                            ['key' => 'remark', 'label' => 'Remark'],
                                            ['key' => 'ratio', 'label' => 'Ratio'],
                                        ];
                                    @endphp

                                    @foreach ($fields as $field)
                                        @php
                                            // Find setting from relation
                                            $setting = $project->settings->where('field_key', $field['key'])->first();
                                            $isRequired = $setting ? $setting->is_required : 0;
                                        @endphp
                                        <tr>
                                            <td class="fw-bold">{{ $field['label'] }}</td>

                                            <td>
                                                @if ($isRequired)
                                                    <span class="badge" style="background-color: #7cb342;">Required</span>
                                                @else
                                                    <span class="badge bg-secondary">Not Required</span>
                                                @endif
                                            </td>

                                            <td>
                                                @if ($setting && $setting->min_value)
                                                    {{ $setting->min_value }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>

                                            <td>
                                                @if ($setting && $setting->max_value)
                                                    {{ $setting->max_value }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3 text-end">
                            <a href="{{ route('project.list') }}" class="btn btn-secondary">Back to Projects</a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection