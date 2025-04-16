<?php
require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once "lib/core/clsProperties.php";

class cls_loyalty_membercheck extends cls_renderer {
   var $currStore;
    var $storeid;
    var $mobile;

    function __construct($params = null) {
        $this->currStore = getCurrUser();
        $this->params = $params;
        if (!$this->currStore) { return; }
        $this->storeid = $this->currStore->id;
        $this->mobile = isset($_POST['mobile']) ? trim($_POST['mobile']) : "";
    }

    function extraHeaders() { ?>
        <link rel="stylesheet" href="jqueryui/css/custom-theme/jquery-ui-1.8.14.custom.css" type="text/css" media="screen" charset="utf-8" />
        <script type="text/javascript" src="js/expand.js"></script>
        <script language="JavaScript" src="js/tigra/validator.js"></script>
        <style>
            body {
                position: static !important;
                background-color: white !important;
            }

            .ui-widget-content {
                background-color: white !important;
            }
        </style>
    <?php }

    public function pageContent() {
        if (!$this->currStore) {
            print "Unauthorized Access";
            return;
        }

        $menuitem = "loyaltymembercheck";
        include "sidemenu." . $this->currStore->usertype . ".php";
        $db = new DBConn();

        if (isset($_POST['delete_mobile']) && $this->storeid == 107) { //only current userid 107 (rohan) is allowed to delete loyalty customer
            $del_mobile = $db->safe(trim($_POST['delete_mobile']));
            $seleforlogs = "SELECT * FROM membership_customer_details where is_membership_active=1 and member_mobno = $del_mobile";
            $selectlogsobj = $db->fetchObject($seleforlogs);

            if ($selectlogsobj) {
                $membership_number = $selectlogsobj->membership_number;
                $member_mobno = $selectlogsobj->member_mobno;
                $member_name = $selectlogsobj->member_name;
                $membership_enroll_amt = $selectlogsobj->membership_enroll_amt;
                $membership_enroll_date = $selectlogsobj->membership_enroll_date;
                $membership_expiry_date = $selectlogsobj->membership_expiry_date;
                $member_enroll_bystore = $selectlogsobj->member_enroll_bystore;
                $member_last_purchase = $selectlogsobj->member_last_purchase;
                $is_membership_active = $selectlogsobj->is_membership_active;
                $query_executedby = $this->storeid;
                $query_type = Membership_querytype::Delete;

                $inserlogs = "INSERT INTO membership_customer_details_logs SET 
                    membership_number = '$membership_number', 
                    member_mobno = '$member_mobno', 
                    member_name = '$member_name', 
                    membership_enroll_amt = '$membership_enroll_amt', 
                    membership_enroll_date = '$membership_enroll_date', 
                    membership_expiry_date = '$membership_expiry_date', 
                    member_enroll_bystore = '$member_enroll_bystore', 
                    member_last_purchase = '$member_last_purchase', 
                    is_membership_active = '$is_membership_active', 
                    query_type = '$query_type', 
                    query_executedby = '$query_executedby', 
                    query_exe_time = now(), 
                    update_date = now()";
                $db->execInsert($inserlogs);

                $deleteQuery = "DELETE FROM membership_customer_details WHERE is_membership_active=1 and member_mobno = $del_mobile";
                $db->execUpdate($deleteQuery);
                echo "<script>alert('Customer record deleted successfully');</script>";
                $this->mobile = "";
            } else {
                echo "<script>alert('Customer record not found');</script>";
            }
        }
        ?>
        <div style="padding-top: 120px; text-align: center; ">
            <div style="display: inline-block; padding: 30px; border: 2px solid #ccc; border-radius: 10px; background-color: #f8f8f8; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                <h2 style="text-align: center; color: #333;">Search Customer Membership Status</h2>
                <form method="POST" action="" style="text-align: center; margin-bottom: 20px;">
                    <label for="mobile" style="font-weight: bold;">Enter Mobile Number:</label>
                    <input type="text" id="mobile" name="mobile" value="<?php echo htmlentities($this->mobile); ?>" required style="padding: 5px; border: 1px solid #ccc; border-radius: 4px; margin: 0 10px;" pattern="\d{10}" title="Enter a valid 10-digit mobile number" />
                    <button type="submit" style="padding: 5px 10px; background-color: #28a745; color: #fff; border: none; border-radius: 4px; cursor: pointer;">Search</button>
                </form>
            </div>
        </div>

        <?php
        if ($this->mobile && preg_match('/^\d{10}$/', $this->mobile)) {
            $query = "SELECT mm.member_name, mm.member_mobno, mm.membership_enroll_date, mm.membership_expiry_date, c.store_name as member_enroll_bystore, mm.is_membership_active 
                      FROM membership_customer_details mm, it_codes c 
                      WHERE c.id = mm.member_enroll_bystore AND mm.member_mobno = '$this->mobile'";
            
            $result = $db->fetchObjectArray($query);

            if ($result) {
                echo "<h3 style='text-align: center; color: #444;'>Customer Details</h3>";
                echo "<table border='1' style='width: 80%; margin: 0 auto; border-collapse: collapse; text-align: center; border: 3px solid black;'>
                        <tr style='background-color: #f2f2f2;'>
                            <th style='padding: 10px; border: 1px solid black; font-size: 16px;'>Member Name</th>
                            <th style='padding: 10px; border: 1px solid black; font-size: 16px;'>Mobile Number</th>
                            <th style='padding: 10px; border: 1px solid black; font-size: 16px;'>Enrolled By Store</th>
                            <th style='padding: 10px; border: 1px solid black; font-size: 16px;'>Enrollment Date</th>
                            <th style='padding: 10px; border: 1px solid black; font-size: 16px;'>Expiry Date</th>
                            <th style='padding: 10px; border: 1px solid black; font-size: 16px;'>Membership Active</th>";
                
                if ($this->storeid == 107) { //only current userid 107 (rohan) is allowed to delete loyalty customer
                    echo "<th style='padding: 10px; border: 1px solid black; font-size: 16px;'>Delete</th>";
                }

                echo "</tr>";

                foreach ($result as $row) {
                    echo "<tr style='background-color: #fff;'>
                            <td style='padding: 10px; border: 1px solid black; font-size: 16px;'>" . htmlentities($row->member_name) . "</td>
                            <td style='padding: 10px; border: 1px solid black; font-size: 16px;'>" . htmlentities($row->member_mobno) . "</td>
                            <td style='padding: 10px; border: 1px solid black; font-size: 16px;'>" . htmlentities($row->member_enroll_bystore) . "</td>
                            <td style='padding: 10px; border: 1px solid black; font-size: 16px;'>" . htmlentities($row->membership_enroll_date) . "</td>
                            <td style='padding: 10px; border: 1px solid black; font-size: 16px;'>" . htmlentities($row->membership_expiry_date) . "</td>
                            <td style='padding: 10px; border: 1px solid black; font-size: 16px;'>" . ($row->is_membership_active ? '<span style="color: green; font-weight: bold;">Yes</span>' : '<span style="color: red; font-weight: bold;">No</span>') . "</td>";
                    
                    if ($this->storeid == 107) { //only current userid 107 (rohan) is allowed to delete loyalty customer
                        echo "<td style='padding: 10px; border: 1px solid black;'>
                                <form method='POST' action='' onsubmit='return confirm(\"Are you sure you want to delete this customer?\");'>
                                    <input type='hidden' name='delete_mobile' value='" . htmlentities($row->member_mobno) . "' />
                                    <button type='submit' style='padding: 5px 10px; background-color: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer;'>Delete</button>
                                </form>
                              </td>";
                    }

                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p style='text-align: center; color: red;'>No customer found with this mobile number.</p>";
            }

        } else if ($this->mobile) {
            echo "<p style='text-align: center; color: red;'>Invalid mobile number. Please enter a valid 10-digit number.</p>";
        }
    }
}
