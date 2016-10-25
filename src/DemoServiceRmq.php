<?php

namespace PhpScotland2016\Demo\Service\Impls;

use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Connection\AMQPStreamConnection;

use PhpScotland2016\Demo\Service\Interfaces\DemoServiceRequest;
use PhpScotland2016\Demo\Service\Interfaces\DemoServiceResponse;
use PhpScotland2016\Demo\Service\Interfaces\DemoServiceInterface;

class DemoServiceRmq implements DemoServiceInterface
{
	public function handleRequest(DemoServiceRequest $request) {
		$logs = array();
		try {
			$session_id = $request->getParam("session_id", null);
			if(is_null($session_id)) {
				throw new \Exception("No sessionid provided");
			}
			$rmq_ready = false;
			// While loop to ensure RabbitMQ demo docker has started.
			while(!$rmq_ready) {
				try {
					$conn = new AMQPStreamConnection(
							$_ENV["RMQ_HOST"],
							$_ENV["RMQ_PORT"],
							$_ENV["RMQ_USER"],
							$_ENV["RMQ_PASS"]
					);
					$rmq_ready = true;
				}
				catch(\Exception $e) {
					sleep(3);
				}
			}
			$chan = $conn->channel();
			$chan->queue_declare($_ENV["RMQ_QUEUE"],
					false, // passive
					false, // durable
					false, // exclusive
					true   // auto_delete
			);
			$msg = new AMQPMessage($request->get());
			$chan->basic_publish($msg, "", $_ENV["RMQ_QUEUE"]);
			$chan->close();
			$conn->close();
		}
		catch(\Exception $e) {
			error_log("Exception: ". $e->getMessage());
		}
		
		$message = $request->getAsArray();
		$message['result'] = 0;
		$message['msg'] = 'See websocket response';
		return new DemoServiceResponse($message);
	}
}

