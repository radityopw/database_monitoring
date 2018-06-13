<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <link rel="apple-touch-icon" sizes="76x76" href="./assets/icons/portal-icon-72.png">
    <link rel="icon" type="image/png" href="./assets/icons/portal-icon-96.png">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <title>
        Dependency Tool
    </title>
    <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no' name='viewport'
    />
    <!--     Default Material Kit Fonts     -->
    <!-- <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons" -->
    <link rel="stylesheet" type="text/css" href="./assets/fonts/font-material-kit.css" />
    <!-- Neo4j Minified CSS -->
    <link rel="stylesheet" type="text/css" href="./assets/css/neo4jd3.min.css">
    <!-- Font Awesome CSS -->
    <link rel="stylesheet" type="text/css" href="./assets/css/font-awesome.min.css">
    <!-- CSS Files -->
    <link href="./assets/css/material-kit.css?v=2.0.3" rel="stylesheet" />
    <!-- CSS Just for demo purpose, don't include it in your project -->
    <link href="./assets/demo/demo.css" rel="stylesheet" />
    <!-- CSS for bootstrap-select -->
    <link href="./assets/css/bootstrap-select.min.css" rel="stylesheet" />
</head>

<body>
    <div class="main">
        <div class="container">
            <div class="row">
                <div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">SP Dependency Tool</h4>
                            <p class="card-text">Stored Procedure Dependency Tool is a tool to detect and identify the relationship of Stored
                                Procedure and other objects in the database and visualize them in the form of graph.</p>
                            <a href="./SP_result.php" class="btn btn-primary">Enter</a>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">User Dependency Tool</h4>
                            <p class="card-text">User Dependency Tool is a tool to detect and identify the relationship of permissions and roles
                                of users to objects in the database and visualize them in the form of graph.</p>
                            <a href="./user_viewer.php" class="btn btn-primary">Enter</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <footer class="footer footer-default">
        <div class="container">
            <nav class="float-left">
                <ul>
                    <li>
                        <a href="#">
                            Dependency Tool
                        </a>
                    </li>
                </ul>
            </nav>
            <div class="copyright float-right">
                &copy;
                <script>
                    document.write(new Date().getFullYear())
                </script>, made with
                <i class="material-icons">favorite</i> by
                <a href="#" target="_blank">Dependency Team.</a>
            </div>
        </div>
    </footer>
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
    <!-- Bootstrap Select Js -->
    <script src="./assets/js/bootstrap-select.min.js" type="text/javascript"></script>
    <!-- Sweetalert 2 All (CSS and Js) -->
    <script src="./assets/js/sweetalert2.all.min.js"></script>
    <!-- Polyfill Js for Android Browser and IE11  -->
    <script src="./assets/js/polyfill.min.js"></script>
    <!-- D3 Js -->
    <script type="text/javascript" src='./assets/js/d3.min.js'></script>
    <!-- Neo4j Js -->
    <script type="text/javascript" src='./assets/js/neo4jd3.js'></script>
</body>


</html>