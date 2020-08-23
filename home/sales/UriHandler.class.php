<?php

class UriHandler {

	var $clsObj;

	function __construct($uri) {
		if (!$uri) {
			require_once "view/cls_home.php";
			$this->clsObj = new cls_home();
		} else {
			$paramStr=null;
			$uri = strtolower($uri);
			$uri = preg_replace('/^\/(.*)$/', "$1", $uri); // remove the leading / if any
			$uri = preg_replace('/^(.*)\/$/', "$1", $uri); // remove the trailing / if any
			$paths = explode('/',$uri);
			$path_array=array();
			$params = array();
			foreach($paths as $subpath) {
				if (strpos($subpath,'=') === false) $path_array[]=$subpath;
				else {
					list($name,$value)=explode('=',$subpath);
					$params[$name]=$value;
				}
			}
			$cls_name = "cls_".join("_",$path_array);
			require_once "view/$cls_name.php";
			$this->clsObj = new $cls_name($params);
		}
	}

	function displayContent() {
		require_once "view/cls_header.php";
		$clsHeader = new cls_header();
		$clsHeader->pageHeader($this->clsObj);
		$this->clsObj->pageContent();
		require_once "view/cls_footer.php";
		$clsFooter = new cls_footer();
		$clsFooter->pageFooter($this->clsObj);
	}
}

?>
