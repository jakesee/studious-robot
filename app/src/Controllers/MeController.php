<?php
namespace VUOX\Controllers;

use VUOX\Models\User;
use VUOX\Models\Contact;
use VUOX\Models\Item;
use VUOX\Models\Orderline;
use VUOX\Models\Order;
use \Respect\Validation\Validator as v;
use \Illuminate\Database\Capsule\Manager as DB;

class MeController extends Controller
{
	public function __invoke($request, $response, $next)
	{
		if(!$this->session->isValid())
			return $response->withStatus(403);
		
		return $next($request, $response);
	}

	public function getProfile($request, $response)
	{
		$user = $this->session->getUser();

		$this->session->fillForm([
			'name' => $user->name,
			'email' => $user->email,
		]);

		return $response->withTemplate('profile.twig', [
			'user' => $user->toArray(),
			'contacts' => $user->contacts,
		]);
	}

	public function postProfile($request, $response)
	{
		$user = $this->session->getUser();

		$validator = $this->validator->validate($request->getParams(), [
			'name' => v::notEmpty()->length(3, 60),
			'email' => v::notEmpty()->email()->emailAvailable($user->id),
			'confirm_email' => v::equals($request->getParam('email')),
		]);

		if($validator->failed())
		{
			$this->flash->addMessage('danger', 'see errors');
			$this->session->fillForm($request->getParams());
			return $this->view->render($response, 'profile.twig');
		}

		$user->name = $request->getParam('name');
		$user->email = $request->getParam('email');
		$user->save();

		$this->flash->addMessage('success', 'Profile information updated successfully.');

		return $response->withRedirect($this->router->pathFor('me.profile'));
		// return $response->withHeader('Location', $this->router->pathFor('me.profile'));
	}

	public function postPassword($request, $response)
	{
		$user = $this->session->getUser();

		if(!$user) // unable to find logged in user; i.e. something wrong with session, so abort everything and exit
		{
			$this->session->logout();
			return $response->withStatus(403);
		}

		$validator = $this->validator->validate($request->getParams(), [
			'password_old' => v::notEmpty()->matchesPassword($user->password),
			'password' => v::notEmpty()->length(8)
		]);

		if($validator->failed())
		{
			$this->flash->addMessage('danger', 'Password mismatched. Unable to change password.');
		}
		else
		{
			// actually change password
			$user->password = password_hash($request->getParam('password'), PASSWORD_DEFAULT);
			$user->save();
			$this->flash->addMessage('success', 'Password changed sucessfully.');
		}

		return $response->withRedirect($this->router->pathFor('me.profile'));
	}

	public function getSignOut($request, $response)
	{
		$this->session->logout();

		$this->flash->addMessage('success', 'You have signed out safely now! See you again!');

		return $response->withRedirect($this->router->pathFor('public.home'));
	}

	public function getDashboard($request, $response)
	{
		return $response->withRedirect($this->router->pathFor('public.home'));
	}

	public function getServices($request, $response)
	{
		$user = $this->session->getUser();
		$user = User::with('contacts.orders.orderlines')->find($user->id);
		$contacts = $user->contacts->toArray();

		return $this->view->render($response, 'services.twig', [
			'contacts' => $contacts
		]);
	}

	public function getAccountBilling($request, $response)
	{
		return $response->withRedirect($this->router->pathFor('public.home'));
	}

	public function postAccount($request, $response)
	{
		return $response->withRedirect($this->router->pathFor('public.home'));
	}

	public function getContacts($request, $response)
	{
		$user = $this->session->getUser();

		return $this->view->render($response, 'contacts.twig', [
			'contacts' => $user->contacts,
		]);
	}

	public function editContact($request, $response)
	{
		$user = $this->session->getUser();

		// check that the current user can edit the requested contact
		$id = $request->getAttribute('id');
		$contact = $user->contacts->find($id);
		if($contact === null)
			return $response->withStatus(403);

		return $this->view->render($response, 'contact-edit.twig', [
			'form' => $contact->toArray()
		]);
	}

	public function postContact($request, $response)
	{
		$error = $this->validator->validate($request->getParams(), [
			'name' => v::notEmpty()->length(3),
			'address1' => v::notEmpty(),
			'phone' => v::notEmpty()->length(8),
			'email' => v::notEmpty()->email(),
		]);

		if($error !== false)
		{
			return $this->view->render($response, 'contact.twig', [
				'error' => $error,
				'form' => $request->getParams()
			]);
		}
		else
		{
			// add new contact for current logged in user
			$user = $this->session->getUser();
			$contact = new Contact($request->getParams());
			$user->contacts()->save($contact);

			$this->flash->addMessage('success', 'contact added!');

			return $response->withRedirect($this->router->pathFor('me.contacts'));
		}
	}

