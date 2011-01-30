<?php


class OgonePayment extends Payment{

	static $db = array(
		'Token' => 'Varchar(30)',
		'PayerID' => 'Varchar(30)',
		'TransactionID' => 'Varchar(30)'
	);


	//main processing function
	function processPayment($data, $form) {


		$paymenturl = "";

		$this->Status = "Pending";
		$this->write();

		Director::redirect($paymenturl); //redirect to payment gateway
		return new Payment_Processing();

		$this->Message = "Error";
		$this->Status = 'Failure';
		$this->write();

		return new Payment_Failure($this->Message);
	}
	function confirmPayment(){


		$this->write();

	}


	function getPaymentFormFields() {
		$logo = '<img src="' . self::$logo . '" alt="Credit card payments powered by Ogone"/>';
		$privacyLink = '<a href="' . self::$privacy_link . '" target="_blank" title="Read Ogone\'s privacy policy">' . $logo . '</a><br/>';
		return new FieldSet(
			new LiteralField('OgoneInfo', $privacyLink),
			new LiteralField(
				'OgonePaymentsList',

				//TODO: set what methods are avaialble
				'<img src="payment/images/payments/methods/visa.jpg" alt="Visa"/>' .
				'<img src="payment/images/payments/methods/mastercard.jpg" alt="MasterCard"/>' .
				'<img src="payment/images/payments/methods/american-express.gif" alt="American Express"/>' .
				'<img src="payment/images/payments/methods/discover.jpg" alt="Discover"/>' .
				'<img src="payment/images/payments/methods/paypal.jpg" alt="PayPal"/>'
			)
		);
	}

	function getPaymentFormRequirements() {return null;}

}

class OgonePayment_Handler extends Controller{

	protected $payment = null; //only need to get this once

	static $allowed_actions = array(
		'confirm',
		'cancel'
	);

	function payment(){
		if($this->payment){
			return $this->payment;
		}

	}

	function confirm($request){

		//TODO: pretend the user confirmed, and skip straight to results. (check that this is allowed)
		//TODO: get updated shipping details from paypal??

		if($payment = $this->payment()){

			if($pid = Controller::getRequest()->getVar('PayerID')){
				$payment->PayerID = $pid;
				$payment->write();

				$payment->confirmPayment();
			}

		}else{
			//something went wrong?	..perhaps trying to pay for a payment that has already been processed
		}

		$this->doRedirect();
		return;
	}

	function cancel($request){

		if($payment = $this->payment()){

			//TODO: do API call to gather further information

			$payment->Status = "Failure";
			$payment->Message = "User cancelled";
			$payment->write();
		}

		$this->doRedirect();
		return;
	}

	function doRedirect(){

		$payment = $this->payment();
		if($payment && $obj = $payment->PaidObject()){
			Director::redirect($obj->Link());
			return;
		}

		Director::redirect(Director::absoluteURL('home',true)); //TODO: make this customisable in Payment_Controllers
		return;
	}
}
