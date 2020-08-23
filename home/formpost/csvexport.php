<?php
require_once "../../it_config.php";
require_once("session_check.php");
require_once "lib/db/DBConn.php";

extract ($_POST);
if (isset($form_name) && $form_name=='missingimage') {
    $queryadd = '';
    if ($category != '-1') $queryadd .= " and d.ctg_id=$category ";
    if ($status != '-1') $queryadd .= " and d.active=$status ";
    
    $query = "select d.design_no, d.active, c.name from it_ck_designs d, it_categories c where d.image is NULL and c.id=d.ctg_id $queryadd order by d.ctg_id";
    //echo $query;
    $db = new DBConn();
    $imgobjs = $db->fetchObjectArray($query);
    $trowa = array(); $tcella = array();
    $tcella[] .= "Category"; $tcella[].="Design No"; $tcella[] .= "Active";
    $trowa[] = $tcella;
    foreach ($imgobjs as $img) {
        $tcella = null;
        $tcella[] .= $img->name; $tcella[] .=$img->design_no; 
        if ($img->active=='1') {
            $tcella[] .= "active";
        } else {
            $tcella[] .= "inactive";
        }
        $trowa[] = $tcella;
    }
    $_SESSION['tdata'] = serialize($trowa);
}

$tdata = unserialize($_SESSION['tdata']);
//print_r ($tdata);
header('Content-type: text/csv');
if (isset($form_name) && $form_name=='missingimage') {
    header('Content-disposition: attachment;filename=MissingImages.csv');
} else 
    header('Content-disposition: attachment;filename=StoreSales.csv');

foreach ($tdata as $trow) {
    foreach ($trow as $tcell) {
        print "$tcell,";
    }
    print "\n";
    //echo "<br/>";
}

unset ($_SESSION['tdata']);
?>
