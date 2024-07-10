#!/usr/bin/php -q
<?php
include '/var/www/ck/it_config.php';
require_once "lib/db/DBConn.php";
require_once "lib/email/EmailHelper.php";

//$emailHelper = new EmailHelper();
$db = new DBConn();
$objs = $db->fetchObjectArray("select id,emailaddress,subject,body from it_emails where processed=0 order by id limit 20");
if(isset($objs) && !empty($objs)){
    $emailHelper = new EmailHelper();
  foreach ($objs as $obj) {
	$toArray = array($obj->emailaddress);
	$subject = $obj->subject;
	$body = $obj->body;
	$errormsg = $emailHelper->send($toArray, $subject, $body);
	$query = "update it_emails set processed=1, updatetime=now() ";
	if ($errormsg != 0) {
		$errormsg = $db->safe($errormsg);
		$query .= ", errormsg=$errormsg";
	}
	$query .= " where id=$obj->id";
	$db->execUpdate($query);
}  
}

$db->closeConnection();

