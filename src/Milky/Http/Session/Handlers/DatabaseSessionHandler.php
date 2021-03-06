<?php namespace Milky\Http\Session\Handlers;

use Carbon\Carbon;
use Milky\Database\ConnectionInterface;
use Milky\Database\Query\Builder;
use Milky\Facades\Acct;
use Milky\Http\Factory;
use Milky\Http\Session\ExistenceAwareInterface;
use SessionHandlerInterface;

class DatabaseSessionHandler implements SessionHandlerInterface, ExistenceAwareInterface
{
	/**
	 * The database connection instance.
	 *
	 * @var ConnectionInterface
	 */
	protected $connection;

	/**
	 * The name of the session table.
	 *
	 * @var string
	 */
	protected $table;

	/*
	 * The number of minutes the session should be valid.
	 *
	 * @var int
	 */
	protected $minutes;

	/**
	 * The existence state of the session.
	 *
	 * @var bool
	 */
	protected $exists;

	/**
	 * Create a new database session handler instance.
	 *
	 * @param  ConnectionInterface $connection
	 * @param  string $table
	 * @param  string $minutes
	 */
	public function __construct( ConnectionInterface $connection, $table, $minutes )
	{
		$this->table = $table;
		$this->minutes = $minutes;
		$this->connection = $connection;
	}

	/**
	 * {@inheritdoc
	 */
	public function open( $savePath, $sessionName )
	{
		return true;
	}

	/**
	 * {@inheritdoc
	 */
	public function close()
	{
		return true;
	}

	/**
	 * {@inheritdoc
	 */
	public function read( $sessionId )
	{
		$session = (object) $this->getQuery()->find( $sessionId );

		if ( isset( $session->last_activity ) )
		{
			if ( $session->last_activity < Carbon::now()->subMinutes( $this->minutes )->getTimestamp() )
			{
				$this->exists = true;

				return null;
			}
		}

		if ( isset( $session->payload ) )
		{
			$this->exists = true;

			return base64_decode( $session->payload );
		}

		return null;
	}

	/**
	 * {@inheritdoc
	 */
	public function write( $sessionId, $data )
	{
		$payload = $this->getDefaultPayload( $data );

		if ( !$this->exists )
			$this->read( $sessionId );

		if ( $this->exists )
			$this->getQuery()->where( 'id', $sessionId )->update( $payload );
		else
		{
			$payload['id'] = $sessionId;

			$this->getQuery()->insert( $payload );
		}

		$this->exists = true;
	}

	/**
	 * Get the default payload for the session.
	 *
	 * @param  string $data
	 * @return array
	 */
	protected function getDefaultPayload( $data )
	{
		$payload = ['payload' => base64_encode( $data ), 'last_activity' => time()];

		if ( Acct::check() )
			$payload['user_id'] = Acct::acct()->getId();

		if ( $request = Factory::i()->request() )
		{
			$payload['ip_address'] = $request->ip();
			$payload['user_agent'] = substr( (string) $request->header( 'User-Agent' ), 0, 500 );
		}

		return $payload;
	}

	/**
	 * {@inheritdoc
	 */
	public function destroy( $sessionId )
	{
		$this->getQuery()->where( 'id', $sessionId )->delete();
	}

	/**
	 * {@inheritdoc
	 */
	public function gc( $lifetime )
	{
		$this->getQuery()->where( 'last_activity', '<=', time() - $lifetime )->delete();
	}

	/**
	 * Get a fresh query builder instance for the table.
	 *
	 * @return Builder
	 */
	protected function getQuery()
	{
		return $this->connection->table( $this->table );
	}

	/**
	 * Set the existence state for the session.
	 *
	 * @param  bool $value
	 * @return $this
	 */
	public function setExists( $value )
	{
		$this->exists = $value;

		return $this;
	}
}
