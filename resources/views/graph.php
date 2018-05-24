<!DOCTYPE html>
<html>
<head>
	<title>GRAPH RESULT</title>
    <!-- <link rel="stylesheet" type="text/css" href="/assets/css/bootstrap.min.css"> -->
	<link rel="stylesheet" type="text/css" href="assets/css/neo4jd3.min.css">
    <link rel="stylesheet" type="text/css" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/material-kit.css?v=2.0.3">
    <!-- <script type="text/javascript" src='/assets/js/bootstrap.min.js'></script> -->
    <script type="text/javascript" src='assets/js/d3.min.js'></script>
	<script type="text/javascript" src='assets/js/neo4jd3.js'></script>

    <style>
        body,
        html,
        .neo4jd3 {
            height: 100%;
            overflow: hidden;
        }

    </style>
</head>
<body>
    <div class="container-fluid" style='height: 100%'>
    <div class="row" style='height: 100%'>
    <div class="col-md-3" style="padding-top:15px;">
    <form method="post">
        <div class="form-group">
            <label>Search <input type="textarea" id="cari" name="cari" placeholder="Search..." class="form-control"></label>
        </div>
        <div class='form-group'>
            <div class="checkbox">
                <label><input type="checkbox" name="tblcheck" id='tblcheck'>
                Table
                </label>
            </div>
               <input type="text" id="table" name="table" placeholder="Table" class="form-control" disabled>
        </div>
        <div class='form-group'>
            <div class="checkbox">
                <label><input type="checkbox" name="spcheck" id='spcheck' disabled>
                Procedure
                </label>
            </div>
            <input type="text" id="db" name="db" placeholder="Stored Procedure" class="form-control" disabled>
        </div>
        <div class='form-group'>
            <div class="checkbox">
                <label><input type="checkbox" name="funccheck" id='funccheck' disabled>
                Function
                </label>
            </div>
            <input type="text" id="func" name="func" placeholder="Function" class="form-control" disabled>
        </div>
        <div class='form-group'>
            <div class="checkbox">
                <label><input type="checkbox" name="usrcheck" id='usrcheck' disabled>
                User
                </label>
            </div>
            <input type="text" id="user" name="user" placeholder="User.." class="form-control" disabled>
        </div>
        <div class='form-group'>
            <div class="checkbox">
                <label><input type="checkbox" name="rolecheck" id='rolecheck' disabled>
                Role
                </label>
            </div>
            <input type="text" id="role" name="role" placeholder="Role" class="form-control" disabled>
        </div>                       
        <div>
            <div class="checkbox">
                <label><input type="checkbox" name="allcheck" id='allcheck'>
                Show All
                </label>
            </div>
            <div><button type='submit' class='btn btn-primary' name='btn-gen'>GENERATE</button></div>
    </div>
</form>

<!-- Legends
<ul class="fa-ul">
  <li><i class="fa-li fa fa-server"></i>Server</li>
  <li><i class="fa-li fa fa-database"></i>Database</li>
  <li><i class="fa-li fa fa-gear"></i>Schema</li>
  <li><i class="fa-li fa fa-table"></i>Table</li>
</ul> -->

