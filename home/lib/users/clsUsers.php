<?php

require_once "lib/db/dbobject.php";
require_once "lib/logger/clsLogger.php";


class clsUsers extends dbobject {

	public function isAuthentic($username, $password) {
		$username = $this->safe($username);
		$password = $this->safe(md5($password));
		$query = "select * from it_codes where code=$username and password=$password";
                return $this->fetchObject($query);
	}
        
        public function isAuthorized($user_id, $pagecode) {
            $page = $this->fetchObject("select * from it_pages where pagecode = '$pagecode'");
            $allowed = $this->fetchObject("select * from it_user_pages where user_id = $user_id and page_id = $page->id ");
            if(!$allowed){ return false; }else{ return true; }
        }
        
        public function pageExists($pageuri){
            $pageuridb = $this->safe($pageuri);
            $obj = $this->fetchObject("select * from it_pages where pageuri = $pageuridb");
            $qry = "select * from it_pages where pageuri = $pageuridb ";         
            if($obj){ return $obj; }  
        }
}
?>
