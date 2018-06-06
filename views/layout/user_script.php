<!--   Core JS Files   -->
<script src="./assets/js/core/jquery.min.js" type="text/javascript"></script>
<script src="./assets/js/core/popper.min.js" type="text/javascript"></script>
<script src="./assets/js/core/bootstrap-material-design.min.js" type="text/javascript"></script>
<script src="./assets/js/plugins/moment.min.js"></script>
<!--	Plugin for the Datepicker, full documentation here: https://github.com/Eonasdan/bootstrap-datetimepicker -->
<script src="./assets/js/plugins/bootstrap-datetimepicker.js" type="text/javascript"></script>
<!--  Plugin for the Sliders, full documentation here: http://refreshless.com/nouislider/ -->
<script src="./assets/js/plugins/nouislider.min.js" type="text/javascript"></script>
<!-- Control Center for Now Ui Kit: parallax effects, scripts for the example pages etc -->
<script src="./assets/js/material-kit.js?v=2.0.3" type="text/javascript"></script>
<!-- Bootstrap Select Js --!>
<script src="./assets/js/bootstrap-select.min.js" type="text/javascript"></script>
<!-- Extra Script -->
<script>
    $(document).ready(function () {
        $('.select-node-type').on('change', function () {
            let valueSelect = $(this).val();
            let point = $(this).data('point');
            let allInput = '.' + point + '-' + 'input';
            let selectorInput = '#' + point + '_' + valueSelect;
            $(allInput).hide();
            $(selectorInput).show();
        });
        $('#search_mode').on('change', function () {
            $('.form-block').hide();
            let selectVal = $(this).val();
            $('#' + selectVal).show();
        });
    });
</script>