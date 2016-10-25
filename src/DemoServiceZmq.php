<?php

namespace PhpScotland2016\Demo\Service\Impls;

use PhpScotland2016\Demo\Service\Interfaces\DemoServiceRequest;
use PhpScotland2016\Demo\Service\Interfaces\DemoServiceResponse;
use PhpScotland2016\Demo\Service\Interfaces\DemoServiceInterface;

class DemoServiceZmq implements DemoServiceInterface
{
	public function handleRequest(DemoServiceRequest $request) {
		$context = new \ZMQContext();
		try {
			$session_id = isset($_GET['sessionid']) ? (int)$_GET['sessionid'] : null;
			if(is_null($session_id)) {
				throw new \Exception("No sessionid provided");
			}
			$conn = "tcp://".$_ENV["ZMQ_BROKER"].":" . $_ENV["ZMQ_BROKER_FRONT_PORT"];
			$zmq = $context->getSocket(\ZMQ::SOCKET_REQ, null);
			$zmq->connect($conn);
			$wait = isset($_GET['wait']) ? (int)$_GET['wait'] : 1;
			$times = isset($_GET['times']) ? (int)$_GET['times'] : 1;
			while($times > 0) {
				$req = new DemoServiceRequest;
				$req->setParam("route", "zmq");
				$req->setParam("wait_for", $wait);
				$req->setParam("session_id", $session_id);
				$zmq->send($req->get(), \ZMQ::MODE_NOBLOCK);
				$res = $zmq->recv();
				$times--;
				$counter++;
			}
		}
		catch(\Exception $e) {
		}
		
		$message = $request->getAsArray();
		$message['result'] = 0;
		$message['msg'] = 'See websocket response';
		return new DemoServiceResponse($message);
	}
}

