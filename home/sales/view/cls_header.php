<?php
class cls_header {

	public function __construct() {
	}

	public function pageHeader($renderObj) {
		$currStore = getCurrStore();
		$store_name="";
		if ($currStore) { $store_name="[$currStore->code] $currStore->store_name"; }
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
<link href="templates/integral/default.css" rel="stylesheet" type="text/css" />
<link href="<?php echo DEF_SITEURL; ?>css/default.css" rel="stylesheet" type="text/css" />
<?php echo $renderObj->extraHeaders(); ?>
</head>
<body>
<div id="header">
	<div id="menuround"></div>
	<ul id="menu">
<?php if (isset($_SESSION['corpStore'])) {
	$corpStore = $_SESSION['corpStore'];
?>
		<li><a href="corporate/goback" accesskey="3" title=""><?php echo $corpStore->store_name; ?></a></li>
<?php } ?>
		<li><a href="#" accesskey="5" title=""><?php echo $store_name; ?></a></li>
	</ul>
</div>
<div id="content">
	<div id="colOne">
		<div id="logo">
			<h1><a href="#"><img src="images/logo-sm6.jpg" /></a></h1>
		</div>
<?php
	}
}
?>
