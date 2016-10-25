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
			$session_id = isset($_GET['sessionid']) ? (int)$_GET['sessionid'] : null;
			if(is_null($session_id)) {
				throw new \Exception("No sessionid provided");
			}
			$wait = isset($_GET['wait']) ? (int)$_GET['wait'] : 1;
			$times = isset($_GET['times']) ? (int)$_GET['times'] : 1;
			$req = new DemoServiceRequest;
			$req->setParam("route", "rmq");
			$req->setParam("wait_for", $wait);
			$req->setParam("session_id", $session_id);
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
			while($times > 0) {
				$msg = new AMQPMessage($req->get());
				$chan->basic_publish($msg, "", $_ENV["RMQ_QUEUE"]);
				$times--;
				$counter++;
			}
			$chan->close();
			$conn->close();
		}
		catch(\Exception $e) {
		}
		
		$message = $request->getAsArray();
		$message['result'] = 0;
		$message['msg'] = 'See websocket response';
		return new DemoServiceResponse($message);
	}
}

