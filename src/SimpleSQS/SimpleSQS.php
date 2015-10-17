<?php

namespace SimpleSQS;

use \Aws\Sqs\SqsClient;

/**
 *  https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-sqs-2012-11-05.html
 */

class SimpleSQS
{
	private $client;
	private $queue;
	private $queuename;
	private $visibility;
	private $wait;
	private $debug;

	public function __construct(array $params){
		$this->errorhandler = $params['error'];

		try{
			$this->client = new SqsClient(array(
				'profile'=> empty($params['profile']) ? 'default' : $params['profile'],
				'region'=>$params['region'],
				'version'=>'2012-11-05',
				'debug'=>empty($params['debug']) ? false : true
			));
			if(!empty($params['queue'])){
				$this->queuename = $params['queue'];
				$this->queue = $this->getQueueUrl($params['queue']);
			}
			$this->debug = $params['debug'];
			$this->visibility = empty($params['visibility']) ? 60 : $params['visibility'];
			$this->wait = empty($params['wait']) ? 2 : $params['wait'];
		}
		catch(\Exception $e){
			$this->errorhandler->__invoke($e);
		}
	}

	private function getQueueUrl($name){
		$res = $this->client->getQueueUrl(array(
			'QueueName'=>$name
		));
		return $res->get('QueueUrl');
	}

	public function dequeue($num){
		try{
			$result = $this->client->receiveMessage(array(
				'MaxNumberOfMessages' => empty($num) ? 1 : $num,
				'QueueUrl' => $this->queue,
				'VisibilityTimeout'=>$this->visibility,
				'WaitTimeSeconds'=>$this->wait
			));
			$messages = $result->get('Messages');
			if(empty($messages)){
				$this->errorhandler->__invoke($result);
			}
			else{

				if($this->debug){
					return $messages;
				}
				$msgs = array();
				foreach($messages as $message){
					$msgs[] = array(
						$message['ReceiptHandle'],
						$message['Body']
					);
				}
				return $msgs;
			}
		}
		catch(\Exception $e){
			$this->errorhandler->__invoke($e);
		}
	}

	public function enqueue($message, $delay = 0){
		try{
			$result = $this->client->sendMessage(array(
				'QueueUrl' => $this->queue,
				'MessageBody' => $message,
				'DelaySeconds'=>$delay
			));
			$id = $result->get('MessageId');
			if(empty($id)){
				$this->errorhandler->__invoke($result);
			}
			return $this->debug ? $result : $id;
		}
		catch(\Exception $e){
			$this->errorhandler->__invoke($e);
		}
	}

	public function delete($id){
		try{
			$this->client->deleteMessage(array(
				'QueueUrl'=>$this->queue,
				'ReceiptHandle'=>$id
			));
		}
		catch(\Exception $e){
			$this->errorhandler->__invoke($e);
		}
	}

	public function createQueue($name){
		try{
			$result = $this->client->createQueue(array(
				'QueueName'=>$name
			));
			$this->queuename = $name;
			$this->queue = $result->get('QueueUrl');
			return $result->get('QueueUrl');
		}
		catch(\Exception $e){
			$this->errorhandler->__invoke($e);
		}
	}

	public function deleteQueue($name = null){
		try{
			$this->client->deleteQueue(array(
				'QueueUrl' => $this->getQueueUrl(empty($name) ? $this->queuename : $name)
			));
		}
		catch(\Exception $e){
			$this->errorhandler->__invoke($e);
		}
	}

	private function E($value){
		error_log(var_export($value,true));
	}
}
