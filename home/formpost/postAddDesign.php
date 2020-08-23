<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/serverChanges/clsServerChanges.php";
//require_once "lib/thumbnail/ThumbLib.inc.php";

extract($_POST);
$errors=array();
$success=array();

function debugLog($msg) {
	$d = date("m/d/y H:i:s.u");
//	error_log($msg.":".$d."\n", 3, "/var/www/cottonking/logs/ck-debug.log");
}

//debugLog("1");

try {
//    validatePost();
    global $success, $errors, $_SESSION;
    $_SESSION['form_post']=$_POST;
    extract($_POST);
    $db = new DBConn();
    $serverCh = new clsServerChanges();
    $design_no=trim($design_no);
    $category=trim($category);

    $_SESSION['form_design_no']=$design_no;
    if (!$design_no) { $errors['design_no']='Please specify an design number'; }

    if ((($_FILES["image"]["type"] == "image/gif")
        || ($_FILES["image"]["type"] == "image/jpeg")
        || ($_FILES["image"]["type"] == "image/png"))
        && ($_FILES["image"]["size"] < 512400)) {
        $image_name=($_FILES['image']['name']);
    }
    else
        $errors['image']='Please enter an image file(gif/jpeg/png) and enter within 500kb size';

    if ($_FILES["image"]["error"] != "0") {
        if ($_FILES["image"]["error"]==4)
            $errors['image']='Please enter an image file to upload';
        else
            $errors['image'] = "Image error : ".$_FILES["image"]["error"];
    }

    if (count($errors) == 0) {
        $extndiv = explode("/",$_FILES["image"]["type"]);
        $extn = $extndiv[1];
        $image="$category.$design_no.$extn";
        $design_no=$db->safe(trim($design_no));
        $category=$category;
//debugLog("2");
        $sqldes="select * from it_ck_designs where design_no=$design_no and ctg_id=$category";
        $design_exist=$db->fetchObject($sqldes);        
//debugLog("3");
        if ($design_exist) {
                $options = array('jpegQuality' => 75);
                //$thumb = PhpThumbFactory::create($_FILES['image']['tmp_name'],$options);
                //$thumb->resize(170, 230);
            if(move_uploaded_file($_FILES['image']['tmp_name'], "../images/stock/$image")) {
//debugLog("4");
                //$thumb->save("../images/stock/s.$image");
                $image = $db->safe($image);
                $extn = $db->safe($extn);
                $nameins="update it_ck_designs set image=$image, active=1, extension=$extn, updatetime=now() where design_no=$design_no and ctg_id=$category";
                $db->execUpdate($nameins);
                //update the designs all mrp also
                $iupqry = "update it_items set is_design_mrp_active = 1 where design_no=$design_no and ctg_id=$category ";
                $db->execUpdate($iupqry);
//debugLog("5");
/* No need to update serverChanges
                $obj = $db->fetchObject("select * from it_ck_designs where design_no=$design_no and ctg_id=$category");
                $server_ch = "[".json_encode($obj)."]";            
                $ser_type = changeType::ck_designs;
                $serverCh->insert($ser_type, $server_ch);
*/
                $success = "the image has been updated for design : $design_no in category : $category";
            }
        }else {
                $error['design']= "Sorry, there was a problem uploading your file.";
            }
    }

} catch (Exception $xcp) {
    $clsLogger = new clsLogger();
    $clsLogger->logError("Failed to add design:".$xcp->getMessage());
    $errors['status']="There was a problem processing your request. Please try again later";
}
if (count($errors) > 0) {
    $_SESSION['form_errors'] = $errors;
}
else
{
    $_SESSION['form_success'] = $success;
}

header("Location: ".DEF_SITEURL."admin/adddesign/ctg_id=$category");
exit;

?>
