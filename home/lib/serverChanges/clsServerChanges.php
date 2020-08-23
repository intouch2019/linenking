<?php

require_once "lib/db/dbobject.php";

class clsServerChanges extends dbobject {

	

	public function insert($type, $data , $data_id = false) {
		$data = $this->safe($data);

		$query = "insert into it_server_changes set type=$type , changedata = $data ";		
		if ($data_id) { $query .= ", data_id = $data_id "; }
//		error_log("\nSER CH QRY:-".$query."\n",3,"../ajax/tmp.txt");
                $insertid = $this->execInsert($query);
	}
        
        public function save($type, $data, $storeid , $data_id = false ) {
		$data = $this->safe($data);

		$query = "insert into it_server_changes set type=$type , changedata = $data , store_id = $storeid ";
		if ($data_id) { $query .= ", data_id = $data_id "; }
		//error_log("\nSER CH QRY:-".$query."\n",3,"../../ajax/tmp.txt");
                $insertid = $this->execInsert($query);
	}

}



?>
