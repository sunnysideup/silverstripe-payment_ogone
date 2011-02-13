<?php

/**
 */
class OgonePayment extends Payment {

	static $db = array(
		"PM" => "Varchar(100)",
		"Brand" => "Varchar(255)",
		"ACCEPTANCE" => "Varchar(255)"
	);

	/**
	* payment_options_array:  "Code" => "Title"
	*/
	protected static $payment_options_array = array('CreditCard' => 'Credit Card','iDeal' => 'iDeal','PayPal' => 'PayPal');
		static function get_payment_options_array() {return self::$payment_options_array;}
		static function set_payment_options_array($a) {self::$payment_options_array = $a;}
		static function add_payment_option($key, $title) {self::$payment_options_array[$key] = $title;}
		static function remove_payment_option($key) {unset(self::$payment_options_array[$key]);}

	/**
	* [paymentOptionCode] =>  array([key] => [fileName], [key] => [fileName], [key]);
	* e.g. : "CreditCard" => array("visa", "master-card", "maestro")
	* e.g. : "Paypal" => array("paypal" => "mysite/images/mypaypalLogo.gif")
	*/
	protected static $logos_array = array(
		"CreditCard" => array("visa", "master-card", "maestro"),
		"iDeal" => array("ideal"),
		"PayPal" => array("paypal")
	);
		static function set_logos_array($a) {self::$logos_array = $a;}
		static function remove_payment_option_from_logos_array($paymentOption) {unset(self::$logos_array[$paymentOption]);}
		static function add_payment_option_to_logos_array($paymentOption, $paymentOptionArray) {self::$logos_array[$paymentOption] = $paymentOptionArray;}
		static function get_logos_array() {return self::$logos_array;}
	// Ogone Information

	protected static $privacy_link = 'http://www.ogone.com/en/About%20Ogone/Privacy%20Policy.aspx';
		static function set_privacy_link($v) {self::$privacy_link = $v;}

	/**
	*@param $a = can be a straight list (e.g. visa, maestro) or can be associated array with image locations (e.g. "visa" => "mysite/images/MyVisaLogo.gif")
	*
	**/

	// URLs
	protected static $url = 'https://secure.ogone.com/ncol/prod/orderstandard.asp';
		static function set_url($v) {self::$url = $v;}

	protected static $test_url = 'https://secure.ogone.com/ncol/test/orderstandard.asp';
		static function set_test_url($v) {self::$test_url = $v;}
	// Test Mode

	protected static $test_mode = false;
		static function set_test_mode($test_mode) {self::$test_mode = $test_mode;}

	// Payment Information

	protected static $account_pspid;
		static function set_account_pspid($account_pspid) {self::$account_pspid = $account_pspid;}

	protected static $sha_passphrase;
		static function set_sha_passphrase($sha_passphrase) {self::$sha_passphrase = $sha_passphrase;}
		static function get_sha_passphrase() {return self::$sha_passphrase;}

	// Ogone Pages Style Optional Informations

	protected static $page_title;
		static function set_page_title($page_title) {self::$page_title = $page_title;}

	protected static $back_color;
		static function set_back_color($back_color) {self::$back_color = $back_color;}

	protected static $text_color;
		static function set_text_color($text_color) {self::$text_color = $text_color;}

	protected static $table_back_color;
		static function set_table_back_color($table_back_color) {self::$table_back_color = $table_back_color;}

	protected static $table_text_color;
		static function set_table_text_color($table_text_color) {self::$table_text_color = $table_text_color;}

	protected static $button_back_color;
		static function set_button_back_color($button_back_color) {self::$button_back_color = $button_back_color;}

	protected static $button_text_color;
		static function set_button_text_color($button_text_color) {self::$button_text_color = $button_text_color;}

	protected static $font_type;
		static function set_font_type($font_type) {self::$font_type = $font_type;}

	protected static $image_url;
		static function set_image_url($image_url) {self::$image_url = $image_url;}

	protected static $template;
		static function set_template($v) {self::$template = $v;}

	protected static $hide_payment_method_in_orderform = true;
		static function set_hide_payment_method_in_orderform($v) {self::$hide_payment_method_in_orderform = $v;}