	public function putContact($request, $response)
	{
		// check that this contact actually belongs to the current user
		$user = $this->session->getUser();
		$contact = $user->contacts()->find($request->getParam('id'));
		if($contact === null)
			return $response->withStatus(403);

		// validate input
		$error = $this->validator->validate($request->getParams(), [
			'name' => v::notEmpty()->length(3),
			'address1' => v::notEmpty(),
			'phone' => v::notEmpty()->length(8),
			'email' => v::notEmpty()->email(),
		]);

		if($error !== false)
		{
			return $this->view->render($response, 'contact-edit.twig', [
				'error' => $error,
				'form' => $request->getParams(),
			]);
		}
		else
		{
			// update the contact
			$contact->fill($request->getParams())->save();

			$this->flash->addMessage('success', 'contact updated!');

			return $response->withRedirect($this->router->pathFor('me.contacts'));
		}
	}

	protected function loadGetCart($request)
	{
		$user = $this->session->getUser();
		$cart = $this->session->get('cart');
		$order = $this->session->get('order');

		return [
			'contacts' => $user->contacts->toArray(),
			'form' => [
				'cart' => $cart,
				'order' => $order
			]
		];
	}

	public function getCart($request, $response)
	{
		return $this->view->render($response, 'cart.twig', $this->loadGetCart($request));
	}

	public function postCartAdd($request, $response)
	{
		$item = Item::find($request->getParam('id'));
		if($item === null)
			return $response->withStatus(404);

		$orderline = new Orderline($item->toArray());

		$this->session->push('cart', $orderline->toArray());

		$this->flash->addMessage('success', 'added ' . $orderline->summary);

		return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
	}

	public function postCartEmpty($request, $response)
	{
		$this->session->remove('cart');
		$this->session->remove('order');

		$this->flash->addMessage('success', 'Cart is emptied.');
		$response->getBody();
		return $response->withRedirect($request->getServerParam('HTTP_REFERER'));
	}

	public function postCartCheckout($request, $response)
	{
		// this method checks all data in session is valid,
		// and calculate totals
		// otherwise show error message in cart page

		// get the data from session
		$cart = $this->session->get('cart');
		$user = $this->session->getUser();

		// check that cart is not empty
		if(empty($cart))
		{
			$this->flash->addMessage('warning', 'Cart is empty. order not submitted.');
			return $response->withRedirect($this->router->pathFor('me.cart'));
		}
		else
		{
			// calculate totals
			$subtotals = [];
			$grandtotal = 0;
			foreach($cart as $item)
			{
				// check the domain option is entered
				if($item['type'] == 'domain' && empty(trim($item['options'])))
				{
					$this->flash->addMessage('danger', 'Please ensure all domain names are specified for domain registration, and then Update the cart.');
					return $response->withRedirect($this->router->pathFor('me.cart'));
				}

				// calculate totals
				$total = $item['quantity'] * $item['price'];
				$subtotals[] = $total;
				$grandtotal += $total;
			}

			$contacts = Contact::where('user_id', $user->id)->get();

			return $response->setContext(function($request, $response, $data) {
				$this->view->render($response, 'checkout.twig', $data);
			}, [
				'subtotals' => $subtotals,
				'grandtotal' => $grandtotal,
				'contacts' => $contacts->toArray()
			]);
		}
	}

	public function postCartUpdate($request, $response)
	{
		$cart = $this->session->get('cart');
		$order = $this->session->get('order');
		$quantity = $request->getParam('quantity');
		$options = $request->getParam('options');
		foreach($quantity as $index => $value) $cart[$index]['quantity'] = $value;
		foreach($options as $index => $value) $cart[$index]['options'] = $value;
		$order['contact_id'] = $request->getParam('contact_id');
		$order['instructions']  = $request->getParam('instructions');

		$this->session->add('cart', $cart); // add instead of push to replace the cart
		$this->session->add('order', $order);

		$this->flash->addMessage('success', 'Cart updated.');
		
		return $response->withRedirect($this->router->pathFor('me.cart'));
	}

	public function postCartConfirm($request, $response)
	{
		// make sure the contact for the order is set
		$error = $this->validator->validate($request->getParams(), [
			'contact_id' => v::notEmpty()->not(v::Equals(0))
		]);
		if($error !== false)
		{
			$response = $this->postCartCheckout($request, $response);
			$response->context['error'] = $error;
			$response->context['form'] = $request->getParams();
			return $response;
		}

		// make sure the order contact belongs to current logged in user
		$user = $this->session->getUser();
		$contact = $user->contacts()->find($request->getParam('contact_id'));
		if($contact === null)
			return $response->withStatus(403);

		// create the order
		try
		{
			DB::beginTransaction();	

			$ordered_at = new \DateTime();
			$order = new Order();
			$order->ordered_at = $ordered_at->format('Y-m-d H:i:s');
			$order->contact_id = $request->getParam('contact_id');
			$order->instructions = $request->getParam('instructions');
			$order->details = 'No details. To be implemented.';
			// $order->status = 'pending'; // this is set by default
			$order->save();
			$order->orderlines()->createMany($this->session->get('cart'));

			DB::commit();
		}
		catch(\Exception $e)
		{
			DB::rollBack();

			$this->flash->addMessage('danger', $e->getMessage());
			return $response->withRedirect($this->router->pathFor('me.cart'));
		}

		$this->flash->addMessage('success', 'order submitted. we will contact you to complete the order processing');
		$this->session->remove('cart');
		return $response->withRedirect($this->router->pathFor('me.cart'));
	}
}