</div>
<div class="col-md-7"><div id='neo4jd3'></div></div>
</div></div>
<?php include 'config.php';
    if(isset($_POST['btn-gen'])){
        if(isset($_POST['allcheck'])){
            $resnode = $neo->run('MATCH (a) return a.name as name,id(a) as id,labels(a) as label');
            $resrel = $neo->run('MATCH (a)-[r]->(b) return id(r) as id,id(a) as start,id(b) as end, type(r) as type');
        }
        //jika server terisi
        if(isset($_POST['srv'])){
            $resnode = $neo->run("match (a:Server) where a.name contains '".$_POST['srv']."' return a.name as name,id(a) as id,labels(a) as label");
        }
        //jika input database tercentang tapi tidak terisi
        if(isset($_POST['dbcheck']) && empty($_POST['db'])){
            $resnode = $neo->run("
                //Node Server
                MATCH (a:Server) where a.name contains '".$_POST['srv']."' return a.name as name,id(a) as id,labels(a) as label UNION
                //Node Database
                MATCH (a)-[:Db]->(b) where a.name contains '".$_POST['srv']."' return b.name as name,id(b) as id,labels(b) as label");
            $resrel = $neo->run("
                //Server-Database
                MATCH (a)-[r:Db]->(b) where a.name contains '".$_POST['srv']."' return id(r) as id,id(a) as start,id(b) as end, type(r) as type");
        }
        //jika input database tercentang dan terisi
        if(isset($_POST['db'])){
            $resnode = $neo->run("
                //Node Server
                MATCH (a:Server) where a.name contains '".$_POST['srv']."' return a.name as name,id(a) as id,labels(a) as label UNION
                //Node Database
                MATCH (a)-[:Db]->(b) where a.name contains '".$_POST['srv']."' AND b.name contains '".$_POST['db']."' return b.name as name,id(b) as id,labels(b) as label");
            $resrel = $neo->run("
                //Server-Database
                MATCH (a)-[r:Db]->(b) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' return id(r) as id,id(a) as start,id(b) as end, type(r) as type");
        }        
        //jika input Schema tercentang tapi tidak terisi
        if(isset($_POST['schcheck']) && empty($_POST['sch'])){
            $resnode = $neo->run("
                //Node Server
                MATCH (a:Server) where a.name contains '".$_POST['srv']."' return a.name as name,id(a) as id,labels(a) as label UNION
                //Node Database
                MATCH (a)-[:Db]->(b) where a.name contains '".$_POST['srv']."' AND b.name contains '".$_POST['db']."' return b.name as name,id(b) as id,labels(b) as label UNION 
                //Node Schema
                MATCH (a)-[:Db]->(b)-[:Sch]->(c) where b.name contains '".$_POST['db']."' return id(c) as id,c.name as name,labels(c) as label");
            $resrel = $neo->run("
                //Server-Database
                MATCH (a)-[r:Db]->(b) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' return id(r) as id,id(a) as start,id(b) as end, type(r) as type UNION 
                //Database-Schema
                MATCH (a)-[:Db]->(b)-[r:Sch]->(c) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' return id(r) as id,id(b) as start,id(c) as end, type(r) as type");
        }
        //jika input Schema tercentang dan terisi
        if(isset($_POST['sch'])){
            $resnode=$neo->run("
                //Node Server
                MATCH (a:Server) where a.name contains '".$_POST['srv']."' return a.name as name,id(a) as id,labels(a) as label UNION
                //Node Database
                MATCH (a)-[:Db]->(b) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' return b.name as name,id(b) as id,labels(b) as label UNION
                //Node Schema
                MATCH (a)-[:Db]->(b)-[:Sch]->(c) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' and c.name contains '".$_POST['sch']."' return c.name as name,id(c) as id,labels(c) as label");
            $resrel=$neo->run("
                //Relasi Server-Database
                MATCH (a)-[r:Db]->(b) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' return id(r) as id,id(a) as start,id(b) as end, type(r) as type UNION
                //Relasi Database-Schema
                MATCH (a)-[:Db]->(b)-[r:Sch]->(c) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' and c.name contains '".$_POST['sch']."' return id(r) as id,id(b) as start,id(c) as end, type(r) as type");
        }
        //jika input Table tercentang tapi tidak terisi
        if(isset($_POST['tblcheck']) && empty($_POST['tbl'])){
            $resnode = $neo->run("
                //Node Server
                MATCH (a:Server) where a.name contains '".$_POST['srv']."' return a.name as name,id(a) as id,labels(a) as label UNION
                //Node Database
                MATCH (a)-[:Db]->(b) where a.name contains '".$_POST['srv']."' AND b.name contains '".$_POST['db']."' return b.name as name,id(b) as id,labels(b) as label UNION 
                //Node Schema
                MATCH (a)-[:Db]->(b)-[:Sch]->(c) where a.name contains '".$_POST['srv']."' AND b.name contains '".$_POST['db']."' AND c.name contains '".$_POST['sch']."' return id(c) as id,c.name as name,labels(c) as label UNION 
                //Node Table All
                MATCH (a)-[:Db]->(b)-[:Sch]->(c)-[:Tbl]->(d) where a.name contains '".$_POST['srv']."' AND b.name contains '".$_POST['db']."' AND c.name contains '".$_POST['sch']."' return d.name as name,id(d) as id,labels(d) as label");
            $resrel = $neo->run("
                MATCH (a)-[r:Db]->(b) where a.name contains '".$_POST['srv']."' return id(r) as id,id(a) as start,id(b) as end, type(r) as type UNION 
                MATCH (a)-[:Db]->(b)-[r:Sch]->(c) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' return id(r) as id,id(b) as start,id(c) as end, type(r) as type UNION
                MATCH (a)-[:Db]->(b)-[:Sch]->(c)-[r:Tbl]->(d) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' and c.name contains '".$_POST['sch']."' return id(r) as id,id(c) as start,id(d) as end, type(r) as type");
        }
        //jika input Table tercentang dan terisi
        if(isset($_POST['tbl'])){
            $resnode=$neo->run("
                //Node Server
                MATCH (a:Server) where a.name contains '".$_POST['srv']."' return a.name as name,id(a) as id,labels(a) as label UNION
                //Node Database
                MATCH (a)-[:Db]->(b) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' return b.name as name,id(b) as id,labels(b) as label UNION
                //Node Schema
                MATCH (a)-[:Db]->(b)-[:Sch]->(c) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' and c.name contains '".$_POST['sch']."' return c.name as name,id(c) as id,labels(c) as label UNION
                //Node Table
                MATCH (a)-[:Db]->(b)-[:Sch]->(c)-[:Tbl]->(d) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' and c.name contains '".$_POST['sch']."' and d.name contains '".$_POST['tbl']."' return d.name as name,id(d) as id,labels(d) as label");
            $resrel=$neo->run("
                //Relasi Server-Database
                MATCH (a)-[r:Db]->(b) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' return id(r) as id,id(a) as start,id(b) as end, type(r) as type UNION
                //Relasi Database-Schema
                MATCH (a)-[:Db]->(b)-[r:Sch]->(c) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' and c.name contains '".$_POST['sch']."' return id(r) as id,id(b) as start,id(c) as end, type(r) as type UNION
                //Relasi Schema-Table
                MATCH (a)-[:Db]->(b)-[:Sch]->(c)-[r:Tbl]->(d) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' and c.name contains '".$_POST['sch']."' and d.name contains '".$_POST['tbl']."' return id(r) as id,id(c) as start,id(d) as end, type(r) as type");
        }        
        //jika input Column tercentang tapi tidak terisi
        if(isset($_POST['colcheck']) && empty($_POST['col'])){
            $resnode =$neo->run("
                //Node Server
                MATCH (a:Server) where a.name contains '".$_POST['srv']."' return a.name as name,id(a) as id,labels(a) as label UNION
                //Node Database
                MATCH (a)-[:Db]->(b) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' return b.name as name,id(b) as id,labels(b) as label UNION
                //Node Schema
                MATCH (a)-[:Db]->(b)-[:Sch]->(c) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' and c.name contains '".$_POST['sch']."' return c.name as name,id(c) as id,labels(c) as label UNION
                //Node Table
                MATCH (a)-[:Db]->(b)-[:Sch]->(c)-[:Tbl]->(d) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' and c.name contains '".$_POST['sch']."' and d.name contains '".$_POST['tbl']."' return d.name as name,id(d) as id,labels(d) as label UNION
                //Node Column
                MATCH (a)-[:Db]->(b)-[:Sch]->(c)-[:Tbl]->(d)-[:Col]->(e) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' and c.name contains '".$_POST['sch']."' and d.name contains '".$_POST['tbl']."' and e.name contains '".$_POST['col']."' return e.name as name,id(e) as id,labels(e) as label
            ");
            $resrel = $neo->run("
                //Relasi Server-Database
                MATCH (a)-[r:Db]->(b) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' return id(r) as id,id(a) as start,id(b) as end, type(r) as type UNION
                //Relasi Database-Schema
                MATCH (a)-[:Db]->(b)-[r:Sch]->(c) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' and c.name contains '".$_POST['sch']."' return id(r) as id,id(b) as start,id(c) as end, type(r) as type UNION
                //Relasi Schema-Table
                MATCH (a)-[:Db]->(b)-[:Sch]->(c)-[r:Tbl]->(d) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' and c.name contains '".$_POST['sch']."' and d.name contains '".$_POST['tbl']."' return id(r) as id,id(c) as start,id(d) as end, type(r) as type UNION
                //Relasi Table-Column
                MATCH (a)-[:Db]->(b)-[:Sch]->(c)-[:Tbl]->(d)-[r:Col]->(e) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' and c.name contains '".$_POST['sch']."' and d.name contains '".$_POST['tbl']."' and e.name contains '".$_POST['col']."' return id(r) as id,id(d) as start,id(e) as end, type(r) as type"
            );$resnode =$neo->run("
                //Node Server
                MATCH (a:Server) where a.name contains '".$_POST['srv']."' return a.name as name,id(a) as id,labels(a) as label UNION
                //Node Database
                MATCH (a)-[:Db]->(b) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' return b.name as name,id(b) as id,labels(b) as label UNION
                //Node Schema
                MATCH (a)-[:Db]->(b)-[:Sch]->(c) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' and c.name contains '".$_POST['sch']."' return c.name as name,id(c) as id,labels(c) as label UNION
                //Node Table
                MATCH (a)-[:Db]->(b)-[:Sch]->(c)-[:Tbl]->(d) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' and c.name contains '".$_POST['sch']."' and d.name contains '".$_POST['tbl']."' return d.name as name,id(d) as id,labels(d) as label UNION
                //Node Column
                MATCH (a)-[:Db]->(b)-[:Sch]->(c)-[:Tbl]->(d)-[:Col]->(e) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' and c.name contains '".$_POST['sch']."' and d.name contains '".$_POST['tbl']."' return e.name as name,id(e) as id,labels(e) as label
            ");
            $resrel = $neo->run("
                //Relasi Server-Database
                MATCH (a)-[r:Db]->(b) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' return id(r) as id,id(a) as start,id(b) as end, type(r) as type UNION
                //Relasi Database-Schema
                MATCH (a)-[:Db]->(b)-[r:Sch]->(c) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' and c.name contains '".$_POST['sch']."' return id(r) as id,id(b) as start,id(c) as end, type(r) as type UNION
                //Relasi Schema-Table
                MATCH (a)-[:Db]->(b)-[:Sch]->(c)-[r:Tbl]->(d) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' and c.name contains '".$_POST['sch']."' and d.name contains '".$_POST['tbl']."' return id(r) as id,id(c) as start,id(d) as end, type(r) as type UNION
                //Relasi Table-Column
                MATCH (a)-[:Db]->(b)-[:Sch]->(c)-[:Tbl]->(d)-[r:Col]->(e) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' and c.name contains '".$_POST['sch']."' and d.name contains '".$_POST['tbl']."' return id(r) as id,id(d) as start,id(e) as end, type(r) as type"
            );
        }
        //jika input Column tercentang dan terisi
        if(isset($_POST['col'])){
            $resnode =$neo->run("
                //Node Server
                MATCH (a:Server) where a.name contains '".$_POST['srv']."' return a.name as name,id(a) as id,labels(a) as label UNION
                //Node Database
                MATCH (a)-[:Db]->(b) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' return b.name as name,id(b) as id,labels(b) as label UNION
                //Node Schema
                MATCH (a)-[:Db]->(b)-[:Sch]->(c) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' and c.name contains '".$_POST['sch']."' return c.name as name,id(c) as id,labels(c) as label UNION
                //Node Table
                MATCH (a)-[:Db]->(b)-[:Sch]->(c)-[:Tbl]->(d) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' and c.name contains '".$_POST['sch']."' and d.name contains '".$_POST['tbl']."' return d.name as name,id(d) as id,labels(d) as label UNION
                //Node Column
                MATCH (a)-[:Db]->(b)-[:Sch]->(c)-[:Tbl]->(d)-[:Col]->(e) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' and c.name contains '".$_POST['sch']."' and d.name contains '".$_POST['tbl']."' and e.name contains '".$_POST['col']."' return e.name as name,id(e) as id,labels(e) as label
            ");
            $resrel = $neo->run("
                //Relasi Server-Database
                MATCH (a)-[r:Db]->(b) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' return id(r) as id,id(a) as start,id(b) as end, type(r) as type UNION
                //Relasi Database-Schema
                MATCH (a)-[:Db]->(b)-[r:Sch]->(c) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' and c.name contains '".$_POST['sch']."' return id(r) as id,id(b) as start,id(c) as end, type(r) as type UNION
                //Relasi Schema-Table
                MATCH (a)-[:Db]->(b)-[:Sch]->(c)-[r:Tbl]->(d) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' and c.name contains '".$_POST['sch']."' and d.name contains '".$_POST['tbl']."' return id(r) as id,id(c) as start,id(d) as end, type(r) as type UNION
                //Relasi Table-Column
                MATCH (a)-[:Db]->(b)-[:Sch]->(c)-[:Tbl]->(d)-[r:Col]->(e) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' and c.name contains '".$_POST['sch']."' and d.name contains '".$_POST['tbl']."' and e.name contains '".$_POST['col']."' return id(r) as id,id(d) as start,id(e) as end, type(r) as type"
            );
        }
        //jika input FK tercentang
        if(isset($_POST['fkcheck'])){
            $resnode =$neo->run("
                //Node Server
                MATCH (a:Server) where a.name contains '".$_POST['srv']."' return a.name as name,id(a) as id,labels(a) as label UNION
                //Node Database
                MATCH (a)-[:Db]->(b) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' return b.name as name,id(b) as id,labels(b) as label UNION
                //Node Schema
                MATCH (a)-[:Db]->(b)-[:Sch]->(c) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' and c.name contains '".$_POST['sch']."' return c.name as name,id(c) as id,labels(c) as label UNION
                //Node Table
                MATCH (a)-[:Db]->(b)-[:Sch]->(c)-[:Tbl]->(d) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' and c.name contains '".$_POST['sch']."' and d.name contains '".$_POST['tbl']."' return d.name as name,id(d) as id,labels(d) as label UNION
                //Node Column
                MATCH (a)-[:Db]->(b)-[:Sch]->(c)-[:Tbl]->(d)-[:Col]->(e) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' and c.name contains '".$_POST['sch']."' and d.name contains '".$_POST['tbl']."' and e.name contains '".$_POST['col']."' return e.name as name,id(e) as id,labels(e) as label UNION
                //Node Column FK
                MATCH (a)-[:Db]->(b)-[:Sch]->(c)-[:Tbl]->(d)-[:Col]->(e)-[r]-(f) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' and c.name contains '".$_POST['sch']."' and d.name contains '".$_POST['tbl']."' and e.name contains '".$_POST['col']."' and type(r) contains 'FK' return f.name as name,id(f) as id,labels(f) as label UNION
                //Node Table FK
                MATCH (a)-[:Db]->(b)-[:Sch]->(c)-[:Tbl]->(d)-[:Col]->(e)-[r]-(f)<-[:Col]-(g) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' and c.name contains '".$_POST['sch']."' and d.name contains '".$_POST['tbl']."' and e.name contains '".$_POST['col']."' and type(r) contains 'FK' return g.name as name,id(g) as id,labels(g) as label UNION
                //Node Schema FK
                MATCH (a)-[:Db]->(b)-[:Sch]->(c)-[:Tbl]->(d)-[:Col]->(e)-[r]-(f)<-[:Col]-(g)<-[:Tbl]-(h) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' and c.name contains '".$_POST['sch']."' and d.name contains '".$_POST['tbl']."' and e.name contains '".$_POST['col']."' and type(r) contains 'FK' return h.name as name,id(h) as id,labels(h) as label UNION
                //Node Db FK
                MATCH (a)-[:Db]->(b)-[:Sch]->(c)-[:Tbl]->(d)-[:Col]->(e)-[r]-(f)<-[:Col]-(g)<-[:Tbl]-(h)<-[:Sch]-(i) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' and c.name contains '".$_POST['sch']."' and d.name contains '".$_POST['tbl']."' and e.name contains '".$_POST['col']."' and type(r) contains 'FK' return i.name as name,id(i) as id,labels(i) as label
                ");
            $resrel = $neo->run("
                //Relasi Server-Database
                MATCH (a)-[r:Db]->(b) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' return id(r) as id,id(a) as start,id(b) as end, type(r) as type UNION
                //Relasi Database-Schema
                MATCH (a)-[:Db]->(b)-[r:Sch]->(c) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' and c.name contains '".$_POST['sch']."' return id(r) as id,id(b) as start,id(c) as end, type(r) as type UNION
                //Relasi Schema-Table
                MATCH (a)-[:Db]->(b)-[:Sch]->(c)-[r:Tbl]->(d) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' and c.name contains '".$_POST['sch']."' and d.name contains '".$_POST['tbl']."' return id(r) as id,id(c) as start,id(d) as end, type(r) as type UNION
                //Relasi Table-Column
                MATCH (a)-[:Db]->(b)-[:Sch]->(c)-[:Tbl]->(d)-[r:Col]->(e) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' and c.name contains '".$_POST['sch']."' and d.name contains '".$_POST['tbl']."' and e.name contains '".$_POST['col']."' return id(r) as id,id(d) as start,id(e) as end, type(r) as type UNION
                //Relasi Column-Column FK
                MATCH (a)-[:Db]->(b)-[:Sch]->(c)-[:Tbl]->(d)-[:Col]->(e)-[r]-(f) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' and c.name contains '".$_POST['sch']."' and d.name contains '".$_POST['tbl']."' and e.name contains '".$_POST['col']."' and type(r) contains 'FK' return id(r) as id,id(e) as start,id(f) as end, type(r) as type UNION
                //Relasi Column-Table FK
                MATCH (a)-[:Db]->(b)-[:Sch]->(c)-[:Tbl]->(d)-[:Col]->(e)-[r]-(f)<-[x]-(g) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' and c.name contains '".$_POST['sch']."' and d.name contains '".$_POST['tbl']."' and e.name contains '".$_POST['col']."' and type(r) contains 'FK' return id(x) as id,id(g) as start,id(f) as end, type(x) as type UNION
                //Relasi Table-Schema FK
                MATCH (a)-[:Db]->(b)-[:Sch]->(c)-[:Tbl]->(d)-[:Col]->(e)-[r]-(f)<-[:Col]-(g)<-[x]-(h) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' and c.name contains '".$_POST['sch']."' and d.name contains '".$_POST['tbl']."' and e.name contains '".$_POST['col']."' and type(r) contains 'FK' return id(x) as id,id(h) as start,id(g) as end, type(x) as type UNION
                //Relasi Schema-Db FK
                MATCH (a)-[:Db]->(b)-[:Sch]->(c)-[:Tbl]->(d)-[:Col]->(e)-[r]-(f)<-[:Col]-(g)<-[:Tbl]-(h)<-[x]-(i) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' and c.name contains '".$_POST['sch']."' and d.name contains '".$_POST['tbl']."' and e.name contains '".$_POST['col']."' and type(r) contains 'FK' return id(x) as id,id(i) as start,id(h) as end, type(x) as type");
        }
        //jika input FK tercentang
        if(isset($_POST['jocheck'])){
            $resnode =$neo->run("
                //Node Server
                MATCH (a:Server) where a.name contains '".$_POST['srv']."' return a.name as name,id(a) as id,labels(a) as label UNION
                //Node Database
                MATCH (a)-[:Db]->(b) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' return b.name as name,id(b) as id,labels(b) as label UNION
                //Node Schema
                MATCH (a)-[:Db]->(b)-[:Sch]->(c) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' and c.name contains '".$_POST['sch']."' return c.name as name,id(c) as id,labels(c) as label UNION
                //Node Table
                MATCH (a)-[:Db]->(b)-[:Sch]->(c)-[:Tbl]->(d) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' and c.name contains '".$_POST['sch']."' and d.name contains '".$_POST['tbl']."' return d.name as name,id(d) as id,labels(d) as label UNION
                //Node Column
                MATCH (a)-[:Db]->(b)-[:Sch]->(c)-[:Tbl]->(d)-[:Col]->(e) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' and c.name contains '".$_POST['sch']."' and d.name contains '".$_POST['tbl']."' and e.name contains '".$_POST['col']."' return e.name as name,id(e) as id,labels(e) as label UNION
                //Node Column JOIN
                MATCH (a)-[:Db]->(b)-[:Sch]->(c)-[:Tbl]->(d)-[:Col]->(e)-[r]-(f) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' and c.name contains '".$_POST['sch']."' and d.name contains '".$_POST['tbl']."' and e.name contains '".$_POST['col']."' and type(r) contains 'JOIN' return f.name as name,id(f) as id,labels(f) as label UNION
                //Node Table JOIN
                MATCH (a)-[:Db]->(b)-[:Sch]->(c)-[:Tbl]->(d)-[:Col]->(e)-[r]-(f)<-[:Col]-(g) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' and c.name contains '".$_POST['sch']."' and d.name contains '".$_POST['tbl']."' and e.name contains '".$_POST['col']."' and type(r) contains 'JOIN' return g.name as name,id(g) as id,labels(g) as label UNION
                //Node Schema JOIN
                MATCH (a)-[:Db]->(b)-[:Sch]->(c)-[:Tbl]->(d)-[:Col]->(e)-[r]-(f)<-[:Col]-(g)<-[:Tbl]-(h) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' and c.name contains '".$_POST['sch']."' and d.name contains '".$_POST['tbl']."' and e.name contains '".$_POST['col']."' and type(r) contains 'JOIN' return h.name as name,id(h) as id,labels(h) as label UNION
                //Node Db JOIN
                MATCH (a)-[:Db]->(b)-[:Sch]->(c)-[:Tbl]->(d)-[:Col]->(e)-[r]-(f)<-[:Col]-(g)<-[:Tbl]-(h)<-[:Sch]-(i) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' and c.name contains '".$_POST['sch']."' and d.name contains '".$_POST['tbl']."' and e.name contains '".$_POST['col']."' and type(r) contains 'JOIN' return i.name as name,id(i) as id,labels(i) as label
                ");
            $resrel = $neo->run("
                //Relasi Server-Database
                MATCH (a)-[r:Db]->(b) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' return id(r) as id,id(a) as start,id(b) as end, type(r) as type UNION
                //Relasi Database-Schema
                MATCH (a)-[:Db]->(b)-[r:Sch]->(c) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' and c.name contains '".$_POST['sch']."' return id(r) as id,id(b) as start,id(c) as end, type(r) as type UNION
                //Relasi Schema-Table
                MATCH (a)-[:Db]->(b)-[:Sch]->(c)-[r:Tbl]->(d) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' and c.name contains '".$_POST['sch']."' and d.name contains '".$_POST['tbl']."' return id(r) as id,id(c) as start,id(d) as end, type(r) as type UNION
                //Relasi Table-Column
                MATCH (a)-[:Db]->(b)-[:Sch]->(c)-[:Tbl]->(d)-[r:Col]->(e) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' and c.name contains '".$_POST['sch']."' and d.name contains '".$_POST['tbl']."' and e.name contains '".$_POST['col']."' return id(r) as id,id(d) as start,id(e) as end, type(r) as type UNION
                //Relasi Column-Column JOIN
                MATCH (a)-[:Db]->(b)-[:Sch]->(c)-[:Tbl]->(d)-[:Col]->(e)-[r]-(f) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' and c.name contains '".$_POST['sch']."' and d.name contains '".$_POST['tbl']."' and e.name contains '".$_POST['col']."' and type(r) contains 'JOIN' return id(r) as id,id(e) as start,id(f) as end, type(r) as type UNION
                //Relasi Column-Table JOIN
                MATCH (a)-[:Db]->(b)-[:Sch]->(c)-[:Tbl]->(d)-[:Col]->(e)-[r]-(f)<-[x]-(g) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' and c.name contains '".$_POST['sch']."' and d.name contains '".$_POST['tbl']."' and e.name contains '".$_POST['col']."' and type(r) contains 'JOIN' return id(x) as id,id(g) as start,id(f) as end, type(x) as type UNION
                //Relasi Table-Schema JOIN
                MATCH (a)-[:Db]->(b)-[:Sch]->(c)-[:Tbl]->(d)-[:Col]->(e)-[r]-(f)<-[:Col]-(g)<-[x]-(h) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' and c.name contains '".$_POST['sch']."' and d.name contains '".$_POST['tbl']."' and e.name contains '".$_POST['col']."' and type(r) contains 'JOIN' return id(x) as id,id(h) as start,id(g) as end, type(x) as type UNION
                //Relasi Schema-Db JOIN
                MATCH (a)-[:Db]->(b)-[:Sch]->(c)-[:Tbl]->(d)-[:Col]->(e)-[r]-(f)<-[:Col]-(g)<-[:Tbl]-(h)<-[x]-(i) where a.name contains '".$_POST['srv']."' and b.name contains '".$_POST['db']."' and c.name contains '".$_POST['sch']."' and d.name contains '".$_POST['tbl']."' and e.name contains '".$_POST['col']."' and type(r) contains 'JOIN' return id(x)  as id,id(i) as start,id(h) as end, type(x) as type");
        }
    //Generate JSON from all Nodes
    foreach ($resnode->getRecords() as $record){
        $nodes[] = ["id"=>$record->value('id'),
                    "labels"=>$record->value('label'),
                    "properties"=>[
                        "name"=> $record->value('name')
                        //taruh value lain di sini (jika ada)
                    ]
        ];
    }
    //Generate JSON from all Relationships
    if(isset($resrel)){
        if($resrel->getRecords()==NULL){
            $rel = [];
        }else{
            foreach ($resrel->getRecords() as $record) {
                $rel[] = ["id"=>$record->value('id'),
                    "type"=>$record->value('type'),
                    "startNode"=>$record->value('start'),
                    "endNode"=>$record->value('end'),
                    "properties"=>array()
                ];
            }
        }
    }else{$rel = [];}
    $json = ["results" => array([
                "data" => array([
                "graph" => array(
                "nodes" => $nodes,
                "relationships" => $rel
                )])])];
    file_put_contents('neodata.json', json_encode($json));
    } ?>    
    <script type="text/javascript">
    document.getElementById('srvcheck').onchange = function() {
        document.getElementById('srv').disabled = !this.checked;
        document.getElementById('dbcheck').disabled = !this.checked;
    };
    document.getElementById('dbcheck').onchange = function() {
        document.getElementById('db').disabled = !this.checked;
        document.getElementById('schcheck').disabled = !this.checked;
    };
    document.getElementById('schcheck').onchange = function() {
        document.getElementById('sch').disabled = !this.checked;
        document.getElementById('tblcheck').disabled = !this.checked;
    };
    document.getElementById('tblcheck').onchange = function() {
        document.getElementById('tbl').disabled = !this.checked;
        document.getElementById('colcheck').disabled = !this.checked;
    };
    document.getElementById('colcheck').onchange = function() {
        document.getElementById('col').disabled = !this.checked;
        document.getElementById('fkcheck').disabled = !this.checked;
        document.getElementById('jocheck').disabled = !this.checked;
    };
    document.getElementById('allcheck').onchange = function(){
        document.getElementById('col').disabled = this.checked;
        document.getElementById('colcheck').disabled = this.checked;
        document.getElementById('tbl').disabled = this.checked;
        document.getElementById('tblcheck').disabled = this.checked;
        document.getElementById('sch').disabled = this.checked;
        document.getElementById('schcheck').disabled = this.checked;
        document.getElementById('db').disabled = this.checked;
        document.getElementById('dbcheck').disabled = this.checked;
        document.getElementById('srv').disabled = this.checked;
        document.getElementById('srvcheck').disabled = this.checked;
        document.getElementById('fkcheck').disabled = this.checked;
        document.getElementById('jocheck').disabled = this.checked;
    }
    </script>
	<script type="text/javascript">
	var neo4jd3 = new Neo4jd3('#neo4jd3', {
    
    icons: {
        'Server': 'server',
        'Database': 'database',
        'Schema': 'gear',
        'Table': 'table',
        'Column': 'columns',
        'SP' : 'f288',
        'Function' : 'f1c9'
    },
    minCollision: 60,
    neo4jDataUrl: 'neodata.json',
    nodeRadius: 20,
    onNodeClick: function(node){
        window.alert("Hello");
    },
    highlight: 
    [
        {
            class: 'SP', 
        }
    ],
    // onNodeDoubleClick: function(node) {
    //     switch(node.id) {
    //         case '25':
    //             // Google
    //             window.open(node.properties.url, '_blank');
    //             break;
    //         default:
    //             var maxNodes = 100,
    //                 data = neo4jd3.randomD3Data(node, maxNodes);
    //             neo4jd3.updateWithD3Data(data);
    //             break;
    //     }
    // },
    zoomFit: true,
});
	</script>
</body>
</html>