	function getPaymentFormFields() {
		$js = '';
		if(self::$hide_payment_method_in_orderform) {
			Requirements::customScript("jQuery('#PaymentMethod').hide(); ", "OgoneHidePaymentMethodDiv");
		}
		if(!(self::get_payment_options_array()) || !count(self::get_payment_options_array()))  {
			user_error("no payment options have been set", E_USER_NOTICE);
		}
		$fieldSet = new FieldSet();
		// PAYMENT OPTIONS
		$logosArray = self::get_logos_array();
		$paymentOptions = self::get_payment_options_array();
		if(is_array($logosArray) && count($logosArray) && is_array($paymentOptions) && count($paymentOptions)) {
			foreach($paymentOptions as $key => $Title) {
				if(isset($logosArray[$key]) && count($logosArray[$key])) {
					foreach($logosArray[$key] as $innerKey => $value) {
						if(is_numeric($innerKey)) {
							$fileName = "/payment_ogone/images/{$value}.png";
						}
						else {
							$fileName = $value;
						}
						$logosArray[$key][$innerKey] = '<img src="'.$fileName.'" alt="'.$innerKey.'" class="logoFor'.$innerKey.'" />';
					}
					$paymentOptions[$key] = ' <span class="ogonePaymentLogos" id="paymentLogosFor'.$key.'">'.implode("",$logosArray[$key]).'</span>';
				}
			}
			$fieldSet->push(
				new OptionsetField('PM','Payment Method',$paymentOptions)
			);
		}
		// PRIVACY LINK
		if(self::$privacy_link) {
			$privacyLink = '<span class="privacyLink"><a href="' . self::$privacy_link . '" rel="external" title="Read Ogone\'s privacy policy">' . _t("OgonePayment.PRIVACYLINK", "privacy information"). '</a></span>';
			$paymentInfo = '<div class="field nolabel readonly"><div class="middleColumn">'.$privacyLink.'</div></div>';
			$fieldSet->push(new LiteralField('OgonePaymentInfo', $paymentInfo));
		}
		return $fieldSet;
	}

	function getPaymentFormRequirements() {return null;}

	function processPayment($data, $form) {
		$page = new Page();
		$page->Title = _t("OgonePayment.PROCESSINGPAYMENT", "Processing Payment");
		$page->Form = $this->OgoneForm();
		$controller = new Page_Controller($page);
		$form = $controller->renderWith('PaymentProcessingPage');
		return new Payment_Processing($form);
	}

