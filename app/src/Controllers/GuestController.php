<?php
namespace VUOX\Controllers;

use VUOX\Models\User;
use Respect\Validation\Validator as v;

class GuestController extends Controller
{
	public function __invoke($request, $response, $next)
	{
		// only let guests access this controller
		if($this->session->isValid())
			return $response->withStatus(403);
		
		return $next($request, $response);
	}

	public function getSignUp($request, $response)
	{
		return $this->view->render($response, 'signup.twig');
	}

	public function postSignUp($request, $response)
	{
		$validator = $this->validator->validate($request->getParams(), [
			'name' => v::notEmpty()->length(3, 60),
			'email' => v::notEmpty()->email()->emailAvailable(),
			'password' => v::notEmpty()->length(8),
		]);

		if($validator->failed())
		{
			$this->session->fillForm($request->getParams());
			return $this->view->render($response, 'signup.twig');
		}

		User::create([
			'name' => $request->getParam('name'),
			'email' => $request->getParam('email'),
			'password' => password_hash($request->getParam('password'), PASSWORD_DEFAULT, ['cost' => 10]),
		]);

		$this->flash->addMessage('info', 'You have been signed up!');

		// sign in
		$this->session->authenticate($request->getParam('email'), $request->getParam('password'));

		return $response->withRedirect($this->router->pathFor('me.dashboard'));
	}

	public function getSignIn($request, $response)
	{
		return $this->view->render($response, 'signin.twig');
	}

	public function postSignIn($request, $response)
	{
		$auth = $this->session->authenticate($request->getParam('email'), $request->getParam('password'));

		if(!$auth)
		{
			$this->flash->addMessage('danger', 'Unable to sign in, credentials mismatched.');
			return $response->withRedirect($this->router->pathFor('guest.signin'));
		}
		else
		{
			return $response->withRedirect($this->router->pathFor('me.dashboard'));
		}
	}
}