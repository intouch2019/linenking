<?php
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";
define("REALM", "Intouch-Orders-Processing");
// Digest and Basic authentication PHP implementation by Thomas Pike
// Comments on this implementation can be left at:
// http://www.xiven.com/blog.php?start=73&count=1

// As of 2005-09-12 I have explicitly released this code as public domain.  This means you can do
// anything you want with it - I have given up all rights to the work with no strings attached.
// You do not need to credit me if you use this code (though it is always welcomed).

// Usage:
// - Include this file in every page.
// - The following constant needs to be defined:
//     'REALM' - specifies the name of the realm of this login system.
// - Somewhere at top of every page, have a line like the following:
//     $auth = new authentication;
// - Create a login page with the following code in:
//     if(!$auth->loggedIn) {
//         $auth->authenticate();
//     } else {
//         echo '<p>Login successful</p>';
//       ....
//     }
// - A users table is required in a database with at least the following fields:
//     userid     INT PRIMARY        Unique user id
//     username   VARCHAR            User name
//     a1         CHAR(32)           Password hash
//     trust      INT                Indicates user trust (affected by auth method used)
// - Passwords should be stored in the database in the form:
//     a1 = md5($username.":".REALM.":".$password);
//   where REALM is the predefined realm name for this login system.
// - (OPTIONAL) Somewhere at the bottom of every page, include the following code:
//     $auth->authenticationInfo($contentMD5);
//   where $contentMD5 is the md5 checksum of the web page.

// Notes:
// - Code is not provided here for the creation of user accounts.  In particular, a secure
//   arrangement for allowing the user to choose their password in the first place would be
//   needed.  This could maybe be done using a true secure method such as SSL, or by some kind
//   of public key method (would probably need to use javascript for this).
// - Digest Authentication is not the world's answer to web security.  If you need a truly secure
//   system you should almost certainly be using SSL instead.  In particular, all data except for
//   the user's password is sent in plain text as usual.  Additionally, this implementation is
//   unavoidably vulnerable to certain "Man in the Middle" attacks as detailed in section 4.8 of
//   RFC 2617 - HTTP Authentication (http://www.ietf.org/rfc/rfc2617.txt).  Also, if Auth is used
//   instead of Auth-int, POST requests could potentially be modified en-route by a malicious
//   proxy.
// - ':' symbol is not allowed in user name to allow for compatibility with Basic authentication.

// Current browser issues:
// - Currently only Opera is known to support Auth-int.  IE and Mozilla only support Auth.
//   Mozilla bug: http://bugzilla.mozilla.org/show_bug.cgi?id=168942
// - Mozilla and IE completely ignore the Authentication-Info header (they neither reject the page
//   if the server sends an incorrect rspauth, nor do they use the nextnonce value for the next
//   request, resulting in a 401 with stale=true being sent after the 2 minute stale nonce grace
//   period has expired even if other pages have been accessed).
//   Mozilla bug: http://bugzilla.mozilla.org/show_bug.cgi?id=116177
// - IE doesn't resend the Opaque value for subsequent requests, therefore if it is not present, we
//   allow the request anyway.  I don't believe this should actually affect security in any way
//   since the Opaque value never changes, and if the response is valid then the client must have
//   the correct Opaque value.
// - If the URI has a querystring (ie. ?foo=bar) IE does not include it in the URI it sends in the
//   Authorization header.  This is a violation of RFC2617 section 3.2.2.5.  This implementation
//   works around this bug by being more leniant than the spec allows.  This functionality can be
//   disabled by setting the IECOMPAT constant to false.
// - WWW-Authenticate line specifies Digest before Basic since otherwise IE will use Basic.  Note
//   that this goes against the advice in RFC 2617: "Note that many browsers will only recognize
//   Basic and will require that it be the first auth-scheme presented."

// Current implementation issues:
// - Nonce count is currently ignored by this system.  This should be implemented as extra security
//   against replay attacks.  To do this, we need a method of "remembering" which nonce counts have
//   been used for the current nonce.  Maybe this could be stored in the database, but this would
//   result in extra queries.  Another alternative is to write the used nonce values in a file.
// - Error logging code for incorrect password not yet implemented

