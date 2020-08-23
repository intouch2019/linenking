<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
require_once 'lib/core/Constants.php';
require_once "lib/serverChanges/clsServerChanges.php";

try{
    $tables = array(
		"categories"=>"categories",
                "designs" => "ck_designs",
                "brands"=>"brands",
                "styles"=>"styles",
                "sizes"=>"sizes",
		"prod_types"=>"prod_types",				
		"materials"=>"materials",		
		"fabric_types"=>"fabric_types",
                "mfg_by"=>"mfg_by",
                //"items"=>"items"
		);
    
    
    $db = new DBConn();
    $serverCh = new clsServerChanges();
    
    
    foreach($tables as $key => $value){
        $table = "it_".$value;
        $query = "select * from $table ";
        $objs = $db->fetchObjectArray($query);    
        foreach($objs as $obj){
            $server_ch = "[".json_encode($obj)."]";
//            $ser_type = changeType::new_mfg_by;
           // $typename = "new_".$value;            
            //$ser_type = constant("changeType::$typename");
            $ser_type = constant("changeType::$value");
            $serverCh->insert($ser_type, $server_ch);
        }
    }
    // inserts all categories
//    $query = "select * from it_mfg_by ";
//    $mfgs = $db->fetchObjectArray($query);    
//    foreach($mfgs as $mfg){
//        $server_ch = json_encode($mfg);
//        $ser_type = changeType::new_mfg_by;
//        $serverCh->insert($ser_type, $server_ch);
//    }
    print "0::success";
}catch(Exception $xcp){
    print "1::error".$xcp->getMessage();
}
?>
