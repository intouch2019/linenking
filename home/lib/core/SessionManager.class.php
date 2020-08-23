<?php

// http://www.devshed.com/c/a/PHP/Storing-PHP-Sessions-in-a-Database

class SessionManager extends dbobject {

   var $life_time;

   function SessionManager() {

      // Read the maxlifetime setting from PHP
      $this->life_time = get_cfg_var("session.gc_maxlifetime");

      // Register this object as the session handler
      session_set_save_handler( 
        array( &$this, "open" ), 
        array( &$this, "close" ),
        array( &$this, "read" ),
        array( &$this, "write"),
        array( &$this, "destroy"),
        array( &$this, "gc" )
      );

   }

   function open( $save_path, $session_name ) {

      global $sess_save_path;

      $sess_save_path = $save_path;

      // establish the database connection - technically not needed since all dbobject calls do this
      $this->getConnection();

      return true;
   }

   function close() {

      // close the database connection
      $this->closeConnection();

      return true;
   }

   function read( $id ) {

      // Set empty result
      $data = '';

      // Fetch session data from the selected database

      $time = time();

      $newid = $this->safe($id);
      $sql = "SELECT `session_data` FROM `it_sessions` WHERE
`session_id` = $newid AND `expires` > $time";

      $obj = $this->fetchObject($sql);

      if($obj) {
        $data = $obj->session_data;
      }

      return $data;

   }

   function write( $id, $data ) {

      // Build query                
      $time = time() + $this->life_time;

      $newid = $this->safe($id);
      $newdata = $this->safe($data);

      $sql = "REPLACE `it_sessions`
(`session_id`,`session_data`,`expires`) VALUES($newid,
$newdata, $time)";

      $this->execQuery($sql);

      return TRUE;

   }

   function destroy( $id ) {

      // Build query
      $newid = mysql_real_escape_string($id);
      $sql = "DELETE FROM `it_sessions` WHERE `session_id` =
'$newid'";

      $this->execQuery($sql);

      return TRUE;

   }

   function gc() {

      // Garbage Collection

                       

      // Build DELETE query.  Delete all records who have passed
the expiration time
      $sql = 'DELETE FROM `it_sessions` WHERE `expires` <
UNIX_TIMESTAMP();';

      $this->execQuery($sql);

      // Always return TRUE
      return true;

   }

}

?>
