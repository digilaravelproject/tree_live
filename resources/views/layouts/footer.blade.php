<div class="go-top">
            <span class="progress-value">
                <i class="ti ti-arrow-up"></i>
            </span>
        </div>

        <footer>
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-9 col-12">
                        <ul class="footer-text">
                            <li>
                                <p class="mb-0">Copyright © {{ date('Y') }} Tree Expert. All rights reserved 💖</p>
                            </li>
                            <li> <a href="#"> V1.0.0 </a></li>
                        </ul>
                    </div>
                    <div class="col-md-3">
                        <ul class="footer-text text-end">
                            <li> <a href="#"> Need Help <i class="ti ti-help"></i></a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </footer>
    
    </div> <!-- Ends .app-content (Opened in top_header.blade.php) -->
    </div> <!-- Ends .app-wrapper (Opened in sidebar.blade.php) -->

    <div id="customizer"></div>

    <!-- Core Scripts -->
    <script src="{{ asset('assets/js/jquery-3.6.3.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/simplebar/simplebar.js') }}"></script>
    
    <!-- Plugins -->
    <script src="{{ asset('assets/vendor/datatable/jquery-3.5.1.js') }}"></script>
    <script src="{{ asset('assets/vendor/phosphor/phosphor.js') }}"></script>
    <script src="{{ asset('assets/vendor/vector-map/jquery-jvectormap-2.0.5.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/vector-map/jquery-jvectormap-world-mill.js') }}"></script>
    <script src="{{ asset('assets/vendor/slick/slick.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/cleavejs/cleave.min.js') }}"></script>
    <script src="{{ asset('assets/js/data_table.js') }}"></script>
    <script src="{{ asset('assets/vendor/datatable/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/glightbox/glightbox.min.js') }}"></script>
    <script src="{{ asset('assets/js/customizer.js') }}"></script>
    <script src="{{ asset('assets/vendor/prism/prism.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/sweetalert/sweetalert.js') }}"></script>
    <script src="{{ asset('assets/vendor/select/select2.min.js') }}"></script>
    
    <!-- App Scripts -->
    <script src="{{ asset('assets/js/script.js') }}"></script>
    <script src="{{ asset('assets/js/formvalidation.js') }}"></script>
    
    <!-- Toastr -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
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

        @if ($errors->any())
            toastr.error("{{ $errors->first() }}");
        @endif
    </script>

    @yield('scripts')

</body>

</html>