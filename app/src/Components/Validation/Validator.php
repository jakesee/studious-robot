<?php
namespace VUOX\Components\Validation;

use VUOX\Middlewares\SessionMiddleware;
use Respect\Validation\Exceptions\NestedValidationException;

class Validator
{
	protected $danger;

	protected $session;

	public function __construct(SessionMiddleware $session)
	{
		$this->session = $session;
	}

	public function validate($inputs, $rules)
	{
		foreach($rules as $field => $rule)
		{
			try
			{
				// $valid = $rule->validate($input[$key]); // return boolean
				// $valid = $rule->check($input[$key]); // throws exception
				$rule->setName(ucfirst($field))->assert($inputs[$field]);
			}
			catch (NestedValidationException $e)
			{
				$this->danger[$field] = $e->getMessages();
			}
		}

		// save errors to session
		if(!empty($this->danger))
		{
			$this->session->add('danger', $this->danger);
			return $this->danger;
		}

		return false;
	}

	public function failed()
	{
		return !empty($this->danger);
	}
}