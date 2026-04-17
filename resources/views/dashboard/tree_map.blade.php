@extends('layouts.app')

@section('title')
    | {{ $page_title }}
@endsection

@section('content')
    <style>
        .filter-label {
            font-weight: 600;
            font-size: 13px;
            margin-bottom: 5px;
            color: #333;
        }

        .form-control,
        .form-select {
            border-radius: 4px;
            border: 1px solid #ddd;
            font-size: 14px;
        }

        /* Custom Info Window Style */
        .gm-style-iw {
            padding: 0 !important;
            border-radius: 8px !important;
            overflow: hidden !important;
        }

        .tree-info-window {
            width: 350px;
            font-family: 'Roboto', sans-serif;
        }

        .tree-img-container {
            width: 100%;
            height: 150px;
            background-color: #f0f0f0;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .tree-img-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .tree-details {
            padding: 15px;
            max-height: 300px;
            overflow-y: auto;
        }

        .tree-row {
            display: flex;
            border-bottom: 1px solid #eee;
            padding: 6px 0;
            font-size: 13px;
        }

        .tree-row:last-child {
            border-bottom: none;
        }

        .tree-label {
            width: 40%;
            font-weight: bold;
            color: #555;
        }

        .tree-val {
            width: 60%;
            color: #333;
        }
    </style>

    <main>
        <div class="container-fluid">
            <div class="row m-1">
                <div class="col-12">
                    <h4 class="main-title mb-3">🌳 Tree Location Map</h4>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form id="filterForm">
                                <div class="row">
                                    <div class="col-md-3 mb-2">
                                        <label class="filter-label">Select Project</label>
                                        <select class="form-select" name="project_id" id="project_id">
                                            <option value="">Select Project</option>
                                            @foreach ($projects as $proj)
                                                <option value="{{ $proj->id }}">{{ $proj->project_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    {{-- Start/End date filters removed per request --}}
                                    <div class="col-md-3 mb-2">
                                        <label class="filter-label">Ward Number</label>
                                        <select class="form-select" name="ward_plot_no" id="ward_plot_no">
                                            <option value="">Select Ward</option>
                                            @foreach ($wards as $ward)
                                                <option value="{{ $ward }}">{{ $ward }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="filter-label">Tree Number</label>
                                        <input type="text" class="form-control" name="tree_no" id="tree_no"
                                            placeholder="Enter Tree No">
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="filter-label">Ownership</label>
                                        <select class="form-select" name="ownership" id="ownership">
                                            <option value="">Select Ownership</option>
                                            @foreach ($ownerships as $own)
                                                <option value="{{ $own }}">{{ $own }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-2 d-flex align-items-end">
                                        <button type="button" class="btn w-100" style="background-color: #7cb342; color: #ffffff;" id="btn-get-data"
                                            onclick="loadMapData()">
                                            <i class="fa fa-filter"></i> Get Data
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-12 text-end">
                    <span class="badge fs-6" style="background-color: #7cb342;">Total Trees Found: <span id="tree-count">0</span></span>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body p-0">
                            <div id="map" style="width: 100%; height: 600px; border-radius: 4px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&callback=initMap" async defer>
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        let map;
        let markers = [];
        let infoWindow;

        $(document).ready(function() {
            // Document ready - no date filters used
        });

        function initMap() {
            const defaultCenter = {
                lat: 20.5937,
                lng: 78.9629
            }; // India
            map = new google.maps.Map(document.getElementById('map'), {
                zoom: 5,
                center: defaultCenter,
                mapTypeId: 'roadmap',
                mapTypeControl: true,
                streetViewControl: false
            });
            infoWindow = new google.maps.InfoWindow();
        }

        function loadMapData() {
            // Get current map bounds - always available after map init
            let bounds = map.getBounds();
            
            // If bounds don't exist (shouldn't happen), use map center with a default radius
            if (!bounds) {
                const center = map.getCenter();
                bounds = new google.maps.LatLngBounds(
                    new google.maps.LatLng(center.lat() - 5, center.lng() - 5),
                    new google.maps.LatLng(center.lat() + 5, center.lng() + 5)
                );
            }

            const ne = bounds.getNorthEast();
            const sw = bounds.getSouthWest();

            const formData = {
                project_id: $('#project_id').val(),
                ward_plot_no: $('#ward_plot_no').val(),
                tree_no: $('#tree_no').val(),
                girth: $('#girth').val(),
                ownership: $('#ownership').val(),
                // Map bounds (always send these)
                north_lat: ne.lat(),
                south_lat: sw.lat(),
                east_lng: ne.lng(),
                west_lng: sw.lng()
            };

            $('#btn-get-data').html('<i class="fa fa-spinner fa-spin"></i> Loading...').prop('disabled', true);

            $.ajax({
                url: "{{ route('tree.map') }}",
                type: "GET",
                data: formData,
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        $('#tree-count').text(response.count);
                        updateMapMarkers(response.trees);
                    } else {
                        alert('Error fetching data');
                    }
                },
                error: function(xhr) {
                    console.error(xhr);
                    alert('Error fetching tree data.');
                },
                complete: function() {
                    $('#btn-get-data').html('<i class="fa fa-filter"></i> Get Data').prop('disabled', false);
                }
            });
        }

        function updateMapMarkers(trees) {
            clearMarkers();
            if (trees.length === 0) {
                alert("No trees found for the selected filters in this zoomed area.");
                return;
            }

            trees.forEach(tree => {
                const lat = parseFloat(tree.latitude);
                const lng = parseFloat(tree.longitude);

                if (!isNaN(lat) && !isNaN(lng)) {
                    const position = {
                        lat: lat,
                        lng: lng
                    };
                    const marker = new google.maps.Marker({
                        position: position,
                        map: map,
                        animation: google.maps.Animation.DROP
                    });

                    marker.addListener('click', () => {
                        showTreeDetails(tree, marker);
                    });

                    markers.push(marker);
                }
            });
            
            // Do NOT call fitBounds - keep the current map view
        }

        function clearMarkers() {
            markers.forEach(m => m.setMap(null));
            markers = [];
        }

        function showTreeDetails(tree, marker) {
            const content = generateInfoWindowContent(tree);
            infoWindow.setContent(content);
            infoWindow.open(map, marker);
        }

        function generateInfoWindowContent(tree) {
            let imageUrl = '';
            try {
                if (tree.all_captured_images) {
                    const images = JSON.parse(tree.all_captured_images);
                    if (images.length > 0) {
                        imageUrl = "{{ asset('') }}" + images[0];
                    }
                }
            } catch (e) {
                console.error("Image error", e);
            }

            const imgHtml = imageUrl ?
                `<img src="${imageUrl}" alt="Tree Image">` :
                `<span class="text-muted" style="color:#aaa;">No Image</span>`;

            const createdDate = new Date(tree.created_at).toLocaleDateString();

            const projectName = tree.project ? tree.project.project_name : '-';
            const treeName = tree.tree ? tree.tree.name : (tree.tree_id || '-');
            const scientificName = tree.scientific ? tree.scientific.scientific_name : '-';
            const familyName = tree.family ? tree.family.family_name : '-';

            return `
                <div class="tree-info-window">
                    <div class="tree-img-container">${imgHtml}</div>
                    <div class="tree-details">
                        <div class="tree-row">
                            <div class="tree-label">Tree Number</div>
                            <div class="tree-val">${tree.tree_no || '-'}</div>
                        </div>
                        <div class="tree-row">
                            <div class="tree-label">Tree Name</div>
                            <div class="tree-val"><b>${treeName}</b></div>
                        </div>
                        <div class="tree-row">
                            <div class="tree-label">Scientific Name</div>
                            <div class="tree-val"><i>${scientificName}</i></div>
                        </div>
                        <div class="tree-row">
                            <div class="tree-label">Family</div>
                            <div class="tree-val">${familyName}</div>
                        </div>
                        <div class="tree-row">
                            <div class="tree-label">Project</div>
                            <div class="tree-val">${projectName}</div>
                        </div>
                        <div class="tree-row">
                            <div class="tree-label">Address</div>
                            <div class="tree-val">${tree.address || '-'}</div>
                        </div>
                        <div class="tree-row">
                            <div class="tree-label">Girth</div>
                            <div class="tree-val">${tree.girth ? tree.girth + ' cm' : '-'}</div>
                        </div>
                        <div class="tree-row">
                            <div class="tree-label">Height</div>
                            <div class="tree-val">${tree.height ? tree.height + ' m' : '-'}</div>
                        </div>
                        <div class="tree-row">
                            <div class="tree-label">Canopy</div>
                            <div class="tree-val">${tree.canopy ? tree.canopy + ' m' : '-'}</div>
                        </div>
                        <div class="tree-row">
                            <div class="tree-label">Age</div>
                            <div class="tree-val">${tree.age ? tree.age + ' Years' : '-'}</div>
                        </div>
                        <div class="tree-row">
                            <div class="tree-label">Condition</div>
                            <div class="tree-val">${tree.condition || '-'}</div>
                        </div>
                        <div class="tree-row">
                            <div class="tree-label">Ownership</div>
                            <div class="tree-val">${tree.ownership || '-'}</div>
                        </div>
                        <div class="tree-row">
                            <div class="tree-label">Landmark</div>
                            <div class="tree-val">${tree.landmark || '-'}</div>
                        </div>
                        <div class="tree-row">
                            <div class="tree-label">Date</div>
                            <div class="tree-val">${createdDate}</div>
                        </div>
                        <div class="tree-row" style="background:#f9f9f9; border:none; margin-top:5px; font-size:11px;">
                            <div class="tree-val" style="width:100%; color:#999;">Lat: ${tree.latitude}, Lng: ${tree.longitude}</div>
                        </div>
                    </div>
                </div>
            `;
        }
    </script>
@endsection