// Issues fixed since last release
// - FIXED: Auth-int support will almost certainly break when faced with multipart form data.
//   eg. <form enctype="multipart/form-data"> which is commonly used for uploading files via HTTP.
// - FIXED: 2004-01-10 Authentication fails when URI contains an apostrophe (')
// - FIXED: 2004-05-03 Basic Authentication broken
// - FIXED: 2004-05-03 Workaround for IE bug: URI in Authorization header does not include
//   querystring

define('HTTPLOC', 'http://cottonking.intouchrewards.com/');
define('OPAQUE', 'moo');
define('NONCEKEY', 'moo');//$_SERVER['UNIQUE_ID']);
define('IECOMPAT', true);

class authentication {
    var $loggedIn;
    var $codeId;
    var $codeInfo;
    var $trust;
    var $reducedTrust;
    var $authMethod;
    var $auth;
    var $a1;
    var $nonce;
    var $nextNonce;
    
    function authentication() {
        $db = new DBConn();
        
        $return = '';
        $this->loggedIn = false;
        $this->codeId = 0;
        $this->trust = 0;
        $this->reducedTrust = false;
//logDebug("headers=".print_r($headers,true));
//error_log("<<".print_r($_SERVER,true).">>\n", 3, "digest.log");
        if(isset($_SERVER['Authorization'])) {
            // Just in case they ever fix Apache to send the Authorization header on, the following code is included
            $headers['Authorization'] = $_SERVER['Authorization'];
        }
        if(function_exists('apache_request_headers')) {
            // We are running PHP as an Apache module, so we can get the Authorization header this way
            $headers = apache_request_headers();
        }
        if(isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
            // Basic authentication information can be retrieved from these server variables
            $username = $_SERVER['PHP_AUTH_USER'];
            $password = $_SERVER['PHP_AUTH_PW'];
        }
        $requestURI = stripslashes($_SERVER['REQUEST_URI']);
        if ((isset($headers['Authorization'])) && substr($headers['Authorization'],0,7) == 'Digest ') {
                // Digest authentication received
                // Get the Authorization header into a usable format
                $authtemp = explode(',', substr($headers['Authorization'],strpos($headers['Authorization'],' ')+1));
                $auth = array();
                foreach($authtemp as $key => $value) {
                    $value = trim($value);
                    if(strpos($value,'=') !== false) {
                        $lhs = substr($value,0,strpos($value,'='));
                        $rhs = substr($value,strpos($value,'=')+1);
                        if(substr($rhs,0,1) == '"' && substr($rhs,-1,1) == '"') {
                            $rhs = substr($rhs,1,-1);
                        }
                        $auth[$lhs] = $rhs;
                    }
                }
                $this->auth = $auth;
                if(strpos($auth['uri'], '?') === false && strpos($requestURI, '?') !== false && IECOMPAT) {
                    // Another joyful IE bug: URI in Authorization header does not include querystring
                    $requestURI = substr($requestURI, 0, strpos($requestURI, '?'));
                }
                if($requestURI == $auth['uri']) {
                    // Request URI matches URI in Authorization header
                    $this->authMethod = 'digest';
                    $nonceOptions = $this->nonceOptions();
                    $this->nextNonce = $nonceOptions[0];
                    if($auth['nonce'] == $nonceOptions[0]) {
                        // Up-to-date nonce received
                        $this->nonce = $nonceOptions[0];
                        $stale = false;
                    } elseif ($auth['nonce'] == $nonceOptions[1]) {
                        // 1-minute old nonce received - allowed
                        $this->nonce = $nonceOptions[1];
                        $stale = false;
                    } elseif ($auth['nonce'] == $nonceOptions[2]) {
                        // 2-minute old nonce received - allowed
                        $this->nonce = $nonceOptions[2];
                        $stale = false;
                    } else {
                        // Stale nonce received (probably more than 2 minutes old)
                        $this->nonce = $auth['nonce'];
                        $stale = true;
                    }
                    $query = "SELECT id as userid, a1hash as a1, trust, code, store_name FROM it_codes WHERE username = '".sqlEscape($auth['username'])."'";
                    $rdUser = $db->fetchObject($query);
//	error_log(print_r($rdUser,true), 3, "digest.log");
                    if($rdUser) {
                        // Username valid - determine password validity
                        $this->a1 = $rdUser->a1; // md5($auth['username'].":".REALM.":".$rdUser->a1);
                        $a2unhashed = $_SERVER['REQUEST_METHOD'].":".$requestURI;
                        if($auth['qop'] == 'auth-int') {
                            $body = '';
                            foreach($_POST as $key => $value) {
                                if($body != '') $body .= '&';
                                $body .= rawurlencode(sqlUnescape($key)) . '=' . rawurlencode(sqlUnescape($value));
                            }
                            if(isset($GLOBALS["HTTP_RAW_POST_DATA"])) {
                                // In PHP < 4.3 get raw POST data from this variable
                                $body = $GLOBALS["HTTP_RAW_POST_DATA"];
                            }
                            if(version_compare(phpversion(), '4.3.0', '>=')) {
                                if($lines = @file('php://input')) {
                                    // In PHP >= 4.3 get raw POST data from this file
                                    $body = implode("\n", $lines);
                                }
                            }
                            $a2unhashed .= ':'.md5($body);
                            $trust = 3;
                        } else {
                            $trust = 2;
                        }
                        $a2 = md5($a2unhashed);
                        $combined = $this->a1.':'.
                                    $this->nonce.':'.
                                    $auth['nc'].':'.
                                    $auth['cnonce'].':'.
                                    $auth['qop'].':'.
                                    $a2;
                        $expectedResponse = md5($combined);
                        if(!isset($auth['opaque']) || $auth['opaque'] == md5(OPAQUE)) {
                            if($auth['response'] == $expectedResponse) {
                                // Password valid
                                if(!$stale) {
                                    // Everything is good!
                                    $this->loggedIn = true;
                                    $this->codeId = $rdUser->userid;
				    $this->codeInfo = $rdUser;
                                    $this->trust = min($rdUser->trust, $trust);
                                    if($this->trust < $rdUser->trust) {
                                        // Reduce trust if user has used less secure login than before (eg. auth rather than auth-int)
                                        $query = "UPDATE it_codes SET trust = ".$this->trust." WHERE id = ".$rdUser->userid;
                                        $db->query($query);
                                        $this->reducedTrust = true;
                                    }
                                } else {
                                    // Nonce is stale
                                    $wwwauth = 'WWW-Authenticate: Digest ';
                                    $wwwauth .= 'qop="'.$auth['qop'].'", ';
                                    $wwwauth .= 'algorithm=MD5, ';
                                    $wwwauth .= 'realm="'.REALM.'", ';
                                    $wwwauth .= 'domain="'.substr(HTTPLOC,0,-1).'", ';
                                    $wwwauth .= 'nonce="'.$nonceOptions[0].'", ';
                                    $wwwauth .= 'opaque="'.md5(OPAQUE).'", ';
                                    $wwwauth .= 'stale=true ';
                                    header($wwwauth);
                                    $stalepage = new page(NOAUTH);
                                    $stalepage->response('401 Unauthorized');
                                    $stalepage->title('Login stale');
                                    $stalepage->h1('Login stale');
                                    $stalepage->add('
                                    <h>Login stale</h>
                                    <p>Stale nonce value, please re-authenticate.</p>
                                    ');
                                    $stalepage->display();
                                    die();
                                }
                            } else {
                                // Password mismatch - log an error and allow retry
                            }
                        } else {
                            // Opaque doesn't match - bad request
                            $this->authenticateFailure('Opaque doesn\'t match ('.$auth['opaque'].' != '.md5(OPAQUE).')');
                        }
                    } else {
                        // Username not found - allow retry
//                        $this->authenticateFailure('Login failure');
                    }
                } else {
                    // Request URI doesn't match URI in Authorization header - bad request
                    $this->authenticateFailure('
                    Request URI doesn\'t match URI in Authorization header ('.$requestURI.' != '.$auth['uri'].')
                    ');
                }
        } else if (isset($username) && isset($password)) {
            // Basic authentication received
            $this->authMethod = 'basic';
            $query = "SELECT id as userid, trust, code, store_name FROM it_codes WHERE username = '".sqlEscape($username)."' ";
            $query .= "AND a1hash = '".md5($username.":".REALM.":".$password)."'";
            $rdUser =& $db->fetchObject($query);
            if($rdUser) {
                // Login successful
                $this->loggedIn = true;
		$this->codeInfo = $rdUser;
                $this->codeId = $rdUser->userid;
                $this->trust = min($rdUser->trust, 1);
                if($this->trust < $rdUser->trust) {
                    // Reduce trust if user has used less secure login than before (eg. basic rather than digest)
                    $query = "UPDATE it_codes SET trust = ".$this->trust." WHERE id = ".$rdUser->userid;
                    $db->query($query);
                    $this->reducedTrust = true;
                }
            }
        } else {
            // Unrecognised authentication type - bad request
            // $this->authenticateFailure();
        }
        return $return;
    }
    
    function authenticationInfo($contentMD5) {
        
        if($this->loggedIn && $this->authMethod == 'digest') {
            // Work out authorisation response
            $a2unhashed = ":".stripslashes($_SERVER['REQUEST_URI']);
            if($this->auth['qop'] == 'auth-int') {
                $a2unhashed .= ':'.$contentMD5;
            }
            $a2 = md5($a2unhashed);
            $combined = $this->a1.':'.
                        $this->nonce.':'.
                        $this->auth['nc'].':'.
                        $this->auth['cnonce'].':'.
                        $this->auth['qop'].':'.
                        $a2;
            
            // Send authentication info
            $wwwauth = 'Authentication-Info: ';
            if($this->nonce != $this->nextNonce) {
                $wwwauth .= 'nextnonce="'.$this->nextNonce.'", ';
            }
            $wwwauth .= 'qop='.$this->auth['qop'].', ';
            $wwwauth .= 'rspauth="'.md5($combined).'", ';
            $wwwauth .= 'cnonce="'.$this->auth['cnonce'].'", ';
            $wwwauth .= 'nc='.$this->auth['nc'].'';
            header($wwwauth);
        }
        
    }
    
    function authenticate() {
        
        // Force client to login
        $nonceOptions = $this->nonceOptions();
        $wwwauth = 'WWW-Authenticate: Digest ';
        $wwwauth .= 'qop="auth-int,auth", ';
        $wwwauth .= 'algorithm=MD5, ';
        $wwwauth .= 'realm="'.REALM.'", ';
        $wwwauth .= 'domain="'.substr(HTTPLOC,0,-1).'", ';
        $wwwauth .= 'nonce="'.$nonceOptions[0].'", ';
        $wwwauth .= 'opaque="'.md5(OPAQUE).'" ';
//        $wwwauth .= "\r\n".'WWW-Authenticate: Digest realm="Omoicore"';
        $wwwauth .= "\r\n";
	logDebug("not authenticated:".$wwwauth);
        header($wwwauth);
        header('HTTP/1.x 401 Unauthorized');
        ?>
        <h1>Login error</h1>
        <p>The user name and/or password you supplied was incorrect.</p>
        <?php
        die();
        
    }
    
    function authenticateFailure($details = '') {
        
        // Bad authorisation request received.
        header('HTTP/1.x 400 Bad Request');
        ?>
        <h1>Bad Request</h1>
        <p>
            Your browser attempted to authenticate with this server, but the request was invalid.  If this problem persists,
            please contact the support staff quoting the following text (copy and paste it if necessary):
        </p>
        <pre><samp><?php echo htmlspecialchars($_SERVER['HTTP_USER_AGENT'])?></samp></pre>
        <?php
        if($details != '') {
            echo '<p><samp>'.htmlspecialchars($details).'</samp></p>';
        }
        die();
        
    }
    
    function nonceOptions() {
        
        // Return acceptable nonce values
        $time = time();
	$user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : "Unknown";
        return array(
            md5(date('Y-m-d H:i',$time).':'.$user_agent.':'.NONCEKEY),
            md5(date('Y-m-d H:i',$time-60).':'.$user_agent.':'.NONCEKEY),
            md5(date('Y-m-d H:i',$time-120).':'.$user_agent.':'.NONCEKEY)
        );
    }
}

function logDebug($msg) {
	$dtime = date("[D] Y-m-d, G:i:s T", time());
	$logMsg=$dtime."|";
	$logMsg.=$_SERVER['REMOTE_ADDR']."|";
	$logMsg.=$_SERVER['PHP_SELF']."|";
	$logMsg.=$_SERVER['QUERY_STRING']."|";
	$logMsg.=$msg."\n";
	error_log($logMsg, 3, "digest.log");
}

function sqlEscape($text) {
    if(get_magic_quotes_gpc()) {
        return $text;
    } else {
        return addslashes($text);
    }
}

function sqlUnescape($text) {
    if(get_magic_quotes_gpc()) {
        return stripslashes($text);
    } else {
        return $text;
    }
}
?>
