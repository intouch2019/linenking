<?php

require_once("../../it_config.php");
require_once("session_check.php");
require_once "../lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/orders/clsOrders.php";
?>
<scrtip>
        <script type="text/javascript" src="jqueryui/js/jquery-ui-1.7.1.custom.min.js"></script>
        <script type="text/javascript" src="js/daterangepicker.jQuery.js"></script>
        <link rel="stylesheet" href="css/ui.daterangepicker.css" type="text/css" />
        <link rel="stylesheet" href="css/redmond/jquery-ui-1.7.1.custom.css" type="text/css" title="ui-theme" />


        <link rel="stylesheet" href="js/chosen/chosen.css" />
        <script src="js/chosen/chosen.jquery.js" type="text/javascript"></script>
        <link rel="stylesheet" href="css/prettyPhoto.css" type="text/css" media="screen" title="prettyPhoto main stylesheet" charset="utf-8" />
        <link rel="stylesheet" type="text/css" href="css/dark-glass/sidebar.css" />
        <script src="js/prettyPhoto/jquery.prettyPhoto.js" type="text/javascript" charset="utf-8"></script>
        <script type="text/javascript" src="js/sidebar/jquery.sidebar.js"></script>
</scrtip>    
<?php
$db = new DBConn();

//extract($_POST);
//print_r($_POST);
//exit();
$id=$_GET["id"];
//print_r($id);
//exit();
try{
    $db = new DBConn();
    $sQuery="select d.createdate,d.customer_name,d.customer_mobile_no,d.cust_old_bill_no,d.old_bill_date,"
            . "d.orignal_purchase_store_name,d.exchange_bill_no,d.exchange_bill_date,c.store_name as exchange_given_at_store,"
            . "d.store_address,d.store_manager_name,d.store_manager_mob_no,d.product,d.design_no,"
            . "d.size,d.style,d.mrp,d.barcode,d.defects,"
            . "d.remark_for_other_defects from defective_garment_form d inner join it_codes c on "
            . "d.exchange_given_at_store=c.id where d.id=$id";
    $obj=$db->fetchObject($sQuery);
//    print_r($obj);
//    exit();
//    <?php
    if($obj->remark_for_other_defects==null || $obj->remark_for_other_defects==""){
        $obj->remark_for_other_defects="No Remark";
    }
    if($obj->cust_old_bill_no==null || $obj->cust_old_bill_no==""){
        $obj->cust_old_bill_no="Not Available";
    }
    if($obj->orignal_purchase_store_name==null || $obj->orignal_purchase_store_name==""){
        $obj->orignal_purchase_store_name="Not Available";
    }
    if($obj->old_bill_date==null || $obj->old_bill_date==""){
        $obj->old_bill_date="Not Available";
    }

    $html = "<html>"
                . "<style>"
                    . "body {font-size:14px;}"
                    . ".thermal{width: 300px;display: block;align-items: center;text-align: justify;margin: auto;} @page { size: auto;  margin: 4mm; } "
                . "</style>"
                . "<body onload='window.print()'>"
                . "<div class='thermal'>
                    <img src='http://linenking.intouchrewards.com/home/images/LKLogo.png.png' width='100%' alt='LinenKing'><br><br>          
                    <h2>Customer Defect Return Form</h2><br>
                    <label>Date : $obj->createdate</label><br><br>
                    <label>Customer Name : $obj->customer_name</label><br><br>
                    <label>Customer Mob : $obj->customer_mobile_no</label><br><br>
                    <label>Bill(Old) : $obj->cust_old_bill_no</label><br><br>
                    <label>Bill Date(Old) : $obj->old_bill_date</label><br><br>
                    <label>Store Name(Orignal) :<br>$obj->orignal_purchase_store_name</label><br><br>
                    <label>Exchange Bill : $obj->exchange_bill_no</label><br><br>
                    <label>Exchange Bill Date : $obj->exchange_bill_date</label><br><br>
                    <label>Exchange Given Store :<br>$obj->exchange_given_at_store</label><br><br>
                    <label>Store Address :<br>$obj->store_address</label><br><br>
                    <label>Store Manager Name : $obj->store_manager_name</label><br><br>
                    <label>Store Manager Mob No : $obj->store_manager_mob_no</label><br><br>
                    <label>Product : $obj->product</label><br><br>
                    <label>Design No : $obj->design_no</label><br><br>
                    <label>Size : $obj->size</label><br><br>
                    <label>Style : $obj->style</label><br><br>
                    <label>Defective Garment MRP : $obj->mrp</label><br><br>
                    <label>Defective Garment Barcode : $obj->barcode</label><br><br>
                    <label>Defects : $obj->defects</label><br><br>
                    <label>Remark : $obj->remark_for_other_defects</label><br><br>
                    <div>------------------------------------------------------------</div>
                    <h3 style='margin-left:22px;'>PRODUCT COMPLAIN GUIDE</h3>    
                    <img src='http://linenking.intouchrewards.com/home/images/DGFormImg.jpeg' width='95%' height='400px' alt='Mark defect area on the diagram'>
                    <div>MARK THE DEFECT AREA ON THE DIAGRAM</div>  
                </div>"
            . "</html>";


    echo $html;
} catch (Exception $ex) {
    $clsLogger = new clsLogger();
    $clsLogger->logError("Failed to print receipt" . $ex->getMessage());
}

