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
<!-- Neo4j JS -->
<!-- Bootstrap Select Js --!>
<script src="./assets/js/bootstrap-select.min.js" type="text/javascript"></script>
<!-- Sweetalert 2 All (CSS and Js) -->
<script src="./assets/js/sweetalert2.all.min.js"></script>
<!-- Polyfill Js for Android Browser and IE11  -->
<script src="./assets/js/polyfill.min.js"></script>
<!-- D3 Js -->
<script type="text/javascript" src='./assets/js/d3.min.js'></script>
<!-- Neo4j Js -->
<script type="text/javascript" src='./assets/js/neo4jd3.js'></script>
<!-- Extra Script -->
<script>
    $(document).ready(function () {
        $('.select-node-type').on('change', function () {
            let valueSelect = $(this).val();
            let point = $(this).data('point');
            let allInput = '.' + point + '-' + 'input';
            let selectorInput = '#' + point + '_' + valueSelect;
            let pointElSelect = '.' + point + '_' + 'node';
            $(allInput).hide();
            $(pointElSelect).val('');
            $(selectorInput).show();
        });
        $('#search_mode').on('change', function () {
            $('.form-block').hide();
            let selectVal = $(this).val();
            $('#' + selectVal).show();
        });
        <?php 
            if($error) { 
        ?>
        swal({
            type: 'error',
            title: 'Alert!',
            text: '<?php echo $errorMessage; ?>',
            showCloseButton: true,
            showConfirmButton: false,
        });
        <?php 
            } else { 
        ?>
        let neo4jd3 = new Neo4jd3('#neo4jd3', {
            icons: {
                'Server': 'server',
                'Database': 'database',
                'Column': 'columns',
                'Object': 'object-group',
                'User': 'user'
                // 'Schema': 'gear',
                // 'Table': 'table',
                // 'SP': 'f288',
                // 'Function': 'f1c9'
            },
            images: {
                'Schema': './assets/icons/schema.svg',
                'Role': './assets/icons/role.svg'
            },
            minCollision: 60,
            neo4jData: <?php echo $resultNeo4jCollection->toJson(); ?>,
            nodeRadius: 20,
            // highlight: [{
            //     class: 'SP',
            //     property: 'surname',
            //     value: '<?= $sp_select; ?>',
            // }],

            zoomFit: true,
        });
        <?php
            }
        ?>
    });
</script>