<?php
require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once "session_check.php";


class cls_companydetails extends cls_renderer {

    var $params;
    var $id;

    function __construct($params = null) {
//	parent::__construct(array(UserType::Admin, UserType::CKAdmin, UserType::Manager));//
        $this->params = $params;
        $this->currStore = getCurrUser();

        if (isset($_SESSION['id'])) {
            $this->id = $_SESSION['id'];
        } else {
            $this->id = "1";
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
        <script type="text/javascript">
        <!--//--><![CDATA[//>
         function deleteEmployee(code)
            {
                var r = confirm("Are you sure to Deactivate Employeecode "+code+" ?");
                if (r == true) {
                    window.location = "formpost/deleteEmployees.php?id="+code;
                    exit;
                }else{ return }
                //window.location.reload();
            }


            function reload() {
                        var dtrange = $("#dateselect").val();
                        //var id = $("#cn").val();
                        //                alert("id");
                        $.ajax({
                            url: "savesession.php?name=account_dtrange&value=" + dtrange,
                            success: function (data) {
                                window.location.reload();
                            }
                        });
            }


            
            $(function(    ) {
                        $("#cn").change(function () {
                            var id = $("#cn").val();
                            //alert();

                            $.ajax({
                                url: "savesession.php?name=id&value=" + id,
                                success: function (data) {
                                    window.location.reload();

                                }
                            });

                        });
                        $("#cn1").change(function () {
                            var id = $("#cn1").val();
                            $.ajax({
                                url: "savesession.php?name=id&value=" + id,
                                success: function (data) {
                                    //alert(data);
                                    window.location.reload();
                                }
                            });
                        });
                        $("#cn2").change(function () {
                            var id = $("#cn2").val();
                            $.ajax({
                                url: "savesession.php?name=id&value=" + id,
                                success: function (data) {
                                    //alert(data);
                                    window.location.reload();
                                }
                            });
                        });
                            $("#cn3").change(function () {
                            var id = $("#cn3").val();
                            $.ajax({
                                url: "savesession.php?name=id&value=" + id,
                                success: function (data) {
                                    //alert(data);
                                    window.location.reload();
                                }
                            });
                        });
            }
                    );



            
                            </script>
            <?php
        }

        //extra-headers close

        public function pageContent() {
            $menuitem = "companydetails";
            include "sidemenu." . $this->currStore->usertype . ".php";
            $formResult = $this->getFormResult();
            $write_htm = true;
//            $currUser = getCurrUser();
            ?>


            <div class="grid_10">
                <?php
                $db = new DBConn();
                $currUser = getCurrUser();
                ?>
                <div class="box">
                    <h2>Company Details</h2><br>

                    <div style="margin-top: 0px; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; position: static; overflow:auto; ">
                        <div style="float:right">
                             <?php  
                           if ($currUser->usertype == UserType::Admin || $currUser->usertype == UserType::CKAdmin) { ?> 
                                <button><a target="_blank" href="editcontactdetails">Add Employees</a></button>
                            <?php } ?>
                        </div>
                        <div class="grid_12">

                            <input type="radio" id="cn" name="cn" value="1"   <?php if ($this->id == 1) { ?>checked <?php } ?>  onchange="reload()"><b>Company Contacts</b>
                            <input type="radio" id="cn1" name="cn" value="2"  <?php if ($this->id == 2) { ?>checked <?php } ?> onchange="reload()" ><b>Company Details</b>
                            <input type="radio" id="cn2" name="cn" value="3"   <?php if ($this->id == 3) { ?>checked <?php } ?>  onchange="reload()"><b>Bank Details</b>
                            <input type="radio" id="cn3" name="cn" value="4"   <?php if ($this->id == 4) { ?>checked <?php } ?>  onchange="reload()"><b>Store Stock Limit</b>
                           <br><br>


                            <?php if ($this->id == 2) { ?>
                             
                                <div class="grid_10">
                                    <table>
                                        <caption><b>Company Details</b></caption>
                                        <tr>
                                            <th>Company Name</th>
                                            <td>Fashionking Brands Private Limited</td>
                                        </tr>
                                        <tr>
                                            <th>Company Office Address</th>
                                            <td>5th Floor, Ganeshree, Behind OM Bakery, Lane No. 14, Prabhat Road, Pune, 411004</td>
                                        </tr>
                                        <tr>
                                            <th>Company Registered Office Address</th>
                                            <td>Chember No. 502,Central Tower, Kohinoor Square, N.C Kelkar Marg, Opp Shivsena Bhavan, Dadar, Mumbai- 400028</td>
                                        </tr>
                                        <tr>
                                            <th>Baramati Factory Address</th>
                                            <td>Plot No. 21,22,23, Hi-Tech Textile Park, MIDC, Baramati, Dist-Pune 413133</td>
                                        </tr>
                                        <tr>
                                            <th>Company GST Number</th>
                                            <td>27AAACC7418H1ZQ</td>
                                        </tr>
                                        <tr>
                                            <th>Company PAN Number </th>
                                            <td>AAACC7418H</td>
                                        </tr>
                                        <tr>
                                            <th>Company Phone Number</th>
                                            <td>020-25431266 / 67</td>
                                        </tr>
                                       


                                    </table>
                                </div>

                            <?php } ?>

                            <?php if ($this->id == 3) { ?>

                                <div class="grid_10">
                                    <table>
                                        <caption><b>Bank Details</b></caption>
                                        <tr>
                                            <th>Company Bank Name</th>
                                            <td>Axis Bank Limited</td>
                                        </tr>
                                        <tr>
                                            <th>Company Bank Account Number </th>
                                            <td>104010200006651</td>
                                        </tr>
                                        <tr>
                                            <th>Company Bank IFSC Code </th>
                                            <td>UTIB0000104</td>
                                        </tr>
                                        <tr>
                                            <th>Company Bank Branch Name </th>
                                            <td>Kothrud, Pune</td>
                                        </tr>

                                    </table>
                                </div>
                            <?php } ?>



                            <?php if ($this->id == 1) {
                                ?>
<!--                                <div class="grid_2">&nbsp;</div>-->
                                <div class="grid_8">
                                    <table>
                                        <caption><b>Company Contacts Details</b></caption>
                                        <tr>
                                            <th>Name</th>
                                            <th>Designation</th>
                                            <th>Contact Number</th>
                                            <th>E-mail</th>
                                          <?php if ($currUser->usertype == UserType::Admin || $currUser->usertype == UserType::CKAdmin) { ?> 
                                                <th></th>
                                                <th></th>
                                            <?php } ?>
                                        </tr>
                                        <?php
                                        $query = "Select id,name,contactno,designation,email from contactdetails where inactive=0";
                                        $qresult = $db->fetchObjectArray($query);
                                        
                                        foreach ($qresult as $details) {
                                            ?>
                                            <tr>
                                                <td><?php echo "Mr. ".$details->name ?></td>
                                                <td><?php echo $details->designation ?></td> 
                                                <td><?php echo $details->contactno ?></td>
                                                <td><?php echo $details->email ?></td>
                                                <?php if ($currUser->usertype == UserType::Admin || $currUser->usertype == UserType::CKAdmin) { ?>        
                                                    <td><a  href="editcontactdetails/ids=<?php echo $details->id ?>"> Edit </a></td> 
                                                    <td><button onclick='deleteEmployee(<?php echo $details->id ?>)'>Delete</button></td> 
                                                <?php } ?>
                                            </tr>
            <?php } ?>
                                    </table>
                                </div>
                            <?php }
                            ?>        
                            
                              <?php if ($this->id == 4) { ?>

                                <div class="grid_10">
                                    <table>
                                        <caption><b>Store Stock Limits (MRP)</b></caption>
                                        <?php $stocklimit=$db->fetchObject("select min_stock_level ,max_stock_level  from it_codes where id = ". $this->currStore->id);?>
                                        <tr>
                                            <th>Store Maximum Stock Limit </th>
                                            <td><?php if(isset($stocklimit->max_stock_level) && $stocklimit->max_stock_level>0){
                                            echo $stocklimit->max_stock_level;}else{echo "";}?></td>
                                        </tr>
                                        <tr>
                                            <th>Store Minimum Stock Limit</th>
                                            <td><?php if(isset($stocklimit->min_stock_level) && $stocklimit->min_stock_level>0){
                                            echo $stocklimit->min_stock_level;}else{echo "";}?></td>
                                        </tr>

                                    </table>
                                </div>
                            <?php } ?>

                        </div>

                    </div>
                    <?php
                }

            }
            ?>