<?php
require_once "../../it_config.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once "lib/db/DBConn.php";

extract($_POST);


$barcode=false;
if (isset($_POST['barcode'])){
	$barcode = $_POST['barcode'];
}

try{
    $db = new DBConn();
    
    $barcode = $db->safe($barcode);
    
    $query = "SELECT convert(d.lineno, unsigned int) dlineno, convert(d.rackno, unsigned int) drackno, i.barcode,"
            . "i.design_no, i.MRP as mrp, ctg.id as ctgid, ctg.name as category,br.name as brand,pt.name as prodtype,"
            . " i.style_id, i.size_id, ctg.vat_id, ctg.cst_id from it_ck_designs d, it_items i, it_categories ctg,it_brands br,"
            . " it_fabric_types ft, it_materials mt,it_prod_types pt where i.barcode=$barcode and i.ctg_id=ctg.id and i.brand_id=br.id and"
            . " i.prod_type_id=pt.id and i.material_id=mt.id and i.fabric_type_id=ft.id and i.design_no=d.design_no and i.ctg_id=d.ctg_id"
            . " group by i.ctg_id, i.design_no order by d.lineno, d.rackno";

    $obj_barcode = $db->fetchObject($query);

    if(isset($obj_barcode) && $obj_barcode != NULL){
        $it = array();
        foreach($obj_barcode as $key => $value){
            $it[$key] = $value;
        }
        $query = "select cs.id, sz.id as size_id, sz.name,cs.sequence from it_ck_sizes cs, it_sizes sz where cs.size_id = sz.id and cs.ctg_id = $obj_barcode->ctgid order by cs.sequence";
        $obj_sizes = $db->fetchObjectArray($query);
        
        $it["size_info"] = $obj_sizes;
        
        $query = "select cs.id, st.id  as style_id, st.name,cs.sequence from it_ck_styles cs, it_styles st where cs.style_id = st.id and cs.ctg_id = $obj_barcode->ctgid order by cs.sequence";
        $obj_styles = $db->fetchObjectArray($query);

        $it["style_info"] = $obj_styles;
    
    if(isset($it) && $it != NULL){
        print "0::".json_encode($it);
        return;
    }else{
        print "1::Barcode not found";
        return;
    }
    }else{
        print "1::Barcode not found";
        return;
    }
    
}catch(Exception $ex){
    print "1::Error-".$ex->getMessage();
}