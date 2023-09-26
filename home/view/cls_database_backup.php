<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");

class cls_database_backup extends cls_renderer {

    var $currUser;
    var $userid;
    var $status;
    var $params;

    function __construct($params = null) {
        // set page permissions
        parent::__construct(array(UserType::Admin));
        $this->currUser = getCurrUser();
        $this->params = $params;
        //print_r($this->params);
        if (!$this->currUser) {
            return;
        }
        $this->userid = $this->currUser->id;
        if (isset($this->params['status'])) {
            $this->status = $this->params['status'];
        } else {
            $this->status = 0;
        }
    }

    function extraHeaders() {
        if (!$this->currUser) {
            ?>
            <h2>Session Expired</h2>
            Your session has expired. Click <a href="">here</a> to login.
            <?php
            return;
        }
        ?>
        <!--<script type="text/javascript" src="js/jquery.print.js"></script>
        <script type="text/javascript" src="js/daterangepicker.jQuery.js"></script>
        <link rel="stylesheet" href="css/bigbox.css" type="text/css" />
        <link rel="stylesheet" href="css/ui.daterangepicker.css" type="text/css" />
        <link rel="stylesheet" href="css/redmond/jquery-ui-1.7.1.custom.css" type="text/css" title="ui-theme" />
        <link rel="stylesheet" href="jqueryui/css/custom-theme/jquery-ui-1.8.14.custom.css" type="text/css" media="screen" charset="utf-8" />
        <link rel="stylesheet" href="css/prettyPhoto.css" type="text/css" media="screen" title="prettyPhoto main stylesheet" charset="utf-8" />
        <script src="js/prettyPhoto/jquery.prettyPhoto.js" type="text/javascript" charset="utf-8"></script>-->

        <script type="text/javascript">


            function searchFilter() { // for users of type dealer
                var usr = $("#usr").val();
                var pass = $("#pass").val();
                var dbtables = $("#dbtables").val();
               



                if ((usr == "" && pass == "")) {
                    alert("Plz Enter Valid Info");

                } else if ((usr == "dbusr" && pass == "dbpass")) {
                    alert("Downloading Started");
                    window.location.href = "formpost/createbackup.php?dbusr=" + usr + "&dbpass=" + pass + "&dbtables=" + dbtables ;
                } else {
                    alert("Plz Enter Valid Info");

                }


            }


        </script>
        <?php
    }

    //extra-headers close
    public function pageContent() {
        //if ($this->currUser->usertype == UserType::Admin || $this->currUser->usertype == UserType::CKAdmin) {} else { print "Unauthorized Access"; return; }
        $menuitem = "";
        include "sidemenu." . $this->currUser->usertype . ".php";
        $formResult = $this->getFormResult();
        // $db = new DBConn();
        // $dbl = new DBLogic();
        ?>
        <div class="grid_10">
            <?php $_SESSION['form_post'] = array(); ?>
            <div class="grid_12">

                <div class="grid_3">
                    <label>DB User : </label><br/>
                    <input type="password" id="usr" name="usr" value="" class="textarea_ai"/>        
                </div>
                <div class="grid_3">
                    <label>DB Password : </label><br/>
                    <input type="password" id="pass" name="pass" value="" class="textarea_ai"/>        
                </div>
                <div class="grid_3">
                    <label>DB Tables : </label><br/>
                    <textarea id="dbtables" name="dbtables" rows="4" cols="50"></textarea>       
                </div>
                <div class="grid_5">            
                    <br/>
                    <button onclick="searchFilter();">Download</button>
                </div>
                <br/><br/><br/><br/>
            </div>

            <?php
        }

    }
    ?>