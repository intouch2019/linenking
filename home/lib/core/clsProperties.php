<?php

require_once "lib/db/dbobject.php";
require_once "lib/logger/clsLogger.php";

class Properties {
const DisableUserLogins = "admin.user.disablelogins";
}

class dbProperties extends dbobject {

	public function getBoolean($prop_name) {
		$prop_name = $this->safe($prop_name);
		$obj = $this->fetchObject("select value from it_ck_properties where name = $prop_name");
		if (!$obj) { return false; }
		$value = intval(trim($obj->value));
		return $value ? true : false;
	}

	public function setBoolean($prop_name, $bool) {
		$value = $bool ? "1" : "0";
		$value = $this->safe($value);
		$prop_name = $this->safe($prop_name);
		$obj = $this->fetchObject("select id from it_ck_properties where name = $prop_name");
		if ($obj) {
			$this->execUpdate("update it_ck_properties set value=$value where id=$obj->id");
		} else {
			$this->execInsert("insert into it_ck_properties set name=$prop_name, value=$value");
		}
	}
}

