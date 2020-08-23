<?php
require_once "lib/core/Constants.php";
require_once "lib/core/clsProperties.php";
require_once "lib/users/clsUsers.php";

class UriHandler {

	var $clsObj;
	var $skip_login_check_views = array(
		"cls_home_login",
		"cls_timeout",
		"cls_unauthorized",
		"cls_loginsdisabled",
		"cls_store_disabled",
                "cls_pagenotfound"
	);

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
                        $pageuri = join("/",$path_array);                                                 
			$currUser = getCurrUser();                        
			if (!$currUser && !in_array($cls_name, $this->skip_login_check_views)) {
				header("Location: ".DEF_SITEURL."timeout");
				exit;
			}
			if ($cls_name != "cls_loginsdisabled" && $currUser && $currUser->usertype == UserType::Dealer) {
				$dbProperties = new dbProperties();
				if ($dbProperties->getBoolean(Properties::DisableUserLogins)) {
					header("Location: ".DEF_SITEURL."loginsdisabled");
					exit;
				}
			}
                        // check if the current user has access to this page
                        /*
                         * $page = select * from it_pages where pageuri = $uri
                         * if (not found) display page not found
                         * select * from it_user_pages where user_id = $currUser->id and page_id = $page->id
                         * if (empty) header("Location: ".DEF_SITEURL."unauthorized");
                         * $_SESSION['pagecode'] = $page->pagecode;
                         */                      
                        if( !in_array($cls_name, $this->skip_login_check_views)){
                            $userpage = new clsUsers();
                            $pageexists = $userpage->pageExists($pageuri);                          
                            if(!$pageexists){
                                header("Location:".DEF_SITEURL."pagenotfound");exit;                                
                            }else{
                                $allowed = $userpage->isAuthorized($currUser->id, $pageexists->pagecode);                              
                                if(trim($allowed)==""){ 
                                    header("Location: ".DEF_SITEURL."unauthorized"); 
                                    exit;                           
                                }else{
                                    $_SESSION['pagecode'] = $pageexists->pagecode;
                                }
                            }
                        }
                        
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
