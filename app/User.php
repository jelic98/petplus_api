<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

class User extends Model implements
    AuthenticatableContract,
    AuthorizableContract
{
    use Authenticatable, Authorizable, SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'firstName', 
		'lastName',
		'email',
		'type',
		'image',
		'location',
		'interval',
		'price',
		'grade',
		'password',
		'accessToken',
		'fcmToken'
	];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
		'password',
		'fcmToken',
		'accessToken'
    ];

	protected $dates = ['deleted_at'];
	
	public function interval() {
		return $this->belongsTo('App\Interval', 'interval');
	}
}
