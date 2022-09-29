<?php
require_once ("lib/db/DBConn.php");
class cls_header {

	public function __construct() {
	}

	public function pageHeader($renderObj) {
		$currUser = getCurrUser();
                $viewcart="";
                $countdesigns="";
                $totqt="";
                $totprice="";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<base href="<?php echo $renderObj->baseUrl(); ?>"></base>
<meta content="index,follow" name="robots" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $renderObj->pageTitle(); ?></title>
<meta content="<?php echo $renderObj->pageKeywords(); ?>" name="keywords" />
<meta content="<?php echo $renderObj->pageDescription(); ?>" name="description" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<link rel="icon" href="favicon.ico" type="image/x-icon" />
<link rel="stylesheet" type="text/css" href="fluid960gs/css/reset.css" media="screen" />
<link rel="stylesheet" type="text/css" href="fluid960gs/css/text.css" media="screen" />
<link rel="stylesheet" type="text/css" href="fluid960gs/css/grid.css" media="screen" />
<link rel="stylesheet" type="text/css" href="fluid960gs/css/layout.css" media="screen" />
<link rel="stylesheet" type="text/css" href="css/newtheme.css" media="screen" />

<link rel="stylesheet" type="text/css" href="fluid960gs/css/nav.css" media="screen" />
<!--[if IE 6]><link rel="stylesheet" type="text/css" href="fluid960gs/css/ie6.css" media="screen" /><![endif]-->
<!--[if IE 7]><link rel="stylesheet" type="text/css" href="fluid960gs/css/ie.css" media="screen" /><![endif]-->
<script type="text/javascript" src="fluid960gs/js/jquery-1.4.2.min.js"></script>
<link rel="stylesheet" type="text/css" href="css/intouch.css" media="screen" />
<?php echo $renderObj->extraHeaders(); ?>
</head>
<body>
<div id="topcolor" class="container_12"></div>
<div id="limelight" class="container_12"></div>
<div id="content" class="container_12" style="position: inherit; background-color:#EEEEEE">
	<div class="grid_12" id="banner" style="">
	<div class="grid_8" style="font-size:32px;font-weight:bold;color:#eeeeee;text-align:right;margin:50px 0px 0px 0px;">Corporate Portal</div>
<?php if (getCurrUser()) { ?>
	<div class="grid_4 loggeduser"><?php echo getCurrUser()->code; ?> [ <a href="logout.php">Logout</a> ]</div>
<?php } ?>
        </div>
	<div class="grid_12">&nbsp;</div>
<div style="padding-left: 250px;">  <?php
                        $this->storeinfo = getCurrUser();
                        $dbProperties = new dbProperties();

                        $path_array = array();
                        $cls_name = "cls_" . join("_", $path_array);
                        if (($dbProperties->getBoolean(Properties::DisableUserLogins) && isset($this->storeinfo->usertype) && $this->storeinfo->usertype == 4)) {
//                            print_r($this->storeinfo);
                            ?><div class="grid_9">

                                <div class="error" style="font-size:1.6em; color:red">Your portal is disabled.

            <?php if (isset($this->storeinfo) && trim($this->storeinfo->disablelogins_reason) != "") {
                ?>
                                        <br>Reason is : <?php echo $this->storeinfo->disablelogins_reason; ?>

            <?php }
            ?> 
                                        <br>Please <a href="<?php echo DEF_SITEURL; ?>home/login">TRY AGAIN</a> later. Thank you for your patience.</div>

                                            </div><?php
                                        } elseif (isset($this->storeinfo->inactive) && $this->storeinfo->inactive == 1 && isset($this->storeinfo->usertype) && $this->storeinfo->usertype == 4) {
//                                            print_r($this->storeinfo);
                                            ?><div class="grid_9">
                                                <div class="error" style="font-size:1.6em; color:red">Your portal is disabled.
                                                     <?php if (isset($this->storeinfo) && trim($this->storeinfo->inactivating_reason) != "") {
                                                        ?>
                                                        <br>Reason is : <?php echo $this->storeinfo->inactivating_reason; ?>

                                                        <?php }
                                                        ?> 

                                                        <?php if (isset($this->storeinfo->paymentlink) && $this->storeinfo->paymentlink != "") { ?>
                                                            <br> To make payment click this link <a href="<?php echo $this->storeinfo->paymentlink; ?>" target="_blank" style="color:blue"><?php echo $this->storeinfo->paymentlink; ?> </a>

                                                            <?php } ?>
                                                        <br>Please <a href="<?php echo DEF_SITEURL; ?>home/login">TRY AGAIN</a> later. Thank you for your patience.</div>
                                                            </div><?php
                                            }
                                            ?>
                                                        </div>
                                                        <div class="grid_12">&nbsp;</div>

                               
<?php
	}
}