<?php

namespace VUOX\Components\Validation\Rules;

use Respect\Validation\Rules\AbstractRule;
use VUOX\Models\User;

class EmailAvailable extends AbstractRule
{
	protected $id; // user id

	public function __construct($id = 0)
	{
		$this->id = $id;
	}

	public function validate($input)
	{
		if($this->id != 0)
		{
			$user = User::find($this->id);//->first();
			if($user) $email = $user->email;
			if($email === $input) return true;
		}

		return User::where('email', $input)->count() === 0;
	}
}
