<!DOCTYPE html>
<html>

<head>
    <?php
    require_once __DIR__.'/../app/hihi.php';
    // require_once __DIR__.'\hihi.php'; 
    require_once __DIR__.'\SP_vis.php'; 
    use Dependency\Components\SP_vis;

    $databaseConfig = require config_path('database.php');

    $neo4jConfig = $databaseConfig['connections']['neo4j']['sp'];
    $neo = createNeo4jConnection($neo4jConfig['username'], $neo4jConfig['password'], $neo4jConfig['host'], $neo4jConfig['port']);

    if (isset($_POST['btn-gen'])) {
        $hops = $_POST['hops'];
        $show = $_POST['show'];
        $sp_select = $_POST['sp_select'];
        if (isset($_POST['show'])) {
            if ($sp_select == 'All') {
            switch ($show) {
                case 'All':
                    $resnode = $neo->run('MATCH (n) RETURN n.name as name,n.surname as surname,n.server as server, n.database as database, n.schema as schema, n.PK as PK, n.column as column ,n.created as created, n.last_altered as last_altered, id(n) as id,labels(n) as label');
                    $resnode2='';
                    $resrel = $neo->run('MATCH (a)-[r]->(b) return id(r) as id,id(a) as start,id(b) as end, type(r) as type, r.FK as FK, r.From as from, r.Insert as insert, r.Join as join, r.Merge as merge, r.Truncate as truncate, r.Update as update, a.name as node_from, b.name as node_to');
                    break;
                case 'Execute':
                    $resnode = $neo->run('MATCH (a)-[r:Execute]->(b) RETURN DISTINCT a.name as name, a.surname as surname, a.server as server, a.database as database, a.schema as schema, a.PK as PK, a.column as column ,a.created as created, a.last_altered as last_altered, id(a) as id,labels(a) as label');
                    $resnode2 = $neo->run('MATCH (a:SP)-[r:Execute]->(b) RETURN DISTINCT b.name as name, b.surname as surname, b.server as server, b.database as database, b.schema as schema, b.PK as PK, b.column as column ,b.created as created, b.last_altered as last_altered, id(b) as id,labels(b) as label');
                    $resrel= $neo->run('MATCH (a:SP)-[r:Execute]->(b) RETURN id(r) as id,id(a) as start,id(b) as end, type(r) as type, r.FK as FK, r.From as from, r.Insert as insert, r.Join as join, r.Merge as merge, r.Truncate as truncate, r.Update as update, a.name as node_from, b.name as node_to');
                    break;
                case 'Use':
                    $resnode = $neo->run('MATCH (a)-[r:Use]->(b) RETURN DISTINCT a.name as name, a.surname as surname, a.server as server, a.database as database, a.schema as schema, a.PK as PK, a.column as column ,a.created as created, a.last_altered as last_altered, id(a) as id,labels(a) as label');
                    $resnode2 = $neo->run('MATCH (a)-[r:Use]->(b) RETURN DISTINCT b.name as name, b.surname as surname, b.server as server, b.database as database, b.schema as schema, b.PK as PK, b.column as column ,b.created as created, b.last_altered as last_altered, id(b) as id,labels(b) as label');
                    $resrel= $neo->run('MATCH (a)-[r:Use]->(b) RETURN id(r) as id,id(a) as start,id(b) as end, type(r) as type, r.FK as FK, r.From as from, r.Insert as insert, r.Join as join, r.Merge as merge, r.Truncate as truncate, r.Update as update, a.name as node_from, b.name as node_to');
                    break;
                
                default:
                    $resnode = $neo->run('MATCH (n) RETURN n.name as name,n.surname as surname,n.server as server, n.database as database, n.schema as schema, n.PK as PK, n.column as column ,n.created as created, n.last_altered as last_altered, id(n) as id,labels(n) as label');
                    $resrel = $neo->run('MATCH (a)-[r]->(b) return id(r) as id,id(a) as start,id(b) as end, type(r) as type, r.FK as FK, r.From as from, r.Insert as insert, r.Join as join, r.Merge as merge, r.Truncate as truncate, r.Update as update, a.name as node_from, b.name as node_to');
                    break;
            }
            }
            if ($sp_select !== 'All') {
                switch ($show) {
                case 'All':
                    $resnode = $neo->run('MATCH path = (a:SP {surname:{sp}})-[r*..'.$hops.']-(b)
                        WITH startnode(LAST(r)) as x
                        RETURN DISTINCT x.name as name,x.surname as surname,x.server as server, x.database as database, x.schema as schema, x.PK as PK, x.column as column ,x.created as created, x.last_altered as last_altered, id(x) as id,labels(x) as label',['sp' => $sp_select]);
                    $resnode2=$neo->run('MATCH path = (a:SP {surname: {sp} })-[r*..'.$hops.']-(b)
                        WITH endnode(LAST(r)) as y
                        RETURN DISTINCT y.name as name,y.surname as surname,y.server as server, y.database as database, y.schema as schema, y.PK as PK, y.column as column ,y.created as created, y.last_altered as last_altered, id(y) as id,labels(y) as label',['sp' => $sp_select]);
                    $resrel = $neo->run('MATCH path = (a:SP {surname: {sp} })-[r*..'.$hops.']-(b)
                        WITH LAST(r) as lr, startnode(LAST(r)) as x, endnode(LAST(r)) as y
                        RETURN id(lr) as id, id(x) as start,id(y) as end, type(lr) as type, lr.FK as FK, lr.From as from, lr.Insert as insert, lr.Join as join, lr.Merge as merge, lr.Truncate as truncate, lr.Update as update, x.name as node_from, y.name as node_to',['sp' => $sp_select]);
                    break;
                case 'Execute':
                    $resnode = $neo->run('MATCH path = (a:SP {surname:{sp}})-[r:Execute*..'.$hops.']-(b)
                        WITH startnode(LAST(r)) as x
                        RETURN DISTINCT x.name as name,x.surname as surname,x.server as server, x.database as database, x.schema as schema, x.PK as PK, x.column as column ,x.created as created, x.last_altered as last_altered, id(x) as id,labels(x) as label',['sp' => $sp_select]);
                    $resnode2=$neo->run('MATCH path = (a:SP {surname: {sp} })-[r:Execute*..'.$hops.']-(b)
                        WITH endnode(LAST(r)) as y
                        RETURN DISTINCT y.name as name,y.surname as surname,y.server as server, y.database as database, y.schema as schema, y.PK as PK, y.column as column ,y.created as created, y.last_altered as last_altered, id(y) as id,labels(y) as label',['sp' => $sp_select]);
                    $resrel = $neo->run('MATCH path = (a:SP {surname: {sp} })-[r:Execute*..'.$hops.']-(b)
                        WITH LAST(r) as lr, startnode(LAST(r)) as x, endnode(LAST(r)) as y
                        RETURN id(lr) as id, id(x) as start,id(y) as end, type(lr) as type, lr.FK as FK, lr.From as from, lr.Insert as insert, lr.Join as join, lr.Merge as merge, lr.Truncate as truncate, lr.Update as update, x.name as node_from, y.name as node_to',['sp' => $sp_select]);
                    break;
                case 'Use':
                    $resnode = $neo->run('MATCH path = (a:SP {surname:{sp}})-[r:Use*..'.$hops.']-(b)
                        WITH startnode(LAST(r)) as x
                        RETURN DISTINCT x.name as name,x.surname as surname,x.server as server, x.database as database, x.schema as schema, x.PK as PK, x.column as column ,x.created as created, x.last_altered as last_altered, id(x) as id,labels(x) as label',['sp' => $sp_select]);
                    $resnode2=$neo->run('MATCH path = (a:SP {surname: {sp} })-[r:Use*..'.$hops.']-(b)
                        WITH endnode(LAST(r)) as y
                        RETURN DISTINCT y.name as name,y.surname as surname,y.server as server, y.database as database, y.schema as schema, y.PK as PK, y.column as column ,y.created as created, y.last_altered as last_altered, id(y) as id,labels(y) as label',['sp' => $sp_select]);
                    $resrel = $neo->run('MATCH path = (a:SP {surname: {sp} })-[r:Use*..'.$hops.']-(b)
                        WITH LAST(r)  as lr, startnode(LAST(r)) as x, endnode(LAST(r)) as y
                        RETURN id(lr) as id, id(x) as start,id(y) as end, type(lr) as type, lr.FK as FK, lr.From as from, lr.Insert as insert, lr.Join as join, lr.Merge as merge, lr.Truncate as truncate, lr.Update as update, x.name as node_from, y.name as node_to',['sp' => $sp_select]);
                    break;
                
                default:
                    $resnode = $neo->run('MATCH path = (a:SP {surname:{sp}})-[r*..'.$hops.']-(b)
                        WITH startnode(LAST(r)) as x
                        RETURN DISTINCT x.name as name,x.surname as surname,x.server as server, x.database as database, x.schema as schema, x.PK as PK, x.column as column ,x.created as created, x.last_altered as last_altered, id(x) as id,labels(x) as label',['sp' => $sp_select]);
                    $resnode2=$neo->run('MATCH path = (a:SP {surname: {sp} })-[r*..'.$hops.']-(b)
                        WITH endnode(LAST(r)) as y
                        RETURN DISTINCT y.name as name,y.surname as surname,y.server as server, y.database as database, y.schema as schema, y.PK as PK, y.column as column ,y.created as created, y.last_altered as last_altered, id(y) as id,labels(y) as label',['sp' => $sp_select]);
                    $resrel = $neo->run('MATCH path = (a:SP {surname: {sp} })-[r*..'.$hops.']-(b)
                        WITH LAST(r) as lr, startnode(LAST(r)) as x, endnode(LAST(r)) as y
                        RETURN id(lr) as id, id(x) as start,id(y) as end, type(lr) as type, lr.FK as FK, lr.From as from, lr.Insert as insert, lr.Join as join, lr.Merge as merge, lr.Truncate as truncate, lr.Update as update, x.name as node_from, y.name as node_to',['sp' => $sp_select]);
                    break;
                }
                
            }
            
        }

        foreach ($resnode->getRecords() as $record) {
                $property = array(
                    "name"=> $record->value('name'),
                    "surname" => $record->value('surname'),
                    "server" => $record->value('server'),
                    "database" => $record->value('database'),
                    "schema" => $record->value('schema'),
                    "column" => $record->value('column'),
                    "PK" => $record->value('PK'),
                    "created" => $record->value('created'),
                    "last altered" => $record->value('last_altered')
                );
                $filtered = array_filter($property);

                $nodes[] = ["id"=>$record->value('id'),
                            "labels"=>$record->value('label'),
                            "properties"=>
                                $filtered
                                //taruh value lain di sini (jika ada)
                            ];
        }

        if ($resnode2 != '') {
            foreach ($resnode2->getRecords() as $record) {
                $property = array(
                    "name"=> $record->value('name'),
                    "surname" => $record->value('surname'),
                    "server" => $record->value('server'),
                    "database" => $record->value('database'),
                    "schema" => $record->value('schema'),
                    "column" => $record->value('column'),
                    "PK" => $record->value('PK'),
                    "created" => $record->value('created'),
                    "last altered" => $record->value('last_altered')
                );
                $filtered = array_filter($property);

                $nodes[] = ["id"=>$record->value('id'),
                            "labels"=>$record->value('label'),
                            "properties"=>
                                $filtered
                                //taruh value lain di sini (jika ada)
                                
                            ];
        }
        }

        foreach ($resrel->getRecords() as $record) {
            $fk = array(
                "FK" => $record->value('FK'),
                "From" => $record->value('from'),
                "Insert" => $record->value('insert'),
                "Join" => $record->value('join'),
                "Merge" => $record->value('merge'),
                "Truncate" => $record->value('truncate'),
                "Update" => $record->value('update'),
                "node_from" => $record->value('node_from'),
                "node_to" => $record->value('node_to')
            );
            $filtered_fk = array_filter($fk);
              $rel[] = ["id"=>$record->value('id'),
                        "type"=>$record->value('type'),
                        "startNode"=>$record->value('start'),
                        "endNode"=>$record->value('end'),
                        "properties"=>
                            $filtered_fk
                            
                        ];
            }
        if (isset($nodes) && isset($rel)) {
            $json = ["results" => array([
                    "data" => array([
                        "graph" => array(
                            "nodes" => $nodes,
                            "relationships" => $rel
                            )])])];
            $result = json_encode($json);
        }
        else{
           echo '<script type="text/javascript">';
           echo 'alert("Tidak ada relasi terkait")';
           echo '</script>';
        }
        
    }
?>
        <title>Dependency Tool</title>
        <link rel="stylesheet" type="text/css" href="assets/css/neo4jd3.min.css">
        <link rel="stylesheet" type="text/css" href="assets/css/font-awesome.min.css">
        <link rel="stylesheet" href="assets/css/material-kit.css?v=2.0.3">
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.1.0/css/all.css" integrity="sha384-lKuwvrZot6UHsBSfcMvOkWwlCMgc0TaWr+30HWe3a4ltaBwTZhyTEggF5tJv8tbt"
            crossorigin="anonymous">
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
        <link rel="icon" href="assets/img/server_Iq4_icon.ico">
</head>

<body>
    <div class="container-fluid" style='height: 100%'>
        <div class="row" style='height: 100%'>
            <div class="col-md-3" style="padding-top:15px; overflow-y: scroll;">
                <nav class="navbar navbar-expand-lg navbar-light bg-primary">
                    <div class="container">
                        <a class="navbar-brand" href="#">Filter</a>
                    </div>
                </nav>

                <form method="post">
                    <div class="form-group">
                        <label>Show</label>
                        <select class="form-control" name="show" id="show">
                            <option value="All" <?php if (isset($show) && $show=='All' ) { echo 'selected'; } ?> >All</option>
                            <option value="Execute" <?php if (isset($show) && $show=='Execute' ) { echo 'selected'; } ?>>Execute</option>
                            <option value="Use" <?php if (isset($show) && $show=='Use' ) { echo 'selected'; } ?>>Use</option>

                        </select>
                    </div>
                    <br>
                    <div class="form-group">
                        <label for="exampleFormControlSelect1">Stored Procedure</label>
                        <select class="form-control" name="sp_select">
                            <option value="All">Select Procedure (All) </option>
                            <?php 
                    foreach ($sp as $value) {
                        $opt = $value['sp_name'];
                        $val = $value['srv'].".".$value['db'].".".$value['sch'].".".$value['sp_name'];  
                ?>
                            <option value="<?= $val; ?>" <?php if (isset($sp_select) && $sp_select==$ val) { echo 'selected'; } ?>>
                                <?= $opt; ?>
                            </option>
                            <?php 
                    }
              ?>
                        </select>
                    </div>
                    <br>
                    <div class="form-group">
                        <label>Radius</label>
                        <select class="form-control" name="hops" id="hops">
                            <option value="1" <?php if (isset($hops) && $hops=='1' ) { echo 'selected'; } ?> >1</option>
                            <option value="2" <?php if (isset($hops) && $hops=='2' ) { echo 'selected'; } ?>>2</option>
                        </select>
                    </div>
                    <br>
                    <div>
                        <div>
                            <button type='submit' class='btn btn-primary btn-round pull-right' name='btn-gen' id="btn-gen">GENERATE</button>
                        </div>
                        <div>

                        </div>
                    </div>

                </form>
                <br>
                <br> Legends
                <ul class="fa-ul">
                    <li>
                        <i class="fa-li fa fa-server"></i> Server</li>
                    <li>
                        <i class="fa-li fa fa-database"></i> Database</li>
                    <li>
                        <i class="fa-li fa fa-gear"></i> Schema</li>
                    <li>
                        <i class="fa-li fa fa-table"></i> Table</li>
                    <li>
                        <i class="fa-li fab fa-product-hunt"></i> Stored Procedure</li>
                    <li>
                        <i class="fa-li fa fa-file-code"></i> Function</li>
                </ul>

            </div>


            <div class="col-md-9">
                <div id='neo4jd3'></div>
            </div>

            <script type="text/javascript">
                var neo4jd3 = new Neo4jd3('#neo4jd3', {
                    icons: {
                        'Server': 'server',
                        'Database': 'database',
                        'Schema': 'gear',
                        'Table': 'table',
                        'Column': 'columns',
                        'SP': 'f288',
                        'Function': 'f1c9'
                    },
                    minCollision: 60,
                    neo4jData: <?= $result; ?>,
                    nodeRadius: 20,
                    highlight: [{
                        class: 'SP',
                        property: 'surname',
                        value: '<?= $sp_select; ?>',
                    }],

                    zoomFit: true,

                });
            </script>
</body>

</html>