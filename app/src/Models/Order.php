<?php
namespace VUOX\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
	protected $fillable = [
		'contact_id',
		'ordered_at',
		'instructions',
		'details'
	];

	protected $attributes = [
		'status' => 'pending'
	];

	public function orderlines()
	{
		return $this->hasMany(Orderline::class);
	}

	public function contact()
	{
		return $this->belongsTo(Contact::class);
	}
}