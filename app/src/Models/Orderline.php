<?php
namespace VUOX\Models;

use Illuminate\Database\Eloquent\Model;

class Orderline extends Model
{
	protected $fillable = [
		'order_id',
		'summary',
		'type',
		'is_recurring',
		'invoice_text',
		'price',
		'quantity',
		'active_start',
		'active_end',
		'next_start',
		'next_end',
		'qty_unit'
	];

	protected $attributes = [
		'status' => 'pending',
		'quantity' => 1
	];

	protected $appends = [
		'qty_unit'
	];

	protected $qty_unit;
	
	public function order()
	{
		return $this->belongsTo(Order::class);
	}

	public function getQtyUnitAttribute()
	{
		return $this->qty_unit;
	}

	public function setQtyUnitAttribute($val)
	{
		$this->qty_unit = $val;
	}
}