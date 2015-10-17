<?php

require_once(dirname(__DIR__).'/vendor/autoload.php');

use SimpleSQS\SimpleSQS;

$sqs = new SimpleSQS(array(
	'profile'=>'default',
	'region'=>'us-west-2',
	'error'=>function($result){
		throw new Exception($result);
	}
));

$sqs->createQueue('mysamplequeue');
