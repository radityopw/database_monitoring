<?php 

namespace Dependency\Parser;

class SP_parser {

	public function from($lexer){
		$key_from = array_keys($lexer, "from");
			if (!empty($key_from)) {		
				$from = array();
				foreach ($key_from as $key) {
  					$tbl_name = $lexer[$key+1];
  					$valid = $tbl_name !== 'openquery' && $tbl_name !== 'select' && $tbl_name !== 'set' && (strncmp($tbl_name, '@', 1) === 1 && strpos($tbl_name, '.') !== false || strpos($tbl_name, 'sys') !== false);
  						if ($valid) {
  							$from[] = $tbl_name; 
  						}
				}
						return array_unique($from);
			}
	}

	public function join($lexer){
		$key_join = array_keys($lexer, "join");
			if (!empty($key_join)) {
				$join = array();
				foreach ($key_join as $key) {
  					$tbl_name = $lexer[$key+1];
  					$valid = $tbl_name !== 'openquery' && $tbl_name !== 'select' && $tbl_name !== 'set'  && (strncmp($tbl_name, '@', 1) === 1 && strpos($tbl_name, '.') !== false || strpos($tbl_name, 'sys') !== false);

  						if ($tbl_name !== 'openquery') {
  							$join[] = $tbl_name;
  						}
					}
						return array_unique($join);
			}
	}

	public function merge($lexer){
		$key_merge = array_keys($lexer, "merge");
			if (!empty($key_merge)) {
				$merge = array();
				foreach ($key_merge as $key) {
  					$tbl_name = $lexer[$key+1];
  					$valid = $tbl_name !== 'openquery' && $tbl_name !== 'select' && $tbl_name !== 'set' && (strncmp($tbl_name, '@', 1) === 1 && strpos($tbl_name, '.') !== false || strpos($tbl_name, 'sys') !== false);

  						if ($valid) {
  							$merge[] = $tbl_name;
  						}		
				}
				return array_unique($merge);
			}
	}

	public function truncate($lexer){
		$key_table = array_keys($lexer, "table");
			if (!empty($key_table)) {
				$table = array();
				foreach ($key_table as $key) {
  					$tbl_name = $lexer[$key+1];
  					$valid = $tbl_name !== 'openquery' && $tbl_name !== 'select' && $tbl_name !== 'set' && (strncmp($tbl_name, '@', 1) || strpos($tbl_name, 'sys') !== false);
  					if ($valid) {
  						$table[] = $tbl_name;
  					}
				}
				return array_unique($table);
			}
	}

	public function insert($lexer){
		$key_insert = array_keys($lexer, "into");
			if (!empty($key_insert)) {
				$into = array();
				foreach ($key_insert as $key) {
  					$tbl_name = $lexer[$key+1];
  					$valid = $tbl_name !== 'openquery' && $tbl_name !== 'select' && $tbl_name !== 'set' && (strncmp($tbl_name, '@', 1) === 1 && strpos($tbl_name, '.') !== false || strpos($tbl_name, 'sys') !== false);
  					if ($valid) {
  						$into[] = $tbl_name; 
  					}
				}
				return array_unique($into);
			}
	}

	public function update($lexer){
		$key_update = array_keys($lexer, "update");
			if (!empty($key_update)) {
				$update = array();
				foreach ($key_update as $key) {
  					$tbl_name = $lexer[$key+1];
  					$valid = $tbl_name !== 'openquery' && $tbl_name !== 'select' && $tbl_name !== 'set' && (strncmp($tbl_name, '@', 1) === 1 && strpos($tbl_name, '.') !== false || strpos($tbl_name, 'sys') !== false);
  					if ($valid) {
  						$update[] = $tbl_name;
  					}
				}
				return array_unique($update);
			}
	}

	public function exec($lexer){
		$key_exec = array_keys($lexer, "exec");
			if (!empty($key_exec)) {
				$exec = array();
				foreach ($key_exec as $key) {
  					$tbl_name = $lexer[$key+1];
  					$valid = $tbl_name !== 'openquery' && $tbl_name !== 'select' && $tbl_name !== 'set' && $tbl_name !== 'AS' && (strncmp($tbl_name, '@', 1) === 1 || strpos($tbl_name, 'sys') !== false);
  					if ($valid) {
  						$exec[] = $tbl_name;
  					}
				}
				return array_unique($exec);
			}
	}
}
?>