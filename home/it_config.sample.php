<?php
ini_set('include_path','/var/www/limelight/home/'.PATH_SEPARATOR.'/var/www/limelight/home/Classes/'.PATH_SEPARATOR.ini_get('include_path'));
define("DEF_SITEURL",    "http://limelight.intouchrewards.com/");
define("DB_SERVER",      "localhost");
define("DB_NME", "limelight_db");
define("DB_USR", "dbusr");
define("DB_PWD", "dbpass");
define("LOGFILE_ERROR", "/var/www/limelight/logs/limelight.error.log");

# log msg types
define("LOG_MSGTYPE_ERROR", 1);
define("LOG_MSGTYPE_WARNING", 2);
define("LOG_MSGTYPE_DEBUG", 3);
define("LOG_MSGTYPE_REPLY", 4);
define("LOG_MSGTYPE_TRIAL", 5);
define("LOG_MSGTYPE_INFO", 6);
define("LOG_MSGTYPE_EXCEPTION", 7);

define("DEF_PAGE_TITLE", "Limelight Corporate Portal");
define("DEF_PAGE_KEYWORDS", "IntouchRewards.com, CottonKing, Limelight");
define("DEF_PAGE_DESCRIPTION", "Limelight Corporate Portal");

define("DEF_DEBUG", true);

if (DEF_DEBUG) {
error_reporting(E_ALL);
ini_set('display_errors', '1');
}

date_default_timezone_set('Asia/Kolkata');

