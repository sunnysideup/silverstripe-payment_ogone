<?php

Director::addRules(50, array(
    OgonePayment_Handler::get_url_segment() . '//$Action/$ID/$OtherID' => 'OgonePayment_Handler'
));

// copy to myste/_config and set as required...
// __________________________________ START OGONE PAYMENT MODULE CONFIG __________________________________
//DO NOT FORGET...
//Payment::set_site_currency('NZD');
//Payment::set_supported_methods(array('OgonePayment' => 'Ogone Payment'));

// MUST SET
//if(Director::isLive()) {
    //OgonePayment::set_test_mode(false);
    //OgonePayment::set_sha_passphrase("hello");
//}
//else {
    //OgonePayment::set_test_mode(true);
    //OgonePayment::set_sha_passphrase("hello");
//}
//OgonePayment::set_account_pspid("myaccountcode");


//HIGLY RECOMMENDED TO SET
//OgonePayment::set_logos_to_show_array(array("visa", "master-card", "maestro", "iDeal", "paypal"); // may also be an associative array with code and filename , e.g. "visa" => "mysite/images/MyVisaLogo.gif"
//OgonePayment::set_payment_options_array(array('CredtiCard' => 'Credit Card','iDeal' => 'iDeal','PayPal' => 'Paypal'));
    //OgonePayment::add_payment_option($key, $title);
    //OgonePayment::remove_payment_option($key) ;

//  FORMATTING
//OgonePayment::set_page_title("");
//OgonePayment::set_back_color();
//OgonePayment::set_text_color();
//OgonePayment::set_table_back_color();
//OgonePayment::set_table_text_color();
//OgonePayment::set_button_back_color();
//OgonePayment::set_button_text_color();
//OgonePayment::set_font_type();
//OgonePayment::set_image_url();
// --- OR ----
//OgonePayment::set_template();

// UNLIKELY TO NEED CHANGING
//OgonePayment::set_privacy_link('http://www.ogone.com/en/About%20Ogone/Privacy%20Policy.aspx');
//OgonePayment::set_url('https://secure.ogone.com/ncol/prod/orderstandard.asp');
//OgonePayment::set_test_url('https://secure.ogone.com/ncol/test/orderstandard.asp');
// __________________________________ END OGONE PAYMENT MODULE CONFIG __________________________________
