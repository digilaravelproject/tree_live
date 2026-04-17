@extends('layouts.app')

@section('title')
    | Map Generator
@endsection

@section('content')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css" />

    <style>
        #map {
            height: 600px;
            width: 100%;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            border: 2px solid #fff;
            z-index: 1;
        }

        .controls-card {
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }

        .btn-action {
            height: 45px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Search Dropdown Style */
        .search-results {
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            background: white;
            border: 1px solid #ddd;
            border-radius: 0 0 8px 8px;
            z-index: 1000;
            max-height: 200px;
            overflow-y: auto;
            display: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .search-item {
            padding: 10px 15px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
        }

        .search-item:hover {
            background-color: #f8f9fa;
            color: #7cb342;
        }
    </style>

    <main>
        <div class="container-fluid py-4">

            <div class="row mb-4">
                <div class="col-12">
                    <div class="card controls-card">
                        <div class="row align-items-end">

                            <div class="col-md-5 mb-3 mb-md-0 position-relative">
                                <label class="form-label fw-bold text-uppercase text-secondary small">Search Location
                                    (India)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fa fa-search" style="color: #7cb342;"></i></span>
                                    <input type="text" id="address-search" class="form-control form-control-lg"
                                        placeholder="Enter city, area in India..." autocomplete="off">
                                    <button class="btn btn-outline-primary" type="button"
                                        id="btn-search-go" style="border-color: #7cb342; color: #7cb342;">Search</button>
                                </div>
                                <div id="search-results" class="search-results"></div>
                            </div>

                            <div class="col-md-2 mb-3 mb-md-0">
                                <label class="form-label fw-bold text-uppercase text-secondary small">Points Count</label>
                                <input type="number" id="point-count" class="form-control form-control-lg text-center"
                                    value="10" min="1" max="5000">
                            </div>

                            <div class="col-md-5">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <button id="btn-generate" class="btn w-100 btn-action" style="background-color: #7cb342; color: #ffffff;">
                                            <i class="fa fa-map-marker me-2"></i> Generate
                                        </button>
                                    </div>
                                    <div class="col-6">
                                        <button id="btn-download" class="btn w-100 btn-action" style="background-color: #558b2f; color: #ffffff;" disabled>
                                            <i class="fa fa-file-excel me-2"></i> Download
                                        </button>
                                    </div>
                                    <div class="col-12 mt-2">
                                        <button id="btn-reset" class="btn btn-secondary w-100 btn-sm">
                                            <i class="fa fa-refresh me-1"></i> Reset Map
                                        </button>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div id="map"></div>
                    <div class="mt-2 text-muted small text-center">
                        <i class="fa fa-info-circle" style="color: #7cb342;"></i>
                        <strong>Instructions:</strong> Search a location in India. A red triangle will appear.
                        Drag the white squares to <strong>Resize</strong> the shape. Click "Generate" to fill it.
                    </div>
                </div>
            </div>

        </div>
    </main>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>
    <script src="https://unpkg.com/@turf/turf/turf.min.js"></script>
    <script src="https://cdn.sheetjs.com/xlsx-0.19.3/package/dist/xlsx.full.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // --- 1. Initialize Map (Centered on India) ---
            var map = L.map('map').setView([20.5937, 78.9629], 5);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            var editableLayers = new L.FeatureGroup();
            map.addLayer(editableLayers);
            var generatedMarkers = new L.LayerGroup();
            map.addLayer(generatedMarkers);

            var generatedData = [];

            // --- 2. Search Logic (Restricted to India) ---
            const searchInput = document.getElementById('address-search');
            const searchResults = document.getElementById('search-results');

            // Function to fetch addresses
            async function searchAddress(query) {
                if (query.length < 3) return;
                // 'countrycodes=in' ensures results are ONLY from India
                const url =
                    `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&countrycodes=in&limit=5`;

                try {
                    const res = await fetch(url);
                    const data = await res.json();
                    showResults(data);
                } catch (e) {
                    console.error(e);
                }
            }

            // Show dropdown results
            function showResults(data) {
                searchResults.innerHTML = '';
                if (data.length === 0) {
                    searchResults.style.display = 'none';
                    return;
                }

                data.forEach(place => {
                    const div = document.createElement('div');
                    div.className = 'search-item';
                    div.innerHTML =
                        `<i class="fa fa-map-marker-alt text-muted me-2"></i> ${place.display_name}`;
                    div.onclick = () => selectLocation(place);
                    searchResults.appendChild(div);
                });
                searchResults.style.display = 'block';
            }

            // Handle location selection
            function selectLocation(place) {
                searchInput.value = place.display_name;
                searchResults.style.display = 'none';

                const lat = parseFloat(place.lat);
                const lon = parseFloat(place.lon);

                map.flyTo([lat, lon], 14);
                createTriangleArea(lat, lon);
            }

            // Input event listener with debounce
            let timeout = null;
            searchInput.addEventListener('input', function() {
                clearTimeout(timeout);
                timeout = setTimeout(() => searchAddress(this.value), 500);
            });

            // Hide dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (e.target !== searchInput && e.target !== searchResults) {
                    searchResults.style.display = 'none';
                }
            });

            // --- 3. Create Resizable Triangle ---
            function createTriangleArea(lat, lng) {
                editableLayers.clearLayers();
                generatedMarkers.clearLayers();
                document.getElementById('btn-download').disabled = true;

                // Create Polygon using Turf
                var center = turf.point([lng, lat]);
                var radius = 1; // 1km radius
                var options = {
                    steps: 3,
                    units: 'kilometers'
                };
                var triangle = turf.circle(center, radius, options);

                // Flip coords for Leaflet [Lat, Lng]
                var coords = triangle.geometry.coordinates[0].map(p => [p[1], p[0]]);
                coords.pop(); // Remove closing point

                var polygon = L.polygon(coords, {
                    color: 'red',
                    fillColor: '#f03',
                    fillOpacity: 0.2
                }).addTo(editableLayers);

                // Enable editing (This makes it resizable/draggable)
                polygon.editing.enable();
            }

            // --- 4. Generate Points Logic ---
            document.getElementById('btn-generate').addEventListener('click', function() {
                if (editableLayers.getLayers().length === 0) {
                    alert('Please search a location first.');
                    return;
                }

                var count = parseInt(document.getElementById('point-count').value);
                if (!count || count < 1) return;

                generatedMarkers.clearLayers();
                generatedData = [];

                // Get current shape (even after resizing)
                var layer = editableLayers.getLayers()[0];
                var geoJson = layer.toGeoJSON();
                var bbox = turf.bbox(geoJson);

                var pointsFound = 0;
                var attempts = 0;

                while (pointsFound < count && attempts < (count * 100)) {
                    var randomPoint = turf.randomPoint(1, {
                        bbox: bbox
                    });

                    if (turf.booleanPointInPolygon(randomPoint.features[0], geoJson)) {
                        var c = randomPoint.features[0].geometry.coordinates;
                        var lat = c[1];
                        var lng = c[0];

                        L.marker([lat, lng]).addTo(generatedMarkers);

                        generatedData.push({
                            'ID': pointsFound + 1,
                            'Latitude': lat.toFixed(6),
                            'Longitude': lng.toFixed(6),
                            'Address': searchInput.value
                        });
                        pointsFound++;
                    }
                    attempts++;
                }

                if (generatedData.length > 0) {
                    document.getElementById('btn-download').disabled = false;
                } else {
                    alert('Could not fit points. Make the triangle bigger.');
                }
            });

            // --- 5. Download Excel ---
            document.getElementById('btn-download').addEventListener('click', function() {
                if (generatedData.length === 0) return;
                var ws = XLSX.utils.json_to_sheet(generatedData);
                var wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, ws, "Coordinates");
                XLSX.writeFile(wb, "Map_Data_India.xlsx");
            });

            // Reset
            document.getElementById('btn-reset').addEventListener('click', function() {
                editableLayers.clearLayers();
                generatedMarkers.clearLayers();
                searchInput.value = '';
                document.getElementById('btn-download').disabled = true;
                map.setView([20.5937, 78.9629], 5);
            });
        });
    </script>
@endsection