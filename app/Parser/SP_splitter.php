<?php 

namespace Dependency\Parser;

class SP_splitter {
	public function split($sql){
		$uncommented = trim( preg_replace( '@(([\'"]).*?[^\\\]\2)|((?:\#|--).*?$|/\*(?:[^/*]|/(?!\*)|\*(?!/)|(?R))*\*\/)\s*|(?<=;)\s+@ms', '$1', $sql ) );

    	$nobracket = str_replace(array('[',']'), '',$uncommented);

    	$rep = str_replace("PROCEDURE", "proc", $nobracket);
    	$rep2 = str_replace("FROM", "from", $rep);
    	$rep3 = str_replace("MERGE", "merge", $rep2);
    	$rep4 = str_replace("JOIN", "join", $rep3);
    	$rep5 = str_replace("TABLE", "table", $rep4);
    	$rep6 = str_replace("INTO", "into", $rep5);
    	$rep7 = str_replace("UPDATE", "update", $rep6);
    	$rep8 = str_replace("EXECUTE", "exec", $rep7);
        $rep9 = str_replace("EXEC", "exec", $rep8);
        $rep10 = str_replace("PROC", "proc", $rep9);

		$keywords = preg_split("/[,;(\s)]+/", $rep10);

    	return $keywords;
	}
}
?>