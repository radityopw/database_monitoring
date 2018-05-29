<?php

namespace Dependency\Components;

use Dependency\Database\SqlConnectionMapper;

// $sqlsrv = createSQLServerConnection();

// $query = config('query.sqlserver.extract_database');
// dd($query);

// $result = tap($sqlsrv->prepare($query))->execute()->fetchAll(PDO::FETCH_OBJ);
// dd($result);
// $resultColl = collect($result)->pluck('name')->mapInto(SqlConnectionMapper::class);

$resultColl = collect("resits")->mapInto(SqlConnectionMapper::class);

return $resultColl;