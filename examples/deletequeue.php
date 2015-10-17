<?php

require_once(dirname(__DIR__).'/vendor/autoload.php');

use SimpleSQS\SimpleSQS;

$sqs = new SimpleSQS(array(
	'profile'=>'default',
	'region'=>'us-west-2',
	'error'=>function($result){
		var_dump($result->__toString());
	}
));

$sqs->deleteQueue('mysamplequeue');
