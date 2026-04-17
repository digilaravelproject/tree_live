@extends('layouts.app')
@section('title')
    | {{ $page_title }}
@endsection

@section('content')
    <main>
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-7 col-xxl-6">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="card eshop-cards">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="h-40 w-40 d-flex-center b-r-15 f-s-18" style="background-color: #7cb342;">
                                            <i class="ph-bold ph-map-pin-line" style="color: #ffffff;"></i>
                                        </span>
                                        <div class="dropdown">
                                            <a href="#" style="color: #7cb342;">Last 6 Months</a>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <div class="flex-shrink-0 align-self-end">
                                            <p class="f-s-16 mb-0">Total Projects</p>
                                            <h5>{{ $projectCount }}</h5>
                                        </div>
                                        <div style="width: 120px; height: 70px;">
                                            <canvas id="visitsChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="card eshop-cards">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="h-40 w-40 d-flex-center b-r-15 f-s-18" style="background-color: #558b2f;">
                                            <i class="ph-bold ph-tree-structure" style="color: #ffffff;"></i>
                                        </span>
                                        <div class="dropdown">
                                            <a href="#" style="color: #558b2f;">Last 6 Months</a>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <div class="flex-shrink-0 align-self-end">
                                            <p class="f-s-16 mb-0">Total Trees</p>
                                            <h5>{{ $treeCount }}</h5>
                                        </div>
                                        <div style="width: 120px; height: 70px;">
                                            <canvas id="orderChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php /*<div class="col-sm-6">
                            <div class="card eshop-cards">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="h-40 w-40 d-flex-center b-r-15 f-s-18" style="background-color: #9ccc65;">
                                            <i class="ph-bold ph-buildings" style="color: #ffffff;"></i>
                                        </span>
                                        <div class="dropdown">
                                            <a href="#" style="color: #9ccc65;">Last 6 Months</a>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <div class="flex-shrink-0 align-self-end">
                                            <p class="f-s-16 mb-0">Districts Covered</p>
                                            <h5>{{ $districtCount }}</h5>
                                        </div>
                                        <div style="width: 120px; height: 70px;">
                                            <canvas id="activityChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> */?>

                        <div class="col-sm-6">
                            <div class="card eshop-cards">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="h-40 w-40 d-flex-center b-r-15 f-s-18" style="background-color: #8d6e63;">
                                            <i class="ph-fill ph-coins" style="color: #ffffff;"></i>
                                        </span>
                                        <div class="dropdown">
                                            <a href="#" style="color: #8d6e63;">Monthly</a>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <div class="flex-shrink-0 align-self-end">
                                            <p class="f-s-16 mb-0">Total Revenue</p>
                                            <h5>$63,987</h5>
                                        </div>
                                        <div style="width: 120px; height: 70px;">
                                            <canvas id="salesChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-5 col-xxl-4">
                    <div class="card active-user-card">
                        <div class="card-body">
                            <div>
                                <h5 class="text-dark">Active Users</h5>
                            </div>
                            <div class="d-flex gap-2 mt-3">
                                <div class="active-user-content">
                                    <h2 class="mb-0" style="color: #7cb342;">50k</h2>
                                    <p class="text-secondary text-nowrap mb-0">Page Views</p>
                                    <div class="app-divider-v dashed py-3"></div>
                                    <p class="f-w-500">Today Users</p>
                                    <div>
                                        <ul class="avatar-group justify-content-start">
                                            <li
                                                class="h-35 w-35 d-flex-center b-r-50 overflow-hidden b-2-light" style="background-color: #7cb342;">
                                                <img src="{{ asset('assets/images/avtar/4.png') }}" alt=""
                                                    class="img-fluid">
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="card flex-grow-1 user-chart-card" style="background-color: #7cb342;">
                                    <div class="card-body">
                                        <div class="active-users-chart" style="height: 200px; position: relative;">
                                            <canvas id="userChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <h5 class="mb-3 f-w-600" style="color: #558b2f;">Analytics Overview</h5>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header border-bottom">
                            <h6 class="m-0">Projects Growth</h6>
                        </div>
                        <div class="card-body">
                            <div style="height: 250px; position: relative;">
                                <canvas id="projectAnalysisChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header border-bottom">
                            <h6 class="m-0">Tree Plantation</h6>
                        </div>
                        <div class="card-body">
                            <div style="height: 250px; position: relative;">
                                <canvas id="treeAnalysisChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <?php /*<div class="col-md-4">
                    <div class="card">
                        <div class="card-header border-bottom">
                            <h6 class="m-0">District Coverage</h6>
                        </div>
                        <div class="card-body">
                            <div style="height: 250px; position: relative;">
                                <canvas id="districtAnalysisChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div> */?>
            </div>
            <div class="row">
                <div class="col-md-6 col-lg-2 d-none d-xxl-block">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <h6 class="header-title-text mb-0">Product Sold</h6>
                                <span><i class="ph-bold ph-trend-down text-danger"></i></span>
                            </div>
                            <div style="height: 100px; position: relative;">
                                <canvas id="productSold"></canvas>
                            </div>
                            <div>
                                <a href="#" class="btn w-100 mt-2" style="background-color: #7cb342; color: #ffffff;">Details</a>
                            </div>
                        </div>
                    </div>
                </div>



            </div>

        </div>
    </main>
@endsection

