<?php

class UserType {
const Admin = 0;
const CKAdmin = 1;
const Manager = 3;
const Dispatcher = 2;
const Dealer = 4;
const Picker = 5;
const Accounts = 6;
const NoLogin = -1;
const BHMAcountant =7;
//const Customer_corporate =7;
// New Addition
const Supervisor =8;
const Operator =9;
const OfficeBoy =10;
const DesignSearchPicker=11;

public static function getAll() {
	return array(
		UserType::Admin => "Intouch Administrator",
		UserType::CKAdmin => "Administrator",
                UserType::Dispatcher => "Dispatcher",
                UserType::Manager => "Manager",
                UserType::Picker => "Picker",
                UserType::Accounts => "Accounts Manager",
                UserType::Dealer => "Store Manager",
                UserType::BHMAcountant => "BHM Accounts Manager",
                //new addition
                UserType::Supervisor => "Supervisor",
                UserType::Operator => "Operator",
                UserType::OfficeBoy => "Office Boy",
                UserType::DesignSearchPicker => "Design Search Picker"
//                UserType::Customer_corporate => "Corporate Customer"
	);
}

public static function getName($usertype) {
	$all = UserType::getAll();
	if (isset($all[$usertype])) { return $all[$usertype]; }
	else { return "Not Found"; }
}
}

class PoType {
    const Fabric = 0;
    const Accessories = 1;    
    const ReadyMade = 2;
    
    public static function getAll() {
	return array(
		PoType::Fabric => "Fabric Purchase Order",
		PoType::Accessories => "Accessories Purchase Order",
		PoType::ReadyMade => "ReadyMade Purchase Order",            
	);
    }

    public static function getName($potype) {
	$all = PoType::getAll();
	if (isset($all[$potype])) { return $all[$potype]; }
	else { return "Not Found"; }
    }
}

class OrderStatus {
const InCart = 0;
const Active = 1;
const Picking = 2;
const Shipped = 3;
const Cancelled = 4;
const Picking_Complete = 5;
const StandingOrder = 6;

public static function getAll() {
	return array(
		OrderStatus::InCart => "In Shopping Cart",
		OrderStatus::Active => "Active",
		OrderStatus::Picking => "Picking",
		OrderStatus::Shipped => "Shipped",
                OrderStatus::Cancelled => "Cancelled",
                OrderStatus::Picking_Complete => "Picking Complete",
                OrderStatus::StandingOrder => "Standing Order"
	);
}

public static function getName($status) {
	$all = OrderStatus::getAll();
	if (isset($all[$status])) { return $all[$status]; }
	else { return "Unknown"; }
}
}

class changeType{    
    const categories= 1;
    const mfg_by = 2;
    const ck_designs = 3;
    const brands = 4;
    const styles = 5;
    const sizes = 6;
    const prod_types = 7;
    const materials = 8;
    const fabric_types = 9;
    const items = 10;
    const invoices = 11;
    const store = 12;
    const taxes = 13;
    const ck_pickgroup = 14;
    const rules = 15;
    const updateScheme =16;
    const ckinvoices = 17;
    const password=18;
    const mrptaxes = 21;
    const updateinvoice=22;
    const saleback = 24;
    const incentive=30;
    const puchasereturn=23;
    const wip_stock=31;
    const crditPoints = 32;
    const removeCrditPoints = 33;
    const properties = 34;
    
    
    public static function getAll(){
        return array(            
            changeType::categories => "Category related changes ",
            changeType::mfg_by => "Manufacturer related changes ",
            changeType::ck_designs => "Design related changes ",
            changeType::brands => "Brand related changes",
            changeType::styles => "Style related changes ",
            changeType::sizes => "Size related changes",
            changeType::prod_types => "Product Type related changes",
            changeType::materials => "Material related changes",
            changeType::fabric_types => "Fabric Type related changes",
            changeType::items => "Item related changes ",
            changeType::invoices => "Invoices related changes",
            changeType::store => "Store related changes ",
            changeType::tax => "Tax related changes",
            changeType::ck_pickgroup => "Picking Complete ",
            changeType::ckinvoices => "CK Invoices",
            changeType::rules => "Scheme Rules",
            changeType::updateScheme => 'Update Scheme',
            changeType::password => "Password Change",
            changeType::mrptaxes => 'MRP Taxes',
            changeType::updateinvoice => 'Update Invoice',
            changeType::saleback => "Sale Back",
            changeType::puchasereturn=>"Purchase Return",
            changeType::wip_stock => "Stock From WIP",
            changeType::crditPoints => "Credit Point Sync",
            changeType::removeCrditPoints => "Remove Credit Point Sync",
            changeType::properties => "Properties"
        );
    }
    
