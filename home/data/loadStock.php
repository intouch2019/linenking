<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
require_once "../../it_config.php";
require_once "lib/db/DBConn.php";

$g_ctg_ids = array(
"SHIRT" => "001",
"TROUSER" => "002",
"JEANS" => "003",
"T-SHIRT" => "004",
"SOCKS" => "006",
"Formal Shirt" => "007",
"Casual Shirt" => "008",
"Handkerchiefs" => "014",
"PRO-WASH SHIRT" => "016",
"SLIM SHIRT" => "017",
"SHORT SHIRT" => "018",
"NON-PLEAT" => "019",
"SL" => "022",
"SM" => "023",
"FF" => "024",
"FH" => "025",
"SEMI FORMAL" => "030",
"SEMI FORMAL SHIRT" => "030",
"SLIM FORMAL" => "031",
);

$g_style_ids = array(
"FULL - SH" => "001",
"HALF - SH" => "002",
"NA" => "003",
"PLEAT - TR" => "004",
"NON-PLEAT - TR" => "005",
"COMFORT FIT - JN" => "006",
"Non-pleat" => "007",
"FS" => "008",
"HS" => "009",
"Pleated" => "010",
"NON PLEATED" => "011",
"NON PLEATE" => "012",
"Comfort" => "013",
"BOOT CUT" => "015",
"NON-PLEATE" => "017",
"CH" => "021",
"SL" => "022",
"SM" => "023",
"FF" => "024",
"FH" => "025",
"HT" => "026",
"SF" => "027",
"SH" => "028",
"NP" => "029",
"PD" => "030",
"PE" => "031",
"PL" => "032",
"RH" => "033",
"CF" => "034",
"PP" => "037",
"NP/PL" => "038",
);

$g_size_ids = array(
"39 CM" => "001",
"40 CM" => "002",
"42 CM" => "003",
"44 CM" => "004",
"36 IN" => "005",
"38 IN" => "006",
"40 IN" => "007",
"42 IN" => "008",
"30 IN" => "009",
"32 IN" => "010",
"34 IN" => "011",
"28 IN" => "013",
"NA" => "014",
"34" => "015",
"38" => "016",
"32" => "017",
"30" => "018",
"36" => "019",
"M" => "020",
"XL" => "021",
"L" => "022",
"XXL" => "023",
"40" => "024",
"42" => "025",
"44" => "026",
"28" => "027",
"39" => "028",
"10" => "030",
"2" => "031",
"28 / 71" => "032",
"30 / 76" => "033",
"32 / 81" => "034",
"34 / 86" => "035",
"36 / 91" => "036",
"38 / 97" => "037",
"40 / 102" => "038",
"42 / 107" => "039",
"44 / 112" => "040",
);


$db = new DBConn();
$db->execUpdate("update it_ck_items set curr_qty = 0, updatetime=now()");
$fh = fopen($argv[1],"r");
if (!$fh) { print "Failed to open $argv[1]\n"; return; }
$count=0;
$tot_qty=0; $skip_qty=0;
while(($data=fgetcsv($fh)) !== FALSE) {
//ver01 - 0010010000005,550,SHIRT,FORMAL SHIRT - PRO-WASH REGALIA,HALF - SH,39 CM,5480,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0
//ver02 - 0010010000005,550,SHIRT,HALF - SH,39 CM,5480,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0
//ver03 - 0010020017702,1295,TROUSER,NP,32,A-6,ROYALE,6,0,0,0,0,0,0,0,0,0,0,0,0,0,6,7770
//ver04 - 0010020019470,995,TROUSER,NP,34 / 86,8628,BASIC TROUSER,1,,
//ver05 - 0010020014966,1395,TROUSER,PL,42,A-4,NA,10,0,0,0,0,0,0,0,0,0,0,0,0,0,10,13950
$item_code=$data[0];
$mrp=$data[1];
$ctg_name=$data[2];
$ctg_id = isset($g_ctg_ids[$ctg_name]) ? $g_ctg_ids[$ctg_name] : false;
$style_name=$data[3];
$style_id = isset($g_style_ids[$style_name]) ? $g_style_ids[$style_name] : false;
$size_name=$data[4];
$size_id = isset($g_size_ids[$size_name]) ? $g_size_ids[$size_name] : false;
$design_no=$data[5];
$prod_type=$data[6];
$curr_qty=intval($data[21]);
$count++;
//if ($curr_qty <= 0) { continue; }
if (!$ctg_id || !$style_id || !$size_id) {
	$skip_qty+=$curr_qty;
	print "\n$count:::$design_no,$ctg_name=$ctg_id,$style_name=$style_id,$size_name=$size_id,curr_qty=$curr_qty\n";
	continue;
}
$tot_qty+=$curr_qty;
$item_code=$db->safe($item_code);
$ctg_id=$db->safe($ctg_id);
$ctg_name=$db->safe($ctg_name);
$style_id=$db->safe($style_id);
$style_name=$db->safe($style_name);
$size_id=$db->safe($size_id);
$size_name=$db->safe($size_name);
$design_no=$db->safe($design_no);
$prod_type=$db->safe($prod_type);
$item = $db->fetchObject("select * from it_ck_items where item_code=$item_code");
if ($item) {
	$query = "update it_ck_items set curr_qty=$curr_qty, updatetime=now() where id=$item->id";
	$db->execUpdate($query);
} else {
	$query = "insert into it_ck_items set item_code=$item_code, mrp=$mrp, ctg_id=$ctg_id, ctg_name=$ctg_name, style_id=$style_id, style_name=$style_name, size_id=$size_id, size_name=$size_name, design_no=$design_no, curr_qty=$curr_qty";
	$db->execInsert($query);
}

$obj = $db->fetchObject("select * from it_ck_designs where ctg_id=$ctg_id and design_no=$design_no");
if ($obj) {
	$db->execUpdate("update it_ck_designs set prod_type=$prod_type, updatetime=now() where id=$obj->id");
} else {
	$db->execUpdate("insert into it_ck_designs set design_no=$design_no, ctg_id=$ctg_id, prod_type=$prod_type");
}
print "\r$count";
}
fclose($fh);
$db->closeConnection();
print "\ntot_qty=$tot_qty,skip_qty=$skip_qty\n";
