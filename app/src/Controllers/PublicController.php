<?php
namespace VUOX\Controllers;

use VUOX\Models\Item;
use Respect\Validation\Validator as v;


class PublicController extends Controller
{
	public function getHome($request, $response)
	{
		$items = Item::all();

		/* Disable TwigView
		return $this->view->render($response, 'home.twig', [
			'items' => $items->toArray()
		]);*/

		// Use custom Template engine
		return $response->withTemplate('home.php', [
			'items' => $items->toArray()
		]);
	}
}