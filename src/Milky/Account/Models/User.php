<?php namespace Milky\Account\Models;

use Carbon\Carbon;
use Fenos\Notifynder\Notifable;
use HolyWorlds\Middleware\Permissions;
use HolyWorlds\Support\Traits\Authorizable;
use HolyWorlds\Support\Traits\UuidAsKey;
use HolyWorlds\Support\Util;
use Milky\Auth\Authenticatable;
use Milky\Auth\Passwords\CanResetPassword;
use Milky\Database\Eloquent\Model;
use Slynova\Commentable\Models\Comment;

class User extends Model
{
	use Authenticatable, Authorizable, CanResetPassword, Notifable, UuidAsKey;

	protected $hidden = ["password", "remember_token", "activation_token"];
	public $incrementing = false;
	protected $fillable = [
		'id',
		'oldid',
		'name',
		'email',
		'usebbhash',
		'password',
		'activation_token',
		'activation_updated',
		'created_at',
		'updated_at',
		'visited_at'
	];

	public function profile()
	{
		$result = $this->hasOne( UserProfile::class, "id" );
		if ( !$result->exists() )
			$result->create( [] );

		return $result;
	}

	public function auths()
	{
		return $this->hasMany( UserAuth::class );
	}

	public function comments()
	{
		return $this->hasMany( Comment::class );
	}

	public function characters()
	{
		return $this->hasMany( Character::class );
	}

	public function getDisplayNameAttribute()
	{
		/*if (!is_null($this->profile->family_name)) {
			return "{$this->name} ({$this->profile->family_name})";
		}*/
		return $this->name;
	}

	public function getMainCharacterAttribute()
	{
		return $this->characters()->where( 'main', 1 )->first();
	}

	public function getIsNewAttribute()
	{
		return Permissions::checkPermission( Setting::get( 'default_group' ) );
	}

	public function getSlugAttribute()
	{
		return Util::slugify( $this->name );
	}

	public function getProfileUrlAttribute()
	{
		return url( "user/{$this->id}-{$this->slug}" );
	}

	public function groups()
	{
		$groups = [];
		foreach ( $this->inheritance as $heir )
		{
			if ( false === array_search( $heir->group, $groups ) )
			{
				$groups[] = $heir->group;
			}
		}

		return $groups;
	}

	public function hasGroup( $group )
	{
		return $this->inheritance()->where( [
			"parent" => ( $group instanceof Group ) ? $group->id : $group,
			"type" => 1
		] )->exists();
	}

	public function addGroup( $parent )
	{
		$this->inheritance()->create( ["parent" => ( $parent instanceof Group ) ? $parent->id : $parent, "type" => 1] );
	}

	public function inheritance()
	{
		return $this->hasMany( GroupInheritance::class, "child" );
	}

	public function permissions()
	{
		return $this->hasMany( PermissionAssigned::class, "name" );
	}

	public static function stats()
	{
		$result = self::get();

		$activated = 0;
		$registered = 0;

		foreach ( $result as $user )
		{
			if ( $user->isActivated() )
			{
				$activated++;
			}
			else
			{
				$registered++;
			}
		}

		$stats = [];
		$stats[] = ["label" => "Registered", "data" => $activated];
		$stats[] = ["label" => "Unactivated", "data" => $registered];
		$stats[] = ["label" => "Online", "data" => 0];

		return $stats;
	}

	public function isActivated()
	{
		return empty( $this->activation_token );
	}

	public function activate()
	{
		if ( $this->isActivated() )
		{
			return;
		}

		$this->activation_token = null;
		$this->activation_updated = new Carbon();
		$this->save();
	}

	public function scopeActivated( $query )
	{
		return $query->where( "activation_token", null );
	}

	public function scopeForToken( $query, $token )
	{
		return $query->where( 'activation_token', $token );
	}

	public function activationToken()
	{
		if ( $this->isActivated() )
		{
			return null;
		}

		return $this->activation_token;
	}

	public function deactivate()
	{
		if ( !$this->isActivated() )
			return null;

		$token = null;
		do
		{
			$token = str_random( 32 );
		}
		while ( static::where( "activation_token", $token )->first() instanceof User );

		$this->activation_token = $token;
		$this->activation_updated = new Carbon();
		$this->save();

		return $token;
	}

	public static function boot()
	{
		parent::boot();

		static::creating( function ( $user )
		{
			if ( !$user->id )
			{
				$user->id = strtolower( Util::rand( 2, false, true ) ) . Util::rand( 3, true, false ) . Util::rand( 1, false, true );
			}
		} );
	}

	public function checkPermission( $permission )
	{
		return Permissions::checkPermission( $permission, $this );
	}

	public function isAdmin()
	{
		return Permissions::checkPermission( 'sys.admin', $this );
	}

	/**
	 * Returns the AcctId for this Account
	 *
	 * @return string Account Id
	 */
	function getId()
	{
		return $this->userId;
	}

	/**
	 * Compiles a human readable display name, e.g., John Smith
	 *
	 * @return string A human readable display name
	 */
	function getDisplayName()
	{
		return $this->name;
	}
}