	function OgoneForm() {
		Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery.js');
		Requirements::themedCSS("OgonePaymentSubmitForm");

		// 1) Main Informations

		$fields = '';
		$order = $this->Order();
		$member = $order->Member();

		// 2) Main Settings

		$url = self::$test_mode ? self::$test_url : self::$url;
		$inputs['PM'] = isset($_REQUEST["PM"]) ? $_REQUEST["PM"] : "CC";
		$inputs['PSPID'] = self::$account_pspid;
		$inputs['ORDERID'] = $order->ID;
		$inputs['AMOUNT'] = $order->Total() * 100;
		$inputs['CURRENCY'] = self::$site_currency;
		$inputs['LANGUAGE'] = i18n::get_locale();
		$inputs['CN'] = $member->getName();
		$inputs['EMAIL'] = $member->Email;
		$inputs['OWNERADDRESS'] = $member->Address . ($member->AddressLine2 ? " $member->AddressLine2" : '');
		$inputs['OWNERZIP'] = $member->PostalCode;
		$inputs['OWNERTOWN'] = $member->City;
		$inputs['OWNERCTY'] = $member->Country;
		if($member->hasMethod('getPhoneNumber')) $inputs['OWNERTELNO'] = $member->getPhoneNumber();
		$inputs['PMLISTTYPE'] = 2;
		// 3) Redirection Informations

		$redirections = array('ACCEPT', 'BACK', 'CANCEL','DECLINE', 'EXCEPTION');

		foreach($redirections as $redirection) {
			$inputs[strtoupper("{$redirection}URL")] = Director::absoluteBaseURL() . OgonePayment_Handler::redirect_link($redirection, $order, $this);
		}

		// 4) Ogone Pages Style Optional Informations

		if(self::$page_title) $inputs['TITLE'] = self::$page_title;
		if(self::$back_color) $inputs['BGCOLOR'] = self::$back_color;
		if(self::$text_color) $inputs['TXTCOLOR'] = self::$text_color;
		if(self::$table_back_color) $inputs['TBLBGCOLOR'] = self::$table_back_color;
		if(self::$table_text_color) $inputs['TBLTXTCOLOR'] = self::$table_text_color;
		if(self::$button_back_color) $inputs['BUTTONBGCOLOR'] = self::$button_back_color;
		if(self::$button_text_color) $inputs['BUTTONTXTCOLOR'] = self::$button_text_color;
		if(self::$font_type) $inputs['FONTTYPE'] = self::$font_type;
		if(self::$image_url) $inputs['LOGO'] = urlencode(self::$image_url);

		// 5) Security Settings

		if(self::$sha_passphrase) {
			$shaInputs = array_change_key_case($inputs, CASE_UPPER);
			ksort($shaInputs);
			foreach($shaInputs as $input => $value) {
				if(isset($value) && $value !== null && $value !== '') {
					$joinInputs[] = strtoupper($input)."=$value";
				}
			}
			$sha = implode(self::$sha_passphrase, $joinInputs) . self::$sha_passphrase;
			$inputs['SHASIGN'] = sha1($sha);
		}

		// 6) Form Creation

		foreach($inputs as $name => $value) {
			$ATT_value = Convert::raw2att($value);
			$fields .= "<input type=\"hidden\" name=\"$name\" value=\"$ATT_value\" />";
		}
		return <<<HTML
			<form id="PaymentForm" method="post" action="$url">
				<div>
					$fields
					<input type="submit" value="Submit" />
				</div>
			</form>
			<script type="text/javascript">
				jQuery(document).ready(function() {
					jQuery("input[type='submit']").hide();
					jQuery('#PaymentForm').submit();
				});
			</script>
HTML;
	}
}

/**
 * Handler for responses from the Ogone site
 */
class OgonePayment_Handler extends Controller {

	protected static $url_segment = 'ogone';
		static function  get_url_segment(){return self::$url_segment;}
		static function  set_url_segment($v){self::$url_segment = $v;}

	static function redirect_link($action, $order, $payment) {
		return self::$url_segment. "/".strtolower($action)."/".$order->ID."/".$payment->ID."/";
	}

	protected $order, $payment;
	protected $hasBeenRedirect = false;

	function init() {
		parent::init();
		if($orderID = intval($this->request->param('ID'))) {
			$this->order = DataObject::get_by_id('Order', $orderID);
		}
		if($paymentID = intval($this->request->param('OtherID'))) {
			$this->payment = DataObject::get_by_id('OgonePayment', $paymentID);
		}
		if(! $this->order) $errors[] = 'Order';
		if(! $this->payment) $errors[] = 'Payment';
		//to do: make sexier error message.
		if(isset($errors)) {
			echo '<p>' . implode(' and ', $errors) . ' not found</p>';
			die;
		}
		if($this->payment->OrderID != $this->order->ID) {
			echo '<p>Order does not match payment.</p>';
			die;
		}
		if(isset($_REQUEST["ACCEPTANCE"])) {
			$this->payment->ACCEPTANCE = $_REQUEST["ACCEPTANCE"];
		}
		if(isset($_REQUEST["PM"])) {
			$this->payment->PM = $_REQUEST["PM"];
		}
		if(isset($_REQUEST["NCERROR"])) {
			$this->payment->ExceptionError = $_REQUEST["NCERROR"];
		}
		$this->payment->write();
	}

	function accept() {
		$status = $_REQUEST['STATUS'];
		switch($status) {
			case 5 :
			case 9 : {
				if(!isset($_REQUEST["amount"]) && isset($_REQUEST["AMOUNT"])) { $_REQUEST["amount"] = $_REQUEST["AMOUNT"];}
				if(!isset($_REQUEST["amount"]) && isset($_REQUEST["Amount"])) { $_REQUEST["amount"] = $_REQUEST["Amount"];}
				if(!isset($_REQUEST["amount"])) { $_REQUEST["amount"] = 0;}
				$money = DBField::create('Money', array("Amount" => floatval($_REQUEST["amount"]), "Currency" => Payment::site_currency()));
				$this->payment->Amount = $money;
				$this->payment->Status = 'Success';
				break;
			}
			case 51 :
			case 91 : {
				$this->payment->Status = 'Pending';
				break;
			}
			case 52 :
			case 92 : {
				$this->payment->Status = 'Failure';
				break;
			}
		}
		$this->payment->write();
		$this->checkShaOut();
		$this->payment->redirectToOrder();
	}

