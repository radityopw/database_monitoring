<?php

include 'config.php';

//Get DMV queries (should be scheduled per minute)
	$sql = $con->prepare("
			SELECT t.[text]
			FROM sys.dm_exec_cached_plans AS p
			INNER JOIN sys.dm_exec_query_stats AS s
			   ON p.plan_handle = s.plan_handle
			CROSS APPLY sys.dm_exec_sql_text(p.plan_handle) AS t
			WHERE t.[text] LIKE N'%join%on%'
			AND t.[text] NOT LIKE N'%sys%'
			AND t.[text] NOT LIKE N'%create procedure%'
			AND t.[text] NOT LIKE N'%insert%'
			AND t.[text] NOT LIKE N'%openquery%'
			ORDER BY s.last_execution_time DESC;");
	$sql->execute();
//Fetching result DMV queries
		while($row=$sql->fetch(PDO::FETCH_ASSOC)){
			//insert per row result ke MySQL
			$mysql = $cons->prepare("
				INSERT INTO `text` (`text`) VALUES ('".$row['text']."')");
			$mysql->execute();

			echo $row['text']."<br> <br>";
		} ?>