<?php
namespace VUOX\Components\Validation;

use VUOX\Middlewares\SessionMiddleware;
use Respect\Validation\Exceptions\NestedValidationException;

class Validator
{
	protected $errors;

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
				$this->errors[$field] = $e->getMessages();
			}
		}

		return $this;
	}

	public function failed()
	{
		return !empty($this->errors);
	}

	public function getErrors()
	{
		return $this->errors;
	}
}
