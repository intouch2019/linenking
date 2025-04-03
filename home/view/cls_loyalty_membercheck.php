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
    <?php }

    public function pageContent() {
        if (!$this->currStore) {
            print "Unauthorized Access";
            return;
        }

        $menuitem = "loyaltymembercheck";
        include "sidemenu." . $this->currStore->usertype . ".php";
        $db = new DBConn();
        ?>

        <h2 style="text-align: center; color: #333;">Search Customer Membership Status</h2>
        <form method="POST" action="" style="text-align: center; margin-bottom: 20px;">
            <label for="mobile" style="font-weight: bold;">Enter Mobile Number:</label>
            <input type="text" id="mobile" name="mobile" value="<?php echo htmlentities($this->mobile); ?>" required style="padding: 5px; border: 1px solid #ccc; border-radius: 4px; margin: 0 10px;" pattern="\d{10}" title="Enter a valid 10-digit mobile number" />
            <button type="submit" style="padding: 5px 10px; background-color: #28a745; color: #fff; border: none; border-radius: 4px; cursor: pointer;">Search</button>
        </form>

        <?php
        if ($this->mobile && preg_match('/^\d{10}$/', $this->mobile)) {
            $query = "SELECT mm.member_name, mm.member_mobno, mm.membership_enroll_date, mm.membership_expiry_date, c.store_name as member_enroll_bystore, mm.is_membership_active FROM membership_customer_details mm,it_codes c WHERE c.id=mm.member_enroll_bystore and member_mobno = '$this->mobile'";
            
            $result = $db->fetchObjectArray($query);
      
            if ($result) {
                echo "<h3 style='text-align: center; color: #444;'>Customer Details</h3>";
                echo "<table border='1' style='width: 80%; margin: 0 auto; border-collapse: collapse; text-align: center; border: 3px solid black;'>
                        <tr style='background-color: #f2f2f2;'>
                            <th style='padding: 10px; border: 1px solid black;'>Member Name</th>
                            <th style='padding: 10px; border: 1px solid black;'>Mobile Number</th>
                            <th style='padding: 10px; border: 1px solid black;'>Enrolled By Store</th>
                            <th style='padding: 10px; border: 1px solid black;'>Enrollment Date</th>
                            <th style='padding: 10px; border: 1px solid black;'>Expiry Date</th>
                            <th style='padding: 10px; border: 1px solid black;'>Membership Active</th>
                        </tr>";
                foreach ($result as $row) {
                    echo "<tr style='background-color: #fff;'>
                            <td style='padding: 10px; border: 1px solid black;'>" . htmlentities($row->member_name) . "</td>
                            <td style='padding: 10px; border: 1px solid black;'>" . htmlentities($row->member_mobno) . "</td>
                                 <td style='padding: 10px; border: 1px solid black;'>" . htmlentities($row->member_enroll_bystore) . "</td>
                            <td style='padding: 10px; border: 1px solid black;'>" . htmlentities($row->membership_enroll_date) . "</td>
                            <td style='padding: 10px; border: 1px solid black;'>" . htmlentities($row->membership_expiry_date) . "</td>
                            <td style='padding: 10px; border: 1px solid black;'>" . ($row->is_membership_active ? '<span style="color: green; font-weight: bold;">Yes</span>' : '<span style="color: red; font-weight: bold;">No</span>') . "</td>
                          </tr>";
                }
                echo "</table>";
            } else {
                echo "<p style='text-align: center; color: red;'>No customer found with this mobile number.</p>";
            }
          
        } else if ($this->mobile) {
            echo "<p style='text-align: center; color: red;'>Invalid mobile number. Please enter a valid 10-digit number.</p>";
        }
        ?>
        <?php
    }
}
