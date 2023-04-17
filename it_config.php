<?php
ini_set('include_path','/var/www/html/linenking/home/'.PATH_SEPARATOR.'/var/www/html/linenking/home/Classes/'.PATH_SEPARATOR.ini_get('include_path'));
//define("DEF_SITEURL",    "http://ec2-52-74-89-178.ap-southeast-1.compute.amazonaws.com/linenking/home/");
//define("DEF_SITEURL",    "http://ck-webserver-load-balancer-621919573.ap-southeast-1.elb.amazonaws.com/");
//define("DEF_SITEURL",    "http://ec2-52-74-89-178.ap-southeast-1.compute.amazonaws.com/");
define("DEF_SITEURL",    "http://linenking.intouchrewards.com/");
define("DB_SERVER","p:13.126.189.179");
//define("DB_SERVER",      "52.76.99.106");
define("DB_NME", "lk_db");
define("DB_USR", "lk_dbusr");
define("DB_PWD", "int0uch990");
define("LOGFILE_ERROR", "/var/www/html/linenking/logs/ll.error.log");
define("DEF_ISSUE_URL","http://tracker.intouchrewards.com/signon.php?");
define("DEF_ISSUE_AUTHKEY","0dc7a1eb4ad8d544dd64468d98241bcb");
define("DEF_ISSUE_PROJECTID","7");

# log msg types
define("LOG_MSGTYPE_ERROR", 1);
define("LOG_MSGTYPE_WARNING", 2);
define("LOG_MSGTYPE_DEBUG", 3);
define("LOG_MSGTYPE_REPLY", 4);
define("LOG_MSGTYPE_TRIAL", 5);
define("LOG_MSGTYPE_INFO", 6);
define("LOG_MSGTYPE_EXCEPTION", 7);

//define("DEF_PAGE_TITLE", "Limelight Corporate Portal");
define("DEF_PAGE_TITLE", "LinenKing Corporate Portal");
//define("DEF_PAGE_KEYWORDS", "IntouchRewards.com, CottonKing, Limelight");
define("DEF_PAGE_KEYWORDS", "IntouchRewards.com, LinenKing");
//define("DEF_PAGE_DESCRIPTION", "Limelight Corporate Portal");
define("DEF_PAGE_DESCRIPTION", "LinenKing Corporate Portal");

define("DEF_DEBUG", true);

define("DEF_WAREHOUSE_ID", 61);
define("DEF_SP_LIFESTYLE_ID",164);
define("DEF_50CK_WAREHOUSE_ID",174);
define("DEF_CK_WAREHOUSE_ID",84);
if (DEF_DEBUG) {
error_reporting(E_ALL);
ini_set('display_errors', '1');
}

date_default_timezone_set('Asia/Kolkata');

/*
if( !function_exists('apache_request_headers') ) {
///
function apache_request_headers() {
  $arh = array();
  $rx_http = '/\AHTTP_/';
  foreach($_SERVER as $key => $val) {
    if( preg_match($rx_http, $key) ) {
      $arh_key = preg_replace($rx_http, '', $key);
      $rx_matches = array();
      // do some nasty string manipulations to restore the original letter case
      // this should work in most cases
      $rx_matches = explode('_', $arh_key);
      if( count($rx_matches) > 0 and strlen($arh_key) > 2 ) {
        foreach($rx_matches as $ak_key => $ak_val) $rx_matches[$ak_key] = ucfirst($ak_val);
        $arh_key = implode('-', $rx_matches);
      }
      $arh[$arh_key] = $val;
    }
  }
  return( $arh );
}
///
}
*/