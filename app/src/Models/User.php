<?php

namespace VUOX\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password'];

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }
}
