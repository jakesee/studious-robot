<?php
namespace VUOX\Controllers;

use VUOX\Models\Order;
use Respect\Validation\Validator as v;

class AdminController extends Controller
{
	public function __invoke($request, $response, $next)
	{
		// only let guests access this controller
		if(!$this->session->isValid())
			return $response->withStatus(403);
		
		return $next($request, $response);
	}

	public function getOrders($request, $response)
	{
		$error = $this->validator->validate($request->getAttributes(), [
			'status' => v::oneOf(V::equals('pending'),V::equals('processing'),V::equals('active'))
		]);

		if($error !== false)
			return $response->withStatus('404');

		$status = $request->getAttribute('status');
		$orders = Order::with('contact')->where('status', $status)->get();

		return $this->view->render($response, 'orders.twig', [
			'orders' => $orders->toArray()
		]);
	}

	public function getOrder($request, $response)
	{
		$order = Order::with('contact','orderlines')->find($request->getAttribute('id'));

		return $this->view->render($response, 'order-manage.twig', [
			'order' => $order->toArray()
		]);
	}
}