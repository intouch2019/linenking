<?php
require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once "session_check.php";

class cls_editcontactdetails extends cls_renderer {

    var $params;
    var $id;

    function __construct($params = null) {
//	parent::__construct(array(UserType::Admin, UserType::CKAdmin, UserType::Manager));//
        $this->params = $params;
        $this->currStore = getCurrUser();
        print_r($params['ids']);
//        if (isset($this->params['ids'])) {
//            $this->id = $this->params['ids'];
//            
//        } 
         if (isset($params['ids'])) {
            $this->id = $params['ids'];
        }
       
    }

    function extraHeaders() {
        ?>
        <script type="text/javascript" src="jqueryui/js/jquery-ui-1.7.1.custom.min.js"></script>
        <script type="text/javascript" src="js/daterangepicker.jQuery.js"></script>
        <link rel="stylesheet" href="css/ui.daterangepicker.css" type="text/css" />
        <link rel="stylesheet" href="css/redmond/jquery-ui-1.7.1.custom.css" type="text/css" title="ui-theme" />
        <link rel="stylesheet" href="js/chosen/chosen.css" />
        <script type="text/javascript">
        </script>
        <link rel="stylesheet" href="css/prettyPhoto.css" type="text/css" media="screen" title="prettyPhoto main stylesheet" charset="utf-8" />
        <script src="js/prettyPhoto/jquery.prettyPhoto.js" type="text/javascript" charset="utf-8"></script>
        <script type="text/javascript"></script>
        <?php
    }

    //extra-headers close

    public function pageContent() {
        $menuitem = "editcontactdetails";
        include "sidemenu." . $this->currStore->usertype . ".php";
        $formResult = $this->getFormResult();
        $write_htm = true;
        ?>


        <div class="grid_6">
            <?php
            $db = new DBConn();
            $store_id = getCurrUserId();
            ?>
            <div class="box">
                <h2>Company Details</h2><br>

                <div style="margin-top: 0px; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; position: static; overflow:auto; ">
                    <div class="grid_12">
                        
                        <form action="formpost/editcontactdetails.php" method="POST">
                         <?php    if(isset($this->id) && $this->id!==""){
                            $query=" select id,Name,designation,contactno,email from contactdetails where id=$this->id " ;
                         $result=$db->fetchObject($query) ; 
                         }?>
                             <?php if(isset($this->id)){ ?>
                            <div class="grid_2"> <label> ID:   </label> </div>
                            <input type="text" name="id" readonly value="<?php if(isset($result) && $result !==""){ echo $this->id; }else{echo "";}?>">
                            <br><br>
                            <?php }else{?> <input name="addnew" type="hidden" value="1" ><?php }?>
                            <div class="grid_2"> <label>  Name:   </label> </div>
                            <input type="text" name="Name" value="<?php if(isset($result) && $result !==""){echo $result->Name; }else{echo "";} ?>">
                            <br><br>

                            <div class="grid_2"><label>  Designation:   </label>  </div>
                            <input type="text" name="Designation" value="<?php if(isset($result) && $result !==""){echo $result->designation; }else{echo "";} ?>">
                            <br><br>

                            <div class="grid_2"><label>  Contact Number:   </label>  </div>
                            <input type="text" name="Contactno" value="<?php if(isset($result) && $result !==""){echo $result->contactno; }else{echo "";} ?>">
                            <br><br>
                            
                            <div class="grid_2"><label>  Email:   </label>  </div>
                            <input type="email" name="Email" value="<?php if (isset($result) && $result !== "") {
            echo $result->email;
        } else {
            echo "";
        } ?>">
                            <br><br>
                            <input type="submit" value="<?php if(isset($this->id)&& ($this->id!=="")){ echo "Save";}else{echo "Add";}?>">
                            <br><br>
                        </form>

                    </div>

                </div>
                <?php
            }

        }
        ?>