@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <div class="card">
            <div class="card-header bg-white">
                <h4>Project Setting for: {{ $project->project_name }}</h4>
            </div>
            <div class="card-body">

                <form action="{{ route('projects.updateSettings', $project->id) }}" method="POST">
                    @csrf

                    @php
                        $fields = [
                            ['key' => 'ward_plot_no', 'label' => 'Plot/Property Number', 'has_range' => false],
                            [
                                'key' => 'all_captured_images',
                                'label' => 'Image',
                                'has_range' => true,
                                'range_label' => 'Image',
                            ],
                            ['key' => 'ward_number', 'label' => 'Ward Number', 'has_range' => false],
                            ['key' => 'tree_name', 'label' => 'Tree Name', 'has_range' => false],
                            ['key' => 'girth', 'label' => 'Girth', 'has_range' => true, 'range_label' => 'Girth'],
                            ['key' => 'height', 'label' => 'Height', 'has_range' => true, 'range_label' => 'Height'],
                            ['key' => 'age', 'label' => 'Tree Age', 'has_range' => true, 'range_label' => 'Age'],
                            ['key' => 'canopy', 'label' => 'Canopy', 'has_range' => true, 'range_label' => 'Canopy'],
                            ['key' => 'condition', 'label' => 'Condition', 'has_range' => false],
                            ['key' => 'address', 'label' => 'Address', 'has_range' => false],
                            ['key' => 'landmark', 'label' => 'Landmark', 'has_range' => false],
                            ['key' => 'concern_person', 'label' => 'Concern Person', 'has_range' => false],
                            ['key' => 'concern_person_email', 'label' => 'Concern Person Email', 'has_range' => false],
                            [
                                'key' => 'concern_person_phone',
                                'label' => 'Concern Person Phone Number',
                                'has_range' => false,
                            ],
                            ['key' => 'ownership', 'label' => 'Ownership', 'has_range' => false],
                            ['key' => 'remark', 'label' => 'Remark', 'has_range' => false],
                            ['key' => 'ratio', 'label' => 'Ratio', 'has_range' => false],
                        ];
                        
                        // Fields that are disabled and must be required
                        $disabledFields = ['ward_plot_no', 'address', 'condition', 'landmark', 'concern_person_phone', 'remark', 'ratio'];
                    @endphp

                    @foreach ($fields as $field)
                        @php
                            // Check existing values from DB
                            $isRequired = $project->getSettingVal($field['key'], 'is_required');
                            $minVal = $project->getSettingVal($field['key'], 'min_value');
                            $maxVal = $project->getSettingVal($field['key'], 'max_value');
                            
                            // Check if this field should be disabled
                            $isDisabledField = in_array($field['key'], $disabledFields);
                            // For disabled fields, always set as required
                            if ($isDisabledField) {
                                $isRequired = 1;
                            }
                        @endphp

                        <div class="form-group row mb-3">
                            <label class="col-sm-3 col-form-label font-weight-bold text-end">
                                {{ $field['label'] }}
                            </label>
                            <div class="col-sm-9">
                                <select name="settings[{{ $field['key'] }}][required]" class="form-control mb-2" {{ $isDisabledField ? 'disabled' : '' }}>
                                    <option value="0" {{ $isRequired == 0 ? 'selected' : '' }}>Not Required</option>
                                    <option value="1" {{ $isRequired == 1 ? 'selected' : '' }}>Required</option>
                                </select>
                                
                                {{-- Hidden field to ensure disabled select value is submitted --}}
                                @if ($isDisabledField)
                                    <input type="hidden" name="settings[{{ $field['key'] }}][required]" value="1">
                                @endif

                                @if ($field['has_range'])
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="small text-muted">Minimum {{ $field['range_label'] }}</label>
                                            <input type="number" name="settings[{{ $field['key'] }}][min]"
                                                class="form-control" value="{{ $minVal }}" placeholder="Min Value">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="small text-muted">Maximum {{ $field['range_label'] }}</label>
                                            <input type="number" name="settings[{{ $field['key'] }}][max]"
                                                class="form-control" value="{{ $maxVal }}" placeholder="Max Value">
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach

                    <div class="form-group row mb-3">
                        <label class="col-sm-3 col-form-label font-weight-bold text-end">Accuracy</label>
                        <div class="col-sm-9">
                            <input type="number" name="accuracy" class="form-control"
                                value="{{ $project->accuracy ?? 10 }}">
                        </div>
                    </div>

                    <div class="form-group row mb-3">
                        <label class="col-sm-3 col-form-label font-weight-bold text-end">Add Second</label>
                        <div class="col-sm-9">
                            <input type="text" name="add_second" class="form-control" placeholder="">
                        </div>
                    </div>

                    <div class="text-end mt-4">
                        <button type="submit" class="btn px-4" style="background-color: #7cb342; color: #ffffff;">Submit</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
@endsection