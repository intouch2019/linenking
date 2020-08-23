<?php

abstract class dbobject {
	var $conn=false;
	var $commit=true;
	var $fh=false;

	function log($msg) {
		//error_log($msg."\n", 3, "/var/www/ck/error.log");
	}

	public function __construct($commit=true) {
		$this->commit=$commit;
	}

	function getConnection() {
		if (!$this->conn) {
			$this->conn =  new mysqli(DB_SERVER,DB_USR,DB_PWD,DB_NME);
                    //$this->conn =  new mysqli(localhost,it-admin,demo1,ck_db);
		}
		return $this->conn;
	}

	function closeConnection() {
		if ($this->conn) {
			$this->conn->close();
		}
		$this->conn=false;
	}

	function execInsert($sql) {
		$this->log("Insert:$sql");
		if ($this->commit) {
			$this->getConnection()->query($sql);
			return $this->getConnection()->insert_id;
		} else { return -1; }
	}

	function execUpdate($sql) {
		$this->log("Update:$sql");
		if ($this->commit) {
			$this->getConnection()->query($sql);
			return $this->conn->affected_rows;
		} else { return 0; }
	}

	function execQuery($sql) {
		$this->log("execQuery:$sql");
		return $this->getConnection()->query($sql);
	}

	function fetchObject($query) {
//print "$query<br />";
		$this->log("fetchObject:$query");
		try {
		$obj=null;
		$result = $this->getConnection()->query($query);
		if ($result) {
			$obj = $result->fetch_object();
			$result->close();
		} else {
                    return NULL;
                }
		return $obj;
		} catch(Exception $xcp) {
		}
	}

	function fetchObjectArray($query) {
		$this->log("fetchObjectArray:$query");
		try {
		$result = $this->getConnection()->query($query);
		$arr = false;
		if ($result) {
		$arr = array();
		while ($obj = $result->fetch_object())
			$arr[] = $obj;
		$result->close();
		} else {
                    print "error=".$this->getConnection()->error;
                }
		return $arr;
		} catch(Exception $xcp) {
                    print_r($xcp);
		}
	}

	function safe($str) {
	    if ($str === false) { return false; }
	    // Stripslashes
	    if (get_magic_quotes_gpc()) {
	        $str = stripslashes($str);
	    }
	    // always quote even for numbers
	    return "'" . $this->getConnection()->real_escape_string($str) . "'";
	}

	function __destruct() {
		$this->closeConnection();
	}
}

?>
