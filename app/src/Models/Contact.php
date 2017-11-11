<?php
namespace VUOX\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
	protected $fillable = [
		'name',
		'company',
		'address1',
		'address2',
		'address3',
		'phone',
		'email'
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function orders()
	{
		return $this->hasMany(Order::class);
	}
}