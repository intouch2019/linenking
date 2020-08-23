<?php

require_once "lib/db/dbobject.php";

class clsSMSServe extends dbobject {

        public function saveSMSreply($phoneno, $message, $storeid=false) {
                $phoneno = $this->safe($phoneno);
                $message = $this->safe($message);
                $query = "insert into it_smsserve set phoneno=$phoneno, message=$message";
                if ($storeid) { $query .= ", storeid=$storeid"; }
                return $this->execInsert($query);
        }
        
        public function getEntry($storeid=false) {
                // either get a new entry or an earlier processed entry that is atleast 3 minutes old
                $numrows = 0;
                $sClause="";
                if ($storeid) { $sClause = " storeid=$storeid and "; }
                else { $sClause = " storeid is null and "; }
                $query = "select * from it_smsserve where $sClause (status =0 or (status in (1,2) and updatetime < now() - INTERVAL 3 MINUTE)) order by id desc limit 1";
                $obj = $this->fetchObject($query);
                if ($obj) {
                        try {
                                $query = "update it_smsserve set status = status + 1,updatetime=now() where id = $obj->id";
                                $numrows = $this->execUpdate($query);
                        } catch (Exception $xcp) {
                                $logger->logException($xcp);
                                $numrows = 0;
                        }
                }
                if ($numrows > 0) {
                        $phoneno = $obj->phoneno;
                        if (strlen($phoneno) == 10) { $phoneno="91$phoneno"; }
                        $phoneno = "+$phoneno";
                        return $obj->id.",$phoneno,".$obj->message;
                } else {
                        return false;
                }
        }

        public function processed($entryid) {
                $query = "update it_smsserve set status = 5,updatetime=now() where id = $entryid";
                $numrows = $this->execUpdate($query);
        }
}

