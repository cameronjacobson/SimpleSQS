<?php

require_once(dirname(__DIR__).'/vendor/autoload.php');

use SimpleSQS\SimpleSQS;

$sqs = new SimpleSQS(array(
	'profile'=>'default',
	'debug'=>false,
	'region'=>'us-west-2',
	'queue'=>'mysamplequeue',
	'error'=>function($result){
		throw new Exception($result);
	}
));

// send to queue
$id = $sqs->enqueue('Hello World');
echo 'Queued '.$id.PHP_EOL;
sleep(2);

// retrieve from queue
$messages = $sqs->dequeue(1);
foreach($messages as $message){
	list($id,$body) = $message;
	$showid = substr($id,0,20).'...';
	echo 'Received '.$showid.PHP_EOL;
	var_dump($body);

	sleep(1);

	// delete from queue
	$sqs->delete($id);
	echo 'Deleted '.$showid.PHP_EOL;
}


