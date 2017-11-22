<?php
namespace VUOX\Controllers;

class TemplateController extends Controller
{
	public function getTemplate($request, $response)
	{
		return $response->withTemplate('default.php', [
			'firstName' => 'Jake',
			'lastName' => 'See',
		]);
	}
}
