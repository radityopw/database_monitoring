<!DOCTYPE html>
<html lang="en">
<?php
require_once __DIR__.'/../app/hihi.php';

require_once __DIR__.'/layout/user_header.php';

//Database Config

$databaseConfig = require __DIR__.'/../config/database.php';
$neo4jConfig = $databaseConfig['connections']['neo4j']['user'];
$neo4jGeneralConf = $databaseConfig['connections']['neo4j'];
$neo4jAllConfig = $databaseConfig['connections']['neo4j'];

//Connection
$neo4j = createNeo4jConnection($neo4jGeneralConf['username_read'], $neo4jGeneralConf['password_read'], $neo4jConfig['host'], $neo4jConfig['port']);

//Change Password
// $password = $neo4jAllConfig['password_read'];
// $neo4j->run("CALL dbms.changePassword('$password')");

//Running Stacks of Queries
$stack = tap($neo4j->stack())->push('MATCH (n) RETURN labels(n) as labels, n');
$stack->push('MATCH (x)-[y]-(z) RETURN y');
$results = $neo4j->runStack($stack);

$labelsCollection = collect();
$nodesCollection = collect();
$relsCollection = collect();
foreach($results as $result){
    foreach($result->getRecords() as $record) {
        $labels = $record->get('labels');
        $nodes = $record->get('n');
        $rel = $record->get('y');
        if ($labels) {
            $labelsCollection->push($labels);
        }
        if ($nodes) {
            $nodesCollection->push($nodes);
        }
        if ($rel) {
            $relsCollection->push($rel);
        }
    }
}
// dd($results);
// dd($nodesCollection);
$relsCollection = $relsCollection->map(function($value, $key){
    return $value->values();
})->flatMap(function($value){
    return array_keys($value);
})->unique();
$labelsCollection = $labelsCollection->flatten()->uniqueStrict();
if (isset($_POST['search_mode']) && $_POST['search_mode'] !== "") {
    if ($_POST['search_mode'] === 'filter') {
        require __DIR__.'/layout/user_filter.php';
    } elseif ($_POST['search_mode'] === 'cypher') {
        require __DIR__.'/layout/user_cypher.php';
    }
}
?>

    <body>
        <div class="row">
            <div class="col-xs-6 col-sm-4 col-md-3 col-lg-3 no-padding-right">
                <div class="main">
                    <div class="container">
                        <div class="tab-content tab-space">
                            <div class="form-group">
                                <label for="from_node_type" class="bmd-label-floating">Search Mode</label>
                                <select name="search_mode" id="search_mode" class="form-control">
                                    <option value="">-- Nothing Selected --</option>
                                    <option value="filter-form">Filter Mode</option>
                                    <option value="cypher-form">Cypher Mode</option>
                                </select>
                                <span class="bmd-help">Please select a node type!</span>
                            </div>
                            <div id="filter-form" style="display:none" class="form-block">
                                <div class="navbar bg-primary text-center display-block">
                                    <h4 class="fw-500">Search By Filter</h4>
                                </div>
                                <form enctype="multipart/form-data" action="./user_viewer.php" method="post">
                                    <input type="hidden" name="search_mode" value="filter" />
                                    <div class="form-group">
                                        <label for="from_node_type" class="bmd-label-floating">Node Source Type</label>
                                        <select name="from_node_type" id="from_node_type" class="form-control select-node-type" data-point="from">
                                            <option value="">-- Nothing Selected --</option>
                                            <?php
                                            foreach ($labelsCollection as $label) {
                                        ?>
                                                <option value="<?php echo $label; ?>">
                                                    <?php echo $label; ?>
                                                </option>
                                                <?php
                                            }
                                        ?>
                                        </select>
                                        <span class="bmd-help">Please select a node type!</span>
                                    </div>
                                    <?php
                                        foreach($labelsCollection as $label) {
                                    ?>
                                        <div class="form-group from-input" id="<?php echo 'from_'.$label; ?>" style="display:none">
                                            <label for="from_node" class="bmd-label-floating">Nodes</label>
                                            <select name="from_node[]" id="from_node[]" class="form-control">
                                                <option value="">-- Nothing Selected --</option>
                                                <?php
                                                foreach($nodesCollection->filter(function($value, $key) use($label){
                                                    return $value->hasLabel($label);
                                                }) as $node){
                                            ?>
                                                    <option value="<?php echo $node->identity();?>">
                                                        <?php echo $node->value('surname');?>
                                                    </option>
                                                    <?php
                                            }
                                            ?>
                                            </select>
                                            <span class="bmd-help">Please select a node!</span>
                                        </div>
                                        <?php
                                        }
                                    ?>
                                            <div class="form-group">
                                                <label for="relationships" class="bmd-label-floating">Relationship's Properties</label>
                                                <select multiple name="relationships" id="relationships" class="form-control selectpicker show-tick">
                                                    <option value="">-- Nothing Selected --</option>
                                                    <?php 
                                                    foreach ($relsCollection as $rel) {

                                                ?>
                                                    <option value="<?php echo $rel;?>">
                                                        <?php echo $rel;?>
                                                    </option>
                                                    <?php
                                                    }
                                                ?>
                                                </select>
                                                <span class="bmd-help">Please select a relationship!</span>
                                            </div>
                                            <div class="form-group">
                                                <label for="to_node_type" class="bmd-label-floating">Node Destination Type</label>
                                                <select name="to_node_type" id="to_node_type" class="form-control select-node-type" data-point="to">
                                                    <option value="">-- Nothing Selected --</option>
                                                    <?php
                                            foreach ($labelsCollection as $label) {
                                        ?>
                                                        <option value="<?php echo $label; ?>">
                                                            <?php echo $label; ?>
                                                        </option>
                                                        <?php
                                            }
                                        ?>
                                                </select>
                                                <span class="bmd-help">Please select a node type!</span>
                                            </div>
                                            <?php
                                            foreach($labelsCollection as $label) {
                                        ?>
                                                <div class="form-group to-input" id="<?php echo 'to_'.$label; ?>" style="display:none">
                                                    <label for="to_node" class="bmd-label-floating">Nodes</label>
                                                    <select name="to_node[]" id="to_node[]" class="form-control">
                                                        <option value="">-- Nothing Selected --</option>
                                                        <?php
                                                        foreach($nodesCollection->filter(function($value, $key) use($label){
                                                            return $value->hasLabel($label);
                                                        }) as $node){
                                                    ?>
                                                            <option value="<?php echo $node->identity();?>">
                                                                <?php echo $node->value('surname');?>
                                                            </option>
                                                            <?php
                                                        }
                                                    ?>
                                                    </select>
                                                    <span class="bmd-help">Please select a node!</span>
                                                </div>
                                                <?php 
                                                }
                                            ?>
                                                <div class="form-group">
                                                    <label for="hop_count" class="bmd-label-floating">Hop Count</label>
                                                    <input type="number" name="hop_count" min="1" max="3" step="1" value="1" />
                                                    <span class="bmd-help">Please select a node type!</span>
                                                </div>
                                                <button type="submit" class="btn btn-primary">Submit</button>
                                </form>
                            </div>
                            <div id="cypher-form" style="display:none" class="form-block">
                                <div class="navbar bg-primary text-center display-block">
                                    <h4 class="fw-500">Search By Query</h4>
                                </div>
                                <form enctype="multipart/form-data" action="./user_viewer.php" method="post">
                                    <input type="hidden" name="search_mode" value="filter" />
                                    <div class="form-group">
                                        <label for="query" class="bmd-label-floating">Cypher Query</label>
                                        <textarea class="form-control" id="query" name="query"></textarea>
                                        <span class="bmd-help">Input your cypher query here. (Read Only Mode)</span>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-6 col-sm-8 col-md-9 col-lg-9 no-padding">
                <div class="main">
                    <div class="container">
                        <div class="tab-content tab-space">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php
            require_once __DIR__.'/layout/user_footer.php';
            require_once __DIR__.'/layout/user_script.php';
        ?>
    </body>


</html>