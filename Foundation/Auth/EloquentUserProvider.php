<?php

namespace Foundation\Auth;

use Foundation\Support\Str;
use Foundation\Contracts\Auth\UserProvider;
use Foundation\Contracts\Hashing\Hasher as HasherContract;
use Foundation\Contracts\Auth\Authenticatable as UserContract;

class EloquentUserProvider implements UserProvider
{
	/**
	 * The hasher implementation.
	 *
	 * @var \Foundation\Contracts\Hashing\Hasher
	 */
	protected $hasher;

	/**
	 * The Eloquent user model.
	 *
	 * @var string
	 */
	protected $model;

	/**
	 * Create a new database user provider.
	 *
	 * @param  \Foundation\Contracts\Hashing\Hasher  $hasher
	 * @param  string  $model
	 * @return void
	 */
	public function __construct(HasherContract $hasher, $model)
	{
		$this->model = $model;
		$this->hasher = $hasher;
	}

	/**
	 * Retrieve a user by their unique identifier.
	 *
	 * @param  mixed  $identifier
	 * @return \Foundation\Contracts\Auth\Authenticatable|null
	 */
	public function retrieveById($identifier)
	{
		return $this->createModel()->newQuery()->find($identifier);
	}

	/**
	 * Retrieve a user by their unique identifier and "remember me" token.
	 *
	 * @param  mixed  $identifier
	 * @param  string  $token
	 * @return \Foundation\Contracts\Auth\Authenticatable|null
	 */
	public function retrieveByToken($identifier, $token)
	{
		$model = $this->createModel();

		return $model->newQuery()
			->where($model->getAuthIdentifierName(), $identifier)
			->where($model->getRememberTokenName(), $token)
			->first();
	}

	/**
	 * Update the "remember me" token for the given user in storage.
	 *
	 * @param  \Foundation\Contracts\Auth\Authenticatable  $user
	 * @param  string  $token
	 * @return void
	 */
	public function updateRememberToken(UserContract $user, $token)
	{
		$user->setRememberToken($token);

		$user->save();
	}

	/**
	 * Retrieve a user by the given credentials.
	 *
	 * @param  array  $credentials
	 * @return \Foundation\Contracts\Auth\Authenticatable|null
	 */
	public function retrieveByCredentials(array $credentials)
	{
		if (empty($credentials))
{
			return;
		}

		// First we will add each credential element to the query as a where clause.
		// Then we can execute the query and, if we found a user, return it in a
		// Eloquent User "model" that will be utilized by the Guard instances.
		$query = $this->createModel()->newQuery();

		foreach ($credentials as $key => $value)
{
			if (! Str::contains($key, 'password'))
{
				$query->where($key, $value);
			}
		}

		return $query->first();
	}

	/**
	 * Validate a user against the given credentials.
	 *
	 * @param  \Foundation\Contracts\Auth\Authenticatable  $user
	 * @param  array  $credentials
	 * @return bool
	 */
	public function validateCredentials(UserContract $user, array $credentials)
	{
		$plain = $credentials['password'];

		return $this->hasher->check($plain, $user->getAuthPassword());
	}

	/**
	 * Create a new instance of the model.
	 *
	 * @return \Foundation\Database\Eloquent\Model
	 */
	public function createModel()
	{
		$class = '\\'.ltrim($this->model, '\\');

		return new $class;
	}

	/**
	 * Gets the hasher implementation.
	 *
	 * @return \Foundation\Contracts\Hashing\Hasher
	 */
	public function getHasher()
	{
		return $this->hasher;
	}

	/**
	 * Sets the hasher implementation.
	 *
	 * @param  \Foundation\Contracts\Hashing\Hasher  $hasher
	 * @return $this
	 */
	public function setHasher(HasherContract $hasher)
	{
		$this->hasher = $hasher;

		return $this;
	}

	/**
	 * Gets the name of the Eloquent user model.
	 *
	 * @return string
	 */
	public function getModel()
	{
		return $this->model;
	}

	/**
	 * Sets the name of the Eloquent user model.
	 *
	 * @param  string  $model
	 * @return $this
	 */
	public function setModel($model)
	{
		$this->model = $model;

		return $this;
	}
}