	function decline() {
		/*
		$status = $_REQUEST['STATUS'];
		if($status <= 2 || $status == 93) {
			$this->payment->Status = 'Failure';
			$this->payment->write();
		}
		*/
		return $this->addErrorMessage(_t("OgonePayment.PAYMENTDECLINED", "Payment declined."));
		$this->payment->redirectToOrder();
	}

	function exception() {
		return $this->addErrorMessage(_t("OgonePayment.PAYMENTERROR", "Payment error."));
		$this->payment->redirectToOrder();
	}

	function unconfirmed() {
		return $this->addErrorMessage(_t("OgonePayment.PAYMENTUNCONFIRMED", "Payment is not confirmed."));
		$this->payment->redirectToOrder();
	}

	function statusupdate() {
		return $this->addErrorMessage(_t("OgonePayment.STATUSUPDATEDOFFLINE", "Payment status is updated offline."));
		$this->payment->redirectToOrder();
	}


	function cancel() {
		return $this->addErrorMessage(_t("OgonePayment.PAYMENTCANCELLED", "Payment cancelled by customer."));
		$this->payment->redirectToOrder();
	}

	function back() {
		$this->cancel();
		$this->payment->redirectToOrder();
	}

	protected function checkShaOut() {
		if(isset($_REQUEST["SHASIGN"])) {
			$presentedSha = $_REQUEST["SHASIGN"];
			ksort($_REQUEST);
			$shaInput = '';
			foreach($_REQUEST as $key => $value) {
				$key = strtoupper($key);
				$value = ($value);
				if(in_array($key, $this->shaOutVariables())) {
					if($key != "SHASIGN") {
						if($value != null && $value != '') {
							$shaInput .= $key.'='.$value.OgonePayment::get_sha_passphrase();
						}
					}
				}
			}
			$calculatedSha = sha1($shaInput);
			if($presentedSha == $calculatedSha) {
				return true;
			}
			else {
				//To do: FIX FIX FIX
				return true;
				die("check sha");
				$this->addErrorMessage(_t("OgonePayment.SECURITYERROR", "Security phrase does not match."));
			}
		}
		$this->addErrorMessage(_t("OgonePayment.SECURITYERROR", "No security phrase provided."));
	}

	protected function addErrorMessage($msg) {
		if($this->payment instanceOf Payment) {
			$this->payment->Status = "Failure";
			$this->payment->ExceptionError = $msg;
			$this->payment->Message = $msg;
			$this->payment->write();
			return;
		}
		return array();
	}

	function test() {
		return "ok";
	}

	function shaOutVariables() {
		return array(
			'AAVADDRESS', 'AAVCHECK', 'AAVZIP', 'ACCEPTANCE', 'ALIAS', 'AMOUNT', 'BIN', 'BRAND', 'CARDNO', 'CCCTY', 'CN', 'COMPLUS', 'CREATION_STATUS', 'CURRENCY',
			'CVCCHECK', 'DCC_COMMPERCENTAGE', 'DCC_CONVAMOUNT', 'DCC_CONVCCY', 'DCC_EXCHRATE', 'DCC_EXCHRATESOURCE', 'DCC_EXCHRATETS', 'DCC_INDICATOR', 'DCC_MARGINPERCENTAGE',
			'DCC_VALIDHOURS', 'DIGESTCARDNO', 'ECI', 'ED', 'ENCCARDNO', 'IP', 'IPCTY', 'NBREMAILUSAGE', 'NBRIPUSAGE', 'NBRIPUSAGE_ALLTX', 'NBRUSAGE', 'NCERROR', 'ORDERID',
			'PARAMPLUS', 'PAYID', 'PM', 'SCO_CATEGORY', 'SCORING', 'STATUS', 'SUBSCRIPTION_ID', 'TRXDATE', 'VC'
		);
	}


}

