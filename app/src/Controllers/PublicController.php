<?php
namespace VUOX\Controllers;

use VUOX\Models\Item;
use Respect\Validation\Validator as v;


class PublicController extends Controller
{
	public function getHome($request, $response)
	{
		$items = Item::all();

		return $this->view->render($response, 'home.twig', [
			'items' => $items->toArray()
		]);
	}
}