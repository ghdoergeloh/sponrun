<?php

namespace App\Domain\Model\Sponsor;

use Illuminate\Database\Eloquent\Model;

class Sponsor extends Model
{

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'firstname', 'lastname',
		'street', 'housenumber',
		'postcode', 'city', 'phone',
		'email', 'run_participation_id', 'user_id'
	];
	protected $dates = [
		'created_at', 'updated_at', 'begin'
	];
	
	public function user()
	{
		return $this->belongsTo('App\Domain\Model\Auth\User');
	}
	
	public function runParticipation()
	{
		return $this->belongsTo('App\Domain\Model\Sponsor\RunParticipation');
	}

}