    public static function getName($type){
         $all = changeType::getAll();
         if(isset($all[$type])){ return $all[$type];}
         else{ return "Unknown"; }
    }
    
}

class taxType{
    const VAT = 1;
    const CST = 2;
    
    public static function getALL(){
        return array(
          taxType::VAT => "Within Maharashtra",  //"VAT", rename to  Within Maharashtra
          taxType::CST => "Outside Maharashtra"   //CST, rename to  outside Maharashtra
        );
    }
    
    public static function getName($type){
        $all = taxtype::getALL();
        if(isset($all[$type])){ return $all[$type];}
        else{ return "Unknown"; }
    }
}

class RuleType {
const PercentDiscountItems = 1;
const BuyMGetN = 2;
const PercentDiscountCategories = 3;
const BuyMGetNPORPercentDiscountItems = 12;

public static function getAll() {
	return array(
		RuleType::PercentDiscountItems => "Percent Discount on all Items",
		RuleType::BuyMGetN => "Buy (m) get (n) Free",
                RuleType::PercentDiscountCategories => "Percent Discount on Categories",
                RuleType::BuyMGetNPORPercentDiscountItems => " Buy (m) get (n) Free / Percent Discount on all Items "
	);
}

public static function getName($ruletype) {
	$all = RuleType::getAll();
	if (isset($all[$ruletype])) { return $all[$ruletype]; }
	else { return "Not Found"; }
}
}




class RatioType{
    const Standing = 1;
    const Base = 2;
    
    public static function getALL(){
        return array(
          RatioType::Standing => "Standing Stock Ratio",
          RatioType::Base => "Base Stock Ratio",  
        );
    }
    
    public static function getName($type){
        $all = RatioType::getALL();
        if(isset($all[$type])){ return $all[$type];}
        else{ return "Unknown"; }
    }
}

class StockType{
      const Undefine= 0;
      const NormalStock = 1;
      const Stock50percent = 2;
    
    public static function getALL(){
        return array(
            StockType::Undefine => "Undefine",
            StockType::NormalStock => "NormalStock",
            StockType::Stock50percent => "Stock50percent"
        );
    }
    
    public static function getName($type){
        $all = StockType::getALL();
        if(isset($all[$type])){ return $all[$type];}
        else{ return "Unknown"; }
    }
}

class StoreType{
      const Undefine= 0;
      const NormalStore = 1;
      const Store50percent = 2;
      const CompanyStore=3;
    public static function getALL(){
        return array(
            StoreType::Undefine => "Undefine",
            StoreType::NormalStore => "NormalStore",
            StoreType::Store50percent => "Store50percent",
            StoreType::CompanyStore => "CompanyStore"
        );
    }
    
    public static function getName($type){
        $all = StoreType::getALL();
        if(isset($all[$type])){ return $all[$type];}
        else{ return "Unknown"; }
    }
}

class StoreStatus{
//      const Undefine= 0;
      const L_to_L = 1;
      const New_L_to_L = 2;
      const New1 = 3;
      const closed = 4;
      
    public static function getALL(){
        return array(
//            StoreType::Undefine => "Undefine",
            StoreStatus::L_to_L => "L to L",
            StoreStatus::New_L_to_L => "New L to L",
            StoreStatus::New1 => "New",
            StoreStatus::closed => "Closed"
        );
    }
    
