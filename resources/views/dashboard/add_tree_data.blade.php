@extends('layouts.app')

@section('title')
    | {{ $page_title }}
@endsection

@section('content')
    <style>
        .location-input-wrap {
            position: relative;
        }

        .use-location-btn {
            position: absolute;
            right: 10px;
            top: 8px;
            border: none;
            background: #fff;
            height: 30px;
            width: 30px;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #7cb342;
            font-size: 18px;
            z-index: 10;
        }

        .use-location-btn:hover {
            background-color: #f8f9fa;
            color: #558b2f;
        }

        .use-location-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .location-feedback {
            margin-top: 5px;
            font-size: 13px;
            font-weight: 500;
        }

        /* Removed readonly background-color restriction to allow manual fill visibility */
        .form-control[readonly] {
            background-color: #e9ecef;
            cursor: not-allowed;
        }

        .auto-filled-select {
            background-color: #f8f9fa;
        }

        .use-location-spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(0, 0, 0, 0.2);
            border-top-color: #7cb342;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Style for dynamically added asterisk */
        .required-star {
            color: #dc3545;
            margin-left: 2px;
        }
    </style>

    <main>
        <div class="container-fluid">

            <div class="row m-1">
                <div class="col-12">
                    <h4 class="main-title mb-3">{{ $page_title }}</h4>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>Tree Information</h5>
                        </div>
                        <div class="card-body">

                            <form action="{{ route('trees.store') }}" method="POST" enctype="multipart/form-data"
                                id="treeForm">
                                @csrf

                                <div class="row">

                                    <div class="col-md-12 mb-3">
                                        <label for="project_id" class="form-label">Select Project <span
                                                class="required-star">*</span></label>
                                        <select class="form-select @error('project_id') is-invalid @enderror"
                                            id="project_id" name="project_id" required>
                                            <option value="">-- Select Project --</option>
                                            @if (isset($allProjects))
                                                @foreach ($allProjects as $project)
                                                    <option value="{{ $project->id }}"
                                                        {{ old('project_id') == $project->id ? 'selected' : '' }}>
                                                        {{ $project->project_name }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                        @error('project_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="ward_plot_no" class="form-label">Ward/Plot No</label>
                                        <input type="text" class="form-control" id="ward_plot_no" name="ward_plot_no"
                                            value="{{ old('ward_plot_no') }}">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="tree_no" class="form-label">Tree No</label>
                                        <input type="text" class="form-control" id="tree_no" name="tree_no"
                                            value="{{ old('tree_no') }}">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="tree_name" class="form-label">Tree Name</label>
                                        <select class="form-select @error('tree_name') is-invalid @enderror" id="tree_name"
                                            name="tree_name">
                                            <option value="">Select Tree</option>
                                            @foreach ($allTrees as $treeItem)
                                                <option value="{{ $treeItem->id }}"
                                                    data-scientific="{{ $treeItem->related_scientific_id }}"
                                                    data-family="{{ $treeItem->related_family_id }}"
                                                    {{ old('tree_name') == $treeItem->id ? 'selected' : '' }}>
                                                    {{ $treeItem->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('tree_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="scientific_name" class="form-label">Scientific Name</label>
                                        <select class="form-select auto-filled-select" id="scientific_name"
                                            name="scientific_name" style="pointer-events: none;" tabindex="-1">
                                            <option value="">(Auto Filled)</option>
                                            @foreach ($allScientific as $scientific)
                                                <option value="{{ $scientific->id }}"
                                                    {{ old('scientific_name') == $scientific->id ? 'selected' : '' }}>
                                                    {{ $scientific->scientific_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="family" class="form-label">Family</label>
                                        <select class="form-select auto-filled-select" id="family" name="family"
                                            style="pointer-events: none;" tabindex="-1">
                                            <option value="">(Auto Filled)</option>
                                            @foreach ($allFamilies as $familyItem)
                                                <option value="{{ $familyItem->id }}"
                                                    {{ old('family') == $familyItem->id ? 'selected' : '' }}>
                                                    {{ $familyItem->family_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="girth" class="form-label">Girth (cm)</label>
                                        <input type="number" step="0.01" class="form-control" id="girth"
                                            name="girth" value="{{ old('girth') }}">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="height" class="form-label">Height (m)</label>
                                        <input type="number" step="0.01" class="form-control" id="height"
                                            name="height" value="{{ old('height') }}">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="canopy" class="form-label">Canopy (m)</label>
                                        <input type="number" step="0.01" class="form-control" id="canopy"
                                            name="canopy" value="{{ old('canopy') }}">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="age" class="form-label">Age (years)</label>
                                        <input type="number" class="form-control" id="age" name="age"
                                            value="{{ old('age') }}">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="condition" class="form-label">Condition</label>
                                        <select class="form-select" id="condition" name="condition">
                                            <option value="">Select Condition</option>
                                            <option value="Excellent">Excellent</option>
                                            <option value="Good">Good</option>
                                            <option value="Fair">Fair</option>
                                            <option value="Poor">Poor</option>
                                            <option value="Dead">Dead</option>
                                        </select>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label for="address" class="form-label">Address</label>
                                        <div class="location-input-wrap">
                                            <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="2"
                                                placeholder="Enter address manually or use the location icon..." style="padding-right: 45px;">{{ old('address') }}</textarea>

                                            <button type="button" id="use-location-btn" class="use-location-btn"
                                                title="Get Current Location">
                                                <i id="use-location-icon" class="ti ti-map-pin"></i>
                                            </button>
                                        </div>
                                        <div id="location-feedback" class="location-feedback text-muted"></div>
                                        @error('address')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="landmark" class="form-label">Landmark</label>
                                        <input type="text" class="form-control" id="landmark" name="landmark"
                                            value="{{ old('landmark') }}">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="ownership" class="form-label">Ownership</label>
                                        <input type="text" class="form-control" id="ownership" name="ownership"
                                            value="{{ old('ownership') }}">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="concern_person" class="form-label">Concern Person</label>
                                        <input type="text" class="form-control" id="concern_person"
                                            name="concern_person" value="{{ old('concern_person') }}">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="latitude" class="form-label">Latitude</label>
                                        <input type="text" class="form-control" id="latitude" name="latitude"
                                            value="{{ old('latitude') }}">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="longitude" class="form-label">Longitude</label>
                                        <input type="text" class="form-control" id="longitude" name="longitude"
                                            value="{{ old('longitude') }}">
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label for="remark" class="form-label">Remark</label>
                                        <textarea class="form-control" id="remark" name="remark" rows="3">{{ old('remark') }}</textarea>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label for="tree_images" class="form-label">Upload Images</label>
                                        <input type="file" class="form-control" id="tree_images" name="tree_images[]"
                                            multiple accept="image/*">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <button type="submit" class="btn" style="background-color: #7cb342; color: #ffffff;"><i
                                                class="ti ti-device-floppy"></i> Save Tree</button>
                                        <a href="#" class="btn btn-secondary"><i class="ti ti-arrow-left"></i>
                                            Back</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Notifications
            @if (session('success'))
                toastr.success("{{ session('success') }}");
            @endif
            @if (session('error'))
                toastr.error("{{ session('error') }}");
            @endif


            var projectSettings = @json($projectSettings);
            var fieldMap = {
                'ward_plot_no': 'ward_plot_no',
                'ward_number': 'ward_plot_no',
                'tree_name': 'tree_name',
                'girth': 'girth',
                'height': 'height',
                'age': 'age',
                'canopy': 'canopy',
                'condition': 'condition',
                'address': 'address',
                'landmark': 'landmark',
                'concern_person': 'concern_person',
                'ownership': 'ownership',
                'remark': 'remark',
                'ratio': 'ratio',
                'all_captured_images': 'tree_images'
            };
            $('#project_id').on('change', function() {
                var projectId = $(this).val();
                resetFormRequirements();

                if (projectId && projectSettings[projectId]) {
                    var settings = projectSettings[projectId];

                    console.log("Applying settings for Project ID: " + projectId, settings);

                    // 2. Loop through settings and apply required
                    settings.forEach(function(setting) {
                        var dbKey = setting.field_key;
                        var isRequired = setting.is_required == 1;

                        // Map DB key to HTML ID
                        var inputId = fieldMap[dbKey] || dbKey;
                        var $input = $('#' + inputId);

                        if ($input.length > 0 && isRequired) {
                            // Required attribute lagayein
                            $input.prop('required', true);

                            // Label dhund kar Star lagayein
                            var $label = $('label[for="' + inputId + '"]');
                            if ($label.length > 0 && $label.find('.required-star').length === 0) {
                                $label.append('<span class="required-star">*</span>');
                            }
                        }
                    });
                }
            });

            function resetFormRequirements() {
                // Form ke saare inputs se required hatayein (except project_id jo hamesha required hai)
                $('#treeForm').find('input, select, textarea').not('#project_id').prop('required', false);

                // Saare labels se required star hatayein (except project_id)
                $('#treeForm').find('label').not('label[for="project_id"]').find('.required-star').remove();
            }


            // --- 2. AUTO-FILL SCIENTIFIC & FAMILY NAME ---
            $('#tree_name').on('change', function() {
                var $selected = $(this).find(':selected');
                var sciId = $selected.data('scientific');
                var famId = $selected.data('family');

                if (sciId) $('#scientific_name').val(sciId).trigger('change');
                else $('#scientific_name').val('').trigger('change');

                if (famId) $('#family').val(famId).trigger('change');
                else $('#family').val('').trigger('change');
            });

            // --- 3. LOCATION AUTO-DETECT ---
            $(document).on('click', '#use-location-btn', function(e) {
                e.preventDefault();
                const $btn = $(this);
                const $icon = $('#use-location-icon');
                const $feedback = $('#location-feedback');

                if (!navigator.geolocation) {
                    $feedback.removeClass('text-success').addClass('text-danger').text(
                        'Browser not supported.');
                    return;
                }

                $btn.prop('disabled', true);
                $icon.removeClass('ti ti-map-pin').html('<span class="use-location-spinner"></span>');
                $feedback.removeClass('text-danger text-success').text('Detecting location...');

                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        $('#latitude').val(lat);
                        $('#longitude').val(lng);

                        $.ajax({
                            url: "{{ route('location.auto-detect') }}",
                            type: "POST",
                            data: {
                                latitude: lat,
                                longitude: lng,
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(res) {
                                if (res.success) {
                                    $('#address').val(res.full_address || res.display_name);
                                    $feedback.removeClass('text-danger').addClass(
                                        'text-success').text('Location found!');
                                } else {
                                    $feedback.removeClass('text-success').addClass(
                                        'text-danger').text('Address not found.');
                                }
                            },
                            error: function() {
                                $feedback.removeClass('text-success').addClass(
                                    'text-danger').text('Server error.');
                            },
                            complete: function() {
                                $btn.prop('disabled', false);
                                $icon.html('').addClass('ti ti-map-pin');
                            }
                        });
                    },
                    function(error) {
                        $feedback.removeClass('text-success').addClass('text-danger').text(
                            'Location access denied.');
                        $btn.prop('disabled', false);
                        $icon.html('').addClass('ti ti-map-pin');
                    }, {
                        enableHighAccuracy: true,
                        timeout: 10000
                    }
                );
            });
        });
    </script>
@endsection