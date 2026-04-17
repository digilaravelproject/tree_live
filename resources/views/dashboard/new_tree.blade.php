@extends('layouts.app')

@section('title')
    | {{ $page_title }}
@endsection

@section('content')
    <!-- Body main section starts -->
    <main>
        <div class="container-fluid">

            @push('scripts')
                <script>
                    $(document).ready(function() {
                        @if (session('success'))
                            toastr.success("{{ session('success') }}");
                        @endif

                        @if (session('error'))
                            toastr.error("{{ session('error') }}");
                        @endif

                        @if (session('warning'))
                            toastr.warning("{{ session('warning') }}");
                        @endif

                        @if (session('info'))
                            toastr.info("{{ session('info') }}");
                        @endif
                    });
                </script>
            @endpush

            <!-- Breadcrumb start -->
            <div class="row m-1">
                <div class="col-12">
                    <h4 class="main-title mb-3">{{ $page_title }}</h4>
                </div>
            </div>
            <!-- Breadcrumb end -->

            <!-- Tree Form start -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>Tree Information</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('trees.update', $tree->id) }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <div class="row">
                                    <!-- Ward/Plot No -->
                                    <div class="col-md-6 mb-3">
                                        <label for="ward_plot_no" class="form-label">Ward/Plot No</label>
                                        <input type="text"
                                            class="form-control @error('ward_plot_no') is-invalid @enderror"
                                            id="ward_plot_no" name="ward_plot_no"
                                            value="{{ old('ward_plot_no', $tree->ward_plot_no) }}">
                                        @error('ward_plot_no')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Tree No -->
                                    <div class="col-md-6 mb-3">
                                        <label for="tree_no" class="form-label">Tree No <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('tree_no') is-invalid @enderror"
                                            id="tree_no" name="tree_no" value="{{ old('tree_no', $tree->tree_no) }}"
                                            required>
                                        @error('tree_no')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Tree Name (Dropdown) -->
                                    <div class="col-md-6 mb-3">
                                        <label for="tree_name" class="form-label">Tree Name <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select @error('tree_name') is-invalid @enderror" id="tree_name"
                                            name="tree_name" required>
                                            <option value="">Select Tree</option>
                                            @foreach ($allTrees as $treeItem)
                                                <option value="{{ $treeItem->id }}"
                                                    {{ old('tree_name', $tree->tree_id) == $treeItem->id ? 'selected' : '' }}>
                                                    {{ $treeItem->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('tree_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Scientific Name (Dropdown) -->
                                    <div class="col-md-6 mb-3">
                                        <label for="scientific_name" class="form-label">Scientific Name</label>
                                        <select class="form-select @error('scientific_name') is-invalid @enderror"
                                            id="scientific_name" name="scientific_name">
                                            <option value="">Select Scientific Name</option>
                                            @foreach ($allScientific as $scientific)
                                                <option value="{{ $scientific->id }}"
                                                    {{ old('scientific_name', $tree->scientific_id) == $scientific->id ? 'selected' : '' }}>
                                                    {{ $scientific->scientific_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('scientific_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Family (Dropdown) -->
                                    <div class="col-md-6 mb-3">
                                        <label for="family" class="form-label">Family</label>
                                        <select class="form-select @error('family') is-invalid @enderror" id="family"
                                            name="family">
                                            <option value="">Select Family</option>
                                            @foreach ($allFamilies as $familyItem)
                                                <option value="{{ $familyItem->id }}"
                                                    {{ old('family', $tree->family_id) == $familyItem->id ? 'selected' : '' }}>
                                                    {{ $familyItem->family_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('family')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Girth -->
                                    <div class="col-md-6 mb-3">
                                        <label for="girth" class="form-label">Girth (cm)</label>
                                        <input type="number" step="0.01"
                                            class="form-control @error('girth') is-invalid @enderror" id="girth"
                                            name="girth" value="{{ old('girth', $tree->girth) }}">
                                        @error('girth')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Height -->
                                    <div class="col-md-6 mb-3">
                                        <label for="height" class="form-label">Height (m)</label>
                                        <input type="number" step="0.01"
                                            class="form-control @error('height') is-invalid @enderror" id="height"
                                            name="height" value="{{ old('height', $tree->height) }}">
                                        @error('height')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Canopy -->
                                    <div class="col-md-6 mb-3">
                                        <label for="canopy" class="form-label">Canopy (m)</label>
                                        <input type="number" step="0.01"
                                            class="form-control @error('canopy') is-invalid @enderror" id="canopy"
                                            name="canopy" value="{{ old('canopy', $tree->canopy) }}">
                                        @error('canopy')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Age -->
                                    <div class="col-md-6 mb-3">
                                        <label for="age" class="form-label">Age (years)</label>
                                        <input type="number" class="form-control @error('age') is-invalid @enderror"
                                            id="age" name="age" value="{{ old('age', $tree->age) }}">
                                        @error('age')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Condition -->
                                    <div class="col-md-6 mb-3">
                                        <label for="condition" class="form-label">Condition</label>
                                        <select class="form-select @error('condition') is-invalid @enderror"
                                            id="condition" name="condition">
                                            <option value="">Select Condition</option>
                                            <option value="Excellent"
                                                {{ old('condition', $tree->condition) == 'Excellent' ? 'selected' : '' }}>
                                                Excellent</option>
                                            <option value="Good"
                                                {{ old('condition', $tree->condition) == 'Good' ? 'selected' : '' }}>Good
                                            </option>
                                            <option value="Fair"
                                                {{ old('condition', $tree->condition) == 'Fair' ? 'selected' : '' }}>Fair
                                            </option>
                                            <option value="Poor"
                                                {{ old('condition', $tree->condition) == 'Poor' ? 'selected' : '' }}>Poor
                                            </option>
                                            <option value="Dead"
                                                {{ old('condition', $tree->condition) == 'Dead' ? 'selected' : '' }}>Dead
                                            </option>
                                        </select>
                                        @error('condition')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Address -->
                                    <div class="col-md-12 mb-3">
                                        <label for="address" class="form-label">Address</label>
                                        <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="2">{{ old('address', $tree->address) }}</textarea>
                                        @error('address')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Landmark -->
                                    <div class="col-md-6 mb-3">
                                        <label for="landmark" class="form-label">Landmark</label>
                                        <input type="text"
                                            class="form-control @error('landmark') is-invalid @enderror" id="landmark"
                                            name="landmark" value="{{ old('landmark', $tree->landmark) }}">
                                        @error('landmark')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Ownership -->
                                    <div class="col-md-6 mb-3">
                                        <label for="ownership" class="form-label">Ownership</label>
                                        <input type="text"
                                            class="form-control @error('ownership') is-invalid @enderror" id="ownership"
                                            name="ownership" value="{{ old('ownership', $tree->ownership) }}">
                                        @error('ownership')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Concern Person -->
                                    <div class="col-md-6 mb-3">
                                        <label for="concern_person" class="form-label">Concern Person</label>
                                        <input type="text"
                                            class="form-control @error('concern_person') is-invalid @enderror"
                                            id="concern_person" name="concern_person"
                                            value="{{ old('concern_person', $tree->concern_person) }}">
                                        @error('concern_person')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Latitude -->
                                    <div class="col-md-6 mb-3">
                                        <label for="latitude" class="form-label">Latitude</label>
                                        <input type="text"
                                            class="form-control @error('latitude') is-invalid @enderror" id="latitude"
                                            name="latitude" value="{{ old('latitude', $tree->latitude) }}">
                                        @error('latitude')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Longitude -->
                                    <div class="col-md-6 mb-3">
                                        <label for="longitude" class="form-label">Longitude</label>
                                        <input type="text"
                                            class="form-control @error('longitude') is-invalid @enderror" id="longitude"
                                            name="longitude" value="{{ old('longitude', $tree->longitude) }}">
                                        @error('longitude')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Remark -->
                                    <div class="col-md-12 mb-3">
                                        <label for="remark" class="form-label">Remark</label>
                                        <textarea class="form-control @error('remark') is-invalid @enderror" id="remark" name="remark" rows="3">{{ old('remark', $tree->remark) }}</textarea>
                                        @error('remark')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Existing Images -->
                                    @if (!empty($tree->all_captured_images))
                                        <div class="col-md-12 mb-3">
                                            <label class="form-label">Existing Images</label>
                                            <div class="row g-2">
                                                @foreach ($tree->all_captured_images as $index => $image)
                                                    <div class="col-md-3">
                                                        <div class="position-relative">
                                                            <img src="{{ asset($image) }}"
                                                                alt="Tree Image {{ $index + 1 }}"
                                                                class="img-fluid rounded"
                                                                style="height: 200px; object-fit: cover; width: 100%;">
                                                            <button type="button"
                                                                class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2"
                                                                onclick="removeImage('{{ $image }}', {{ $index }})">
                                                                <i class="ti ti-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Upload New Images -->
                                    <div class="col-md-12 mb-3">
                                        <label for="tree_images" class="form-label">Upload New Images</label>
                                        <input type="file"
                                            class="form-control @error('tree_images') is-invalid @enderror"
                                            id="tree_images" name="tree_images[]" multiple accept="image/*">
                                        @error('tree_images')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">You can select multiple images</small>
                                    </div>

                                </div>

                                <!-- Hidden field for images to delete -->
                                <input type="hidden" name="images_to_delete" id="images_to_delete" value="">

                                <!-- Buttons -->
                                <div class="row">
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ti ti-device-floppy"></i> Update Tree
                                        </button>
                                        <a href="{{ route('tree.list') }}" class="btn btn-secondary">
                                            <i class="ti ti-arrow-left"></i> Back to List
                                        </a>
                                    </div>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Tree Form end -->

        </div>
    </main>
    <!-- Body main section ends -->

    @push('scripts')
        <script>
            let imagesToDelete = [];

            function removeImage(imagePath, index) {
                if (confirm('Are you sure you want to remove this image?')) {
                    imagesToDelete.push(imagePath);
                    document.getElementById('images_to_delete').value = JSON.stringify(imagesToDelete);

                    // Hide the image container
                    event.target.closest('.col-md-3').style.display = 'none';
                }
            }
        </script>
    @endpush
@endsection