    public static function getName($type){
        $all = StoreStatus::getALL();
        if(isset($all[$type])){ return $all[$type];}
        else{ return "Unknown"; }
    }
}
class StoreLevel{
//      const Undefine= 0;
    const AP = "A+";
    const A = "A";
    const B = "B";
    const C = "C";
    const D = "D";

    public static function getLevel() {
        return array(
//            StoreType::Undefine => "Undefine",
            StoreLevel::AP => "A+",
            StoreLevel::A => "A",
            StoreLevel::B => "B",
            StoreLevel::C => "C",
            StoreLevel::D => "D"
        );
    }

    public static function getName($type) {
        $all = StoreLevel::getALL();
        if (isset($all[$type])) {
            return $all[$type];
        } else {
            return "Unknown";
        }
    }

}

class coreNoncore{
     const core= 1;
      const noncore = 0;
    public static function getcore(){
        return array(
            coreNoncore::core => "core",
            coreNoncore::noncore => "noncore",
      
        );
    }
    

}

class Cat_MarginType{
      const Regular = 1;
      const margin0 = 0;
    public static function getALL(){
        return array(
            Cat_MarginType::Regular => "Regular Margin",
            Cat_MarginType::margin0 => "0% Margin",
      
        );
    }
    
    public static function getName($type){
        $all = Cat_MarginType::getALL();
        if(isset($all[$type])){ return $all[$type];}
        else{ return "Unknown"; }
    }
}

class RollType {
const Management = 1;
const IT = 2;
const Administraion = 3;
const Marketing = 4;
//const Picker = 4;
//const Supervisor = 4;
const Accounts = 5; 
const Sales = 6;
const Warehouse =7;
const Purchase =8;
const VM =9;
const Production =10;
const HR =11;
const Stores =12;
const Quality =13;
//const Customer_corporate =7;

public static function getAll() {
	return array(
		RollType::Management => "Management",
		RollType::IT => "IT",
                RollType::Administraion => "Administraion",
                RollType::Marketing => "Marketing",
//                RollType::Picker => "Picker",
//                RollType::Supervisor => "Supervisor",
                RollType::Accounts => "Accounts",
                RollType::Sales => "Sales",
                RollType::Warehouse => "Warehouse",
                RollType::Purchase =>"Purchase",
                RollType::VM =>"VM",
                RollType::Production =>"Production",
                RollType::HR =>"HR",
                RollType::Stores =>"Stores",
                RollType::Quality =>"Quality",
//                RollType::Warehouse => "Warehouse"
//               UserType::Customer_corporate => "Corporate Customer"
	);
}

public static function getName($rolltype) {
	$all = RollType::getAll();
	if (isset($all[$rolltype])) { return $all[$rolltype]; }
	else { return "Unknown"; }
}
}

class Membership_querytype{
    const Insert = 1;
    const Update = 2;
    const Delete = 3;
    
    public static function getALL(){
        return array(
          querytype::Insert => "Insert Query",
          querytype::Update => "Update Query",
          querytype::Delete => "Delete Query"      
        );
    }
    
    public static function getName($type){
        $all = Membership_querytype::getALL();
        if(isset($all[$type])){ return $all[$type];}
        else{ return "Unknown"; }
    }
}


class OTP_Status{
    const OTP_send = 1;
    const Verified = 2;
    const Expired = 3;
    
    public static function getALL(){
        return array(
          querytype::OTP_send => "OTP Send",
          querytype::Verified => "Verified",
          querytype::Expired => "Expired"      
        );
    }
    
    public static function getName($type){
        $all = OTP_Status::getALL();
        if(isset($all[$type])){ return $all[$type];}
        else{ return "Unknown"; }
    }
}

class Discount_scheme{
    const loyalty_membership = 1;
    const dealer_discount = 2;
    
    public static function getALL(){
        return array(
          Discount_scheme::loyalty_membership => "Loyalty Membership",
          Discount_scheme::dealer_discount => "EOSS"
        );
    }
    
    public static function getName($type){
        $all = Discount_scheme::getALL();
        if(isset($all[$type])){ return $all[$type];}
        else{ return "Unknown"; }
    }
}
?>