<?php

/**
 */
class OgonePayment extends Payment {

	static $db = array(
		"PM" => "Varchar(100)",
		"Brand" => "Varchar(255)",
		"ACCEPTANCE" => "Varchar(255)"
	);

	// Ogone Information

	protected static $payment_options_array = array();
		static function set_payment_options_array($a) {self::$payment_options_array = $a;}
		static function add_payment_option($key, $title) {self::$payment_options_array[$key] = $title;}
		static function remove_payment_option($key) {unset(self::$payment_options_array[$key]);}


	// Ogone Information

	protected static $privacy_link = 'http://www.ogone.com/en/About%20Ogone/Privacy%20Policy.aspx';
		static function set_privacy_link($v) {self::$privacy_link = $v;}

	protected static $logo = 'mysite/images/ogone.gif';
		static function set_logo($v) {self::$logo = $v;}

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

	function getPaymentFormFields() {
		if(!(self::$payment_options_array) || !count(self::$payment_options_array))  {
			user_error("no payment options have been set", E_USER_NOTICE);
		}
		$logo = '<img src="' . self::$logo . '" alt="Payments powered by Ogone "/>';
		$privacyLink = '<div class="field nolabel readonly"><div class="middleColumn"><a href="' . self::$privacy_link . '" rel="external" title="Read Ogone\'s privacy policy">' . $logo . '</a></div></div>';

		return new FieldSet(
			new LiteralField('OgoneInfo', $privacyLink),
			new OptionsetField(
				'OgoneMethod',
				'',
				self::$payment_options_array
			)
		);
	}

	function getPaymentFormRequirements() {return null;}

	function processPayment($data, $form) {
		$page = new Page();

		$page->Title = 'Redirection to Ogone...';
		$page->Logo = '<img src="' . self::$logo . '" alt="Payments powered by Ogone" />';
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
		$inputs['PM'] = isset($_REQUEST["OgoneMethod"]) ? $_REQUEST["OgoneMethod"] : "CC";
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

		$redirections = array('accept', 'back', 'cancel','decline', 'exception');

		foreach($redirections as $redirection) {
			$inputs[strtoupper("{$redirection}url")] = Director::absoluteBaseURL() . OgonePayment_Handler::redirect_link($redirection, $order, $this);
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
				$joinInputs[] = strtoupper($input)."=$value";
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

	static $URLSegment = 'ogone';

	static function redirect_link($action, $order, $payment) {
		return self::$URLSegment . "/".$action."/".$order->ID."/".$payment->ID."/";
	}

	protected $order, $payment;

	/*
	 * http://staging.shop.avelon.me.xplainhosting.com/ogone/accept?
	 * order=140&
	 * payment=19&
	 * orderID=140&
	 * currency=EUR&
	 * amount=149&
	 * PM=CreditCard&
	 * ACCEPTANCE=test123&
	 * STATUS=9&
	 * CARDNO=XXXXXXXXXXXX1111&
	 * ED=0921&
	 * CN=Default+Admin&
	 * TRXDATE=01%2F31%2F11&
	 * PAYID=9296689&
	 * NCERROR=0&
	 * BRAND=VISA&
	 * IPCTY=NZ&
	 * CCCTY=US&
	 * ECI=7&
	 * CVCCheck=NO&
	 * AAVCheck=NO&
	 * VC=NO&
	 * IP=125.237.65.47&
	 * SHASIGN=E62DF983CDD8F2AE90CA566FDB1FF99A255969B7
	 */
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
		if($payment->OrderID != $order->ID) {
			echo '<p>Not enough variables provided.</p>';
			die;
		}
		if(isset($_REQUEST["ACCEPTANCE"])) {
			$this->payment->ACCEPTANCE = $_REQUEST["ACCEPTANCE"];
			$this->payment->write();
		}
		if(isset($_REQUEST["PM"])) {
			$this->payment->PM = $_REQUEST["PM"];
			$this->payment->write();
		}
		if(isset($_REQUEST["NCERROR"])) {
			$this->payment->ExceptionError = $_REQUEST["NCERROR"];
			$this->payment->write();
		}
	}

	function accept() {
		$status = $_REQUEST['STATUS'];
		//CHECK for SHA-OUT!!!
		switch($status) {
			case 5 :
			case 9 : {
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
		return $this->addErrorMessageAndRedirect(_t("OgonePayment.PAYMENTDECLINED", "Payment declined."));
	}

	function exception() {
		return $this->addErrorMessageAndRedirect(_t("OgonePayment.PAYMENTERROR", "Payment error."));
	}


	function cancel() {
		return $this->addErrorMessageAndRedirect(_t("OgonePayment.PAYMENTCANCELLED", "Payment cancelled by customer."));
	}

	function back() {
		return $this->cancel();
	}

	protected function checkShaOut() {
		if(isset($_REQUEST["SHASIGN"])) {
			$presentSha = $_REQUEST["SHASIGN"];
			ksort($_REQUEST);
			foreach($_REQUEST as $key => $value) {
				$shouldBeShaInput = '';
				if(in_array($key, array('ACCEPTANCE', 'AMOUNT','BRAND','CARDNO','CURRENCY','NCERROR','ORDERID','PAYID','PM','STATUS'))) {
					$shouldBeShaInput = strtoupper($key).'='.$value.OgonePayment::get_sha_passphrase();
				}
			}
			$shouldBeSha = sha1($shouldBeShaInput);
			if($presentSha == $shouldBeSha) {
				return true;
			}
		}
		$this->addErrorMessageAndRedirect(_t("OgonePayment.SECURITYERROR", "Security Error"));
	}

	protected function addErrorMessageAndRedirect($msg) {
		if($this->payment instanceOf Payment) {
			$this->payment->Status = "Failure";
			$this->payment->ExceptionError = $msg;
			$this->payment->Message = $msg;
			$this->payment->write();
			$this->payment->redirectToOrder();
			return;
		}
		return array();
	}

	function test() {
		return "ok";
	}

}

