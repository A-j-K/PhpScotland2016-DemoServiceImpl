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
			$session_id = $request->getParam("sessionid", null);
			if(is_null($session_id)) {
				throw new \Exception("No sessionid provided");
			}
			$times = $request->getParam("times", 1);
			$wait_for = $request->getParam("wait_for", 1);
			$conn = "tcp://".$_ENV["ZMQ_BROKER"].":" . $_ENV["ZMQ_BROKER_FRONT_PORT"];
			$zmq = $context->getSocket(\ZMQ::SOCKET_REQ, null);
			$zmq->connect($conn);
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

