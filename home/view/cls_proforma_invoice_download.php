<?php
require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once "session_check.php";

class cls_proforma_invoice_download extends cls_renderer {

    var $currStore;
    var $params;

    function __construct($params = null) {
        $this->currStore = getCurrUser();
        $this->params = $params;
    }

    function extraHeaders() {
        if (!$this->currStore) {
            ?>
            <h2>Session Expired</h2>
            Your session has expired. Click <a href="">here</a> to login.
            <?php
            return;
        }
        ?>
        <script type="text/javascript" src="js/expand.js"></script>
        <link rel="stylesheet" href="css/prettyPhoto.css" type="text/css" media="screen" title="prettyPhoto main stylesheet" charset="utf-8" />
        <script src="js/prettyPhoto/jquery.prettyPhoto.js" type="text/javascript" charset="utf-8"></script>
        <?php
    }

    //extra-headers close

    public function pageContent() {
        $menuitem = "proformainvoicedownload";
        include "sidemenu." . $this->currStore->usertype . ".php";
        ?>

        <div class="grid_10">
            <?php
            $db = new DBConn();     
            $store_id= getCurrUserId();
            
            if($this->currStore->usertype==UserType::Admin || $this->currStore->usertype==UserType::Accounts){
                $where="";
            }else{
                 $where="where store_id=$store_id";
            }

            $query = "select proforma_invoice_no, order_no, createtime from proforma_invoice_detail $where order by id desc";
            $orders = $db->fetchObjectArray($query);
            ?>
            <div class="box">
                <h2>
                    <a href="#" id="toggle-accordion" style="cursor: pointer; ">Active Orders</a>
                </h2><br>
                <div style="margin-top: 0px; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; position: static; overflow-x: hidden; overflow-y: hidden; ">
                    <div class="block" id="accordion" style="margin-top: 0px; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; ">
                        <div id="accordion">
                            <table>
                                <tr>
                                    <th>Proforma Invoice No</th>
                                    <th>Order No</th>
                                    <th>Create Time </th>
                                    <th>Action</th>
                                </tr>
        <?php foreach ($orders as $order) {
            ?>
                                    <tr>
                                        <td><?php echo $order->proforma_invoice_no; ?></td>
                                        <td><?php echo $order->order_no; ?></td>
                                        <td><?php echo $order->createtime; ?></td>
                                        
                                        <td> <a target="_blank" href="proformapdf/<?php 
                                        $proforma_pdf = str_replace('-', '_', $order->proforma_invoice_no);
                                        echo $proforma_pdf 
                                                ?>.pdf">View Proforma PDF</td>
                                    </tr>
        <?php } ?>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
