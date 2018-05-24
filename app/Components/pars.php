<?php
namespace PHPSQLParser;
require 'vendor/autoload.php';

class Pars {
	public function parse($query){
		$parser = new PHPSQLParser($query, true);
		$error = error_get_last();
		if($error == NULL){
			return $data = $parser->parsed;
		}
	}
}
$pars = new Pars();
?>