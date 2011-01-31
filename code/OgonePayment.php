<?php

/**
 */
class OgonePayment extends Payment {

	// Ogone Information

	protected static $privacy_link = 'http://www.ogone.com/en/About%20Ogone/Privacy%20Policy.aspx';
	protected static $logo = 'mysite/images/ogone.gif';

	// URLs

	protected static $url = 'https://secure.ogone.com/ncol/prod/orderstandard.asp';
	protected static $test_url = 'https://secure.ogone.com/ncol/test/orderstandard.asp';

	// Test Mode

	protected static $test_mode = false;
	static function set_test_mode($test_mode) {self::$test_mode = $test_mode;}

	// Payment Informations

	protected static $account_pspid;
	static function set_account_pspid($account_pspid) {self::$account_pspid = $account_pspid;}

	protected static $sha_passphrase;
	static function set_sha_passphrase($sha_passphrase) {self::$sha_passphrase = $sha_passphrase;}

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

	function getPaymentFormFields() {
		$logo = '<img src="' . self::$logo . '" alt="Credit card payments powered by Ogone "/>';
		$privacyLink = '<a href="' . self::$privacy_link . '" target="_blank" title="Read Ogone\'s privacy policy">' . $logo . '</a><br/>';
		return new FieldSet(
			new LiteralField('OgoneInfo', $privacyLink),
			new DropdownField(
				'OgoneMethod',
				'',
				array(
					'CC' => 'Credit Card',
					'ID' => 'Ideal',
					'PP' => 'Paypal'
				)
			)
		);
	}

	function getPaymentFormRequirements() {return null;}

	function processPayment($data, $form) {
		$page = new Page();

		$page->Title = 'Redirection to Ogone...';
		$page->Logo = '<img src="' . self::$logo . '" alt="Payments powered by Ogone"/>';
		$page->Form = $this->OgoneForm();

		$controller = new Page_Controller($page);

		$form = $controller->renderWith('PaymentProcessingPage');

		return new Payment_Processing($form);
	}

	function OgoneForm() {
		Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery.js');

		// 1) Main Informations

		$fields = '';
		$order = $this->Order();
		$member = $order->Member();
		$this->Amount = $order->Total() + 0;
		$this->write();

		// 2) Main Settings

		$url = self::$test_mode ? self::$test_url : self::$url;
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

		// 3) Redirection Informations
		
		$redirections = array('accept', 'decline');
		foreach($redirections as $redirection) {
			$inputs["{$redirection}url"] = Director::absoluteBaseURL() . OgonePayment_Handler::redirect_link($redirection, $order, $this);
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
				if($value && $value != '') $joinInputs[] = "$input=$value";
			}
			$sha = implode(self::$sha_passphrase, $joinInputs) . self::$sha_passphrase;
			$inputs['SHASIGN'] = sha1($sha);
		}

		// 6) Form Creation

		foreach($inputs as $name => $value) {
			$ATT_value = Convert::raw2att($value);
			$fields .= "<input type=\"hidden\" name=\"$name\" value=\"$ATT_value\"/>";
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
					//jQuery("input[type='submit']").hide();
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
		return self::$URLSegment . "/$action?order=$order->ID&payment=$payment->ID";
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
		if(isset($_REQUEST['order']) && $orderID = $_REQUEST['order']) {
			$this->order = DataObject::get_by_id('Order', $orderID);
		}
		if(isset($_REQUEST['payment']) && $paymentID = $_REQUEST['payment']) {
			$this->payment = DataObject::get_by_id('OgonePayment', $paymentID);
		}
		if(! $this->order) $errors[] = 'Order';
		if(! $this->payment) $errors[] = 'Payment';
		if(isset($errors)) {
			echo '<p>' . implode(' and ', $errors) . ' not found</p>';
			die;
		}
	}
	
	function accept() {
		$status = $_REQUEST['STATUS'];
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
		$this->payment->write();
		$this->payment->redirectToOrder();
	}
	
	function decline() {
		$status = $_REQUEST['STATUS'];
		if($status <= 2) {
			$this->payment->Status = 'Failure';
			$this->payment->write();
		}
		$this->payment->redirectToOrder();
	}
}