@section('scripts')
    {{-- Chart.js Library --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {

            // Shared Data
            var monthLabels = @json($months);

            // Common Config for "Small Sparkline" Charts (Inside Cards)
            const sparklineOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: true
                    }
                },
                scales: {
                    x: {
                        display: false
                    },
                    y: {
                        display: false
                    }
                },
                elements: {
                    point: {
                        radius: 0
                    },
                    line: {
                        tension: 0.4,
                        borderWidth: 2
                    }
                }
            };

            // Common Config for "Big Analysis" Charts (New Section)
            const analysisChartOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        border: {
                            dash: [4, 4]
                        }
                    }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                }
            };

            // ==========================================
            // 1. SMALL SPARKLINE CHARTS (Top Cards)
            // ==========================================

            if (document.getElementById("visitsChart")) {
                new Chart(document.getElementById("visitsChart"), {
                    type: 'line',
                    data: {
                        labels: monthLabels,
                        datasets: [{
                            data: @json($projectData),
                            borderColor: '#7cb342',
                            backgroundColor: 'rgba(124, 179, 66, 0.1)',
                            fill: true
                        }]
                    },
                    options: sparklineOptions
                });
            }

            if (document.getElementById("orderChart")) {
                new Chart(document.getElementById("orderChart"), {
                    type: 'line',
                    data: {
                        labels: monthLabels,
                        datasets: [{
                            data: @json($treeData),
                            borderColor: '#558b2f',
                            backgroundColor: 'rgba(85, 139, 47, 0.1)',
                            fill: true
                        }]
                    },
                    options: sparklineOptions
                });
            }

            if (document.getElementById("activityChart")) {
                new Chart(document.getElementById("activityChart"), {
                    type: 'line',
                    data: {
                        labels: monthLabels,
                        datasets: [{
                            data: @json($districtData),
                            borderColor: '#9ccc65',
                            backgroundColor: 'rgba(156, 204, 101, 0.1)',
                            fill: true
                        }]
                    },
                    options: sparklineOptions
                });
            }

            if (document.getElementById("salesChart")) {
                new Chart(document.getElementById("salesChart"), {
                    type: 'line',
                    data: {
                        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                        datasets: [{
                            data: [10, 20, 15, 30, 25, 40, 35],
                            borderColor: '#8d6e63',
                            backgroundColor: 'rgba(141, 110, 99, 0.1)',
                            fill: true
                        }]
                    },
                    options: sparklineOptions
                });
            }

            // ==========================================
            // 2. NEW SECTION CHARTS (Big Analysis Charts)
            // ==========================================

            // Chart 1: Project Growth (Bar Chart)
            if (document.getElementById("projectAnalysisChart")) {
                new Chart(document.getElementById("projectAnalysisChart"), {
                    type: 'bar',
                    data: {
                        labels: monthLabels,
                        datasets: [{
                            label: 'New Projects',
                            data: @json($projectData),
                            backgroundColor: '#7cb342',
                            borderRadius: 4,
                            barPercentage: 0.6
                        }]
                    },
                    options: analysisChartOptions
                });
            }

            // Chart 2: Tree Plantation (Line Area Chart)
            if (document.getElementById("treeAnalysisChart")) {
                new Chart(document.getElementById("treeAnalysisChart"), {
                    type: 'line',
                    data: {
                        labels: monthLabels,
                        datasets: [{
                            label: 'Trees Planted',
                            data: @json($treeData),
                            borderColor: '#558b2f',
                            backgroundColor: 'rgba(85, 139, 47, 0.2)',
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: analysisChartOptions
                });
            }

            // Chart 3: District Coverage (Line Chart)
            if (document.getElementById("districtAnalysisChart")) {
                new Chart(document.getElementById("districtAnalysisChart"), {
                    type: 'line',
                    data: {
                        labels: monthLabels,
                        datasets: [{
                            label: 'Districts Added',
                            data: @json($districtData),
                            borderColor: '#9ccc65',
                            backgroundColor: 'transparent',
                            borderWidth: 2,
                            tension: 0.4,
                            pointBackgroundColor: '#9ccc65'
                        }]
                    },
                    options: analysisChartOptions
                });
            }

            // ==========================================
            // 3. OTHER EXISTING CHARTS
            // ==========================================

            if (document.getElementById("userChart")) {
                new Chart(document.getElementById("userChart"), {
                    type: 'bar',
                    data: {
                        labels: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
                        datasets: [{
                            data: [44, 55, 41, 67, 22, 43, 21, 33, 45, 31, 87, 65],
                            backgroundColor: '#ffffff',
                            borderRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            x: {
                                display: false
                            },
                            y: {
                                display: false
                            }
                        }
                    }
                });
            }

            if (document.getElementById("overviewChart")) {
                new Chart(document.getElementById("overviewChart"), {
                    type: 'line',
                    data: {
                        labels: ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"],
                        datasets: [{
                                label: 'Profit',
                                data: [31, 40, 28, 51, 42, 109, 100],
                                borderColor: '#7cb342',
                                backgroundColor: 'rgba(124, 179, 66, 0.05)',
                                fill: true,
                                tension: 0.4
                            },
                            {
                                label: 'Expense',
                                data: [11, 32, 45, 32, 34, 52, 41],
                                borderColor: '#9ccc65',
                                backgroundColor: 'rgba(156, 204, 101, 0.05)',
                                fill: true,
                                tension: 0.4
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                                align: 'end'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    display: false
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            }

            if (document.getElementById("productSold")) {
                new Chart(document.getElementById("productSold"), {
                    type: 'line',
                    data: {
                        labels: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                        datasets: [{
                            data: [25, 66, 41, 89, 63, 25, 44, 12, 36, 9],
                            borderColor: '#8d6e63',
                            borderWidth: 2,
                            pointRadius: 0,
                            tension: 0.4
                        }]
                    },
                    options: sparklineOptions
                });
            }

        });
    </script>
@endsection