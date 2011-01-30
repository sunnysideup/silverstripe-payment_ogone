<?php

class OgoneForms extends ViewableData {

	protected static $standard_array = array();
		static function add_to_standard_array($key, $value) {self::$standard_array[$key] = $value;}
		static function remove_from_standard_array($key) {unset(self::$standard_array[$key])}
		static function set_standard_array($a) {self::$standard_array = $a;}
		static function get_standard_array() {return self::$standard_array;}


	static function PaymentForm($arrayOfValues) {
		$v = '';
		$arrayOfValues = array_merge($arrayOfValues, self::get_standard_array());
		if(is_array($arrayOfValues) && count($arrayOfValues)) {
			foreach($arrayOfValues as $key => $value) {
				$v . ='<input name="'.$key.'" value="'.Convert::raw2sql($value).'" type="hidden" />'
			}
		}
		return '
			<form method="post" action="https://secure.ogone.com/ncol/xxxxx/orderstandard.asp" id="ogone_payment_form" name="ogone_payment_form">
				'.$v.'
				<input type="submit", "pay" id="submit2" name="submit2" />
			</form>';
	}


	function validateArray($key) {
		$array = array (
			"PSPID", //fill here your PSPID
			"orderID", //fill here your REF
			"amount", //fill here your amount * 100
			"currency", //fill here your currency
			"language", //fill here your Client language <!-- lay out information -->
			"TITLE", //fill here your title
			"BGCOLOR", //fill here your background color
			"TXTCOLOR", //fill here your text color
			"TBLBGCOLOR", //fill here your table background color
			"TBLTXTCOLOR", //fill here your table text color
			"BUTTONBGCOLOR", //fill here your background button color
			"BUTTONTXTCOLOR", //fill here your button text color
			"FONTTYPE", //fill here your font
			"LOGO", //fill here your logo file name <!-- or dynamic template page -->
			"TP", //fill here your template page <!-- post-payment redirection -->
			"accepturl",
			"declineurl",
			"exceptionurl",
			"cancelurl",
			"backurl", //<!-- miscellanous -->
			"homeurl",
			"catalogurl",
			"CN", //fill here your Client name
			"EMAIL", //fill here your Client email
			"PM",
			"BRAND",
			"ownerZIP",
			"owneraddress",
			"owneraddress2",
			"SHASign", //fill here your signature
			"Alias",
			"AliasUsage",
			"AliasOperation",
			"COM",
			"COMPLUS",
			"PARAMPLUS",
			"USERID",
			"CreditCode",
			// --------- OPTIONAL
			"PM"// 			<option>CreditCard</option>			<option>iDEAL</option>			<option>ING HomePay</option>			<option>KBC Online</option>			<option>CBC Online</option> 			<option>DEXIA NetBanking</option>
			"BRAND",
			"addrMatch",
			"CN",:
			"Ecom_BillTo_Postal_Name_First",
			"Ecom_BillTo_Postal_Name_Last",
			"EMAIL",
			"WIN3DS",
			"SHASign",
			"ownerZIP",
			"owneraddress",
			"owneraddress2",
			"ownercty",
			"ownertown",
			"ownertelno",
			"Alias",
			"AliasUsage",
			"AliasOperation",
			"COM",
			"COMPLUS",
			"PARAMPLUS",
			"PARAMVAR",
			"USERID",
			"CreditCode",
			"GENERIC_BL",
			"DATATYPE",
			//"PM list type",
			"PAYID",
			"CUID",
			"SCORINGCLIENT",
			"CIVILITY",
			"PMLIST",
			"operation",
			"Ecom_ShipTo_Telecom_Phone_Number",
			"Ecom_ShipTo_Online_Email",
			"Ecom_ShipTo_DOB",
			"Ecom_BillTo_Postal_Street_Line1",
			"Ecom_BillTo_Postal_Street_Line2",
			"Ecom_BillTo_Postal_Street_Number",
			"Ecom_BillTo_Postal_City",
			"Ecom_BillTo_Postal_CountryCode",
			"Ecom_BillTo_Postal_PostalCode",
			"Ecom_ShipTo_Postal_Street_Line1",
			"Ecom_ShipTo_Postal_Street_Line2",
			"Ecom_ShipTo_Postal_Street_Number",
			"Ecom_ShipTo_Postal_City",
			"Ecom_ShipTo_Postal_CountryCode",
			"Ecom_ShipTo_Postal_PostalCode",
			"Ecom_ShipTo_Postal_Name_Prefix",
			"Ecom_ShipTo_Postal_Name_First",
			"Ecom_ShipTo_Postal_Name_Last",
			"Ecom_ShipTo_Company",
			"InvDate",
			"INVOrderID",
			"AmountHTVA",
			"AmountTVA",
			"Device"
		);
		if(isset($array[$key])) {
			return true;
		}
	}

}
