<?php

Director::addRules(50, array(
	OgonePayment_Handler::$URLSegment . '/$Action/$ID' => 'OgonePayment_Handler'
));