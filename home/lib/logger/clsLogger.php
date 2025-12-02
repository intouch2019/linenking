<?php

require_once "lib/db/dbobject.php";

class clsLogger extends dbobject {

	public function logError($msg, $incomingid=false) {
		$msg = $this->safe($msg);
		$query = "insert into it_logs set msgtype=".LOG_MSGTYPE_ERROR.", message=$msg";
		if ($incomingid) {
			$query .= ", incomingid=$incomingid";
		}
		$this->execInsert($query);
		$this->closeConnection();
	}

	public function logException($xcp, $incomingid=false) {
		$msg = $xcp->getMessage();
		$query = "insert into it_logs set msgtype=".LOG_MSGTYPE_EXCEPTION.", message=$msg";
		if ($incomingid) {
			$query .= ", incomingid=$incomingid";
		}
		$this->execInsert($query);
		$this->closeConnection();
	}

	public function logInfo($msg, $incomingid=false,$pg_name=false,$ipaddr=false) {
		$msg = $this->safe($msg);
		$query = "insert into it_logs set msgtype=".LOG_MSGTYPE_INFO.", message=$msg";
		if ($incomingid) {
			$query .= ", incomingid=$incomingid";
		}
                if($pg_name){
                    $pg_name_db = $this->safe($pg_name);
                    $query .= " , pg_name = $pg_name_db";
                }
                if($ipaddr){
                    $ipaddr_db = $this->safe($ipaddr);
                    $query .= " , ipaddr = $ipaddr_db";
                }
                //error_log("\nquery: $query",3,"tmp.txt");
		$this->execInsert($query);
		$this->closeConnection();
	}
         public function logInfo1($msg, $incomingid=false,$pg_name=false,$ipaddr=false) {
		$msg = $this->safe($msg);
		$query = "insert into it_arorder_logs set msgtype=".LOG_MSGTYPE_INFO.", message=$msg";
		if ($incomingid) {
			$query .= ", incomingid=$incomingid";
		}
                if($pg_name){
                    $pg_name_db = $this->safe($pg_name);
                    $query .= " , pg_name = $pg_name_db";
                }
                if($ipaddr){
                    $ipaddr_db = $this->safe($ipaddr);
                    $query .= " , ipaddr = $ipaddr_db";
                }
                //error_log("\nquery: $query",3,"tmp.txt");
		$this->execInsert($query);
		$this->closeConnection();
	}

	public function logReply($incomingid, $msg) {
		$msg = $this->safe($msg);
		$query = "insert into it_logs set incomingid=$incomingid, msgtype=".LOG_MSGTYPE_REPLY.", message=$msg";
		$this->execInsert($query);
		$this->closeConnection();
	}

	public function logTrial($incomingid, $msg) {
		$msg = $this->safe($msg);
		$query = "insert into it_logs set incomingid=$incomingid, msgtype=".LOG_MSGTYPE_TRIAL.", message=$msg";
		$this->execInsert($query);
		$this->closeConnection();
	}
        
        public function it_codes_logInfo($msg, $incomingid=false,$pg_name=false,$ipaddr=false) {
		$msg = $this->safe($msg);
		$query = "insert into it_codes_logs set msgtype=".LOG_MSGTYPE_INFO.", message=$msg";
		if ($incomingid) {
			$query .= ", incomingid=$incomingid";
		}
                if($pg_name){
                    $pg_name_db = $this->safe($pg_name);
                    $query .= " , pg_name = $pg_name_db";
                }
                if($ipaddr){
                    $ipaddr_db = $this->safe($ipaddr);
                    $query .= " , ipaddr = $ipaddr_db";
                }
		$this->execInsert($query);
		$this->closeConnection();
	}
}

?>
