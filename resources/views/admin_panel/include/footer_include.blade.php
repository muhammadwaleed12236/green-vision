<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- jQuery -->
<script src="{{ url('assets/js/jquery-3.6.0.min.js') }}"></script>

<!-- Bootstrap & Plugins -->
<script src="{{ url('assets/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ url('assets/js/feather.min.js') }}"></script>
<script src="{{ url('assets/js/jquery.slimscroll.min.js') }}"></script>

<!-- DataTables -->
<script src="{{ url('assets/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ url('assets/js/dataTables.bootstrap4.min.js') }}"></script>

<!-- ApexCharts -->
<script src="{{ url('assets/plugins/apexchart/apexcharts.min.js') }}"></script>
<script src="{{ url('assets/plugins/apexchart/chart-data.js') }}"></script>

<!-- Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Custom JS -->
<script src="{{ url('assets/js/script.js') }}"></script>

<script>
    $(document).ready(function () {
        // Initialize Select2
        $('.search').select2({
            placeholder: "Search Or Select",
            allowClear: true,
            width: '100%'  // Fit with Bootstrap
        });

        // Hide global loader once page is ready
        let globalLoader = document.getElementById("global-loader");
        if (globalLoader) {
            globalLoader.style.display = "none";
        }

        // Initialize Feather Icons
        if (typeof feather !== 'undefined') {
            feather.replace();
        }

        // Optional: Initialize DataTables for tables with class 'datatable'
        if ($.fn.DataTable) {
            $('.datatable').DataTable({
                retrieve: true
            });
        }
    });
</script>

@stack('scripts')
</body>
</html>
