<?php namespace Milky\Database\Connectors;

use InvalidArgumentException;
use Milky\Binding\BindingBuilder;
use Milky\Database\MySqlConnection;
use Milky\Database\PostgresConnection;
use Milky\Database\SQLiteConnection;
use Milky\Database\SqlServerConnection;
use Milky\Framework;
use Milky\Helpers\Arr;

class ConnectionFactory
{
	/**
	 * Establish a PDO connection based on the configuration.
	 *
	 * @param  array $config
	 * @param  string $name
	 * @return Connection
	 */
	public function make( array $config, $name = null )
	{
		$config = $this->parseConfig( $config, $name );

		if ( isset( $config['read'] ) )
			return $this->createReadWriteConnection( $config );

		return $this->createSingleConnection( $config );
	}

	/**
	 * Create a single database connection instance.
	 *
	 * @param  array $config
	 * @return Connection
	 */
	protected function createSingleConnection( array $config )
	{
		$pdo = function () use ( $config )
		{
			return $this->createConnector( $config )->connect( $config );
		};

		return $this->createConnection( $config['driver'], $pdo, $config['database'], $config['prefix'], $config );
	}

	/**
	 * Create a single database connection instance.
	 *
	 * @param  array $config
	 * @return Connection
	 */
	protected function createReadWriteConnection( array $config )
	{
		$connection = $this->createSingleConnection( $this->getWriteConfig( $config ) );

		return $connection->setReadPdo( $this->createReadPdo( $config ) );
	}

	/**
	 * Create a new PDO instance for reading.
	 *
	 * @param  array $config
	 * @return \PDO
	 */
	protected function createReadPdo( array $config )
	{
		$readConfig = $this->getReadConfig( $config );

		return $this->createConnector( $readConfig )->connect( $readConfig );
	}

	/**
	 * Get the read configuration for a read / write connection.
	 *
	 * @param  array $config
	 * @return array
	 */
	protected function getReadConfig( array $config )
	{
		$readConfig = $this->getReadWriteConfig( $config, 'read' );

		if ( isset( $readConfig['host'] ) && is_array( $readConfig['host'] ) )
			$readConfig['host'] = count( $readConfig['host'] ) > 1 ? $readConfig['host'][array_rand( $readConfig['host'] )] : $readConfig['host'][0];

		return $this->mergeReadWriteConfig( $config, $readConfig );
	}

	/**
	 * Get the read configuration for a read / write connection.
	 *
	 * @param  array $config
	 * @return array
	 */
	protected function getWriteConfig( array $config )
	{
		$writeConfig = $this->getReadWriteConfig( $config, 'write' );

		return $this->mergeReadWriteConfig( $config, $writeConfig );
	}

	/**
	 * Get a read / write level configuration.
	 *
	 * @param  array $config
	 * @param  string $type
	 * @return array
	 */
	protected function getReadWriteConfig( array $config, $type )
	{
		if ( isset( $config[$type][0] ) )
			return $config[$type][array_rand( $config[$type] )];

		return $config[$type];
	}

	/**
	 * Merge a configuration for a read / write connection.
	 *
	 * @param  array $config
	 * @param  array $merge
	 * @return array
	 */
	protected function mergeReadWriteConfig( array $config, array $merge )
	{
		return Arr::except( array_merge( $config, $merge ), ['read', 'write'] );
	}

	/**
	 * Parse and prepare the database configuration.
	 *
	 * @param  array $config
	 * @param  string $name
	 * @return array
	 */
	protected function parseConfig( array $config, $name )
	{
		return Arr::add( Arr::add( $config, 'prefix', '' ), 'name', $name );
	}

	/**
	 * Create a connector instance based on the configuration.
	 *
	 * @param  array $config
	 * @return ConnectorInterface
	 *
	 * @throws \InvalidArgumentException
	 */
	public function createConnector( array $config )
	{
		if ( !isset( $config['driver'] ) )
			throw new InvalidArgumentException( 'A driver must be specified.' );

		if ( Framework::available( $key = 'db.connection' . $config['driver'] ) )
			return BindingBuilder::resolveBinding( $key );

		switch ( $config['driver'] )
		{
			case 'mysql':
				return new MySqlConnector;
			case 'pgsql':
				return new PostgresConnector;
			case 'sqlite':
				return new SQLiteConnector;
			case 'sqlsrv':
				return new SqlServerConnector;
		}

		throw new InvalidArgumentException( "Unsupported driver [{$config['driver']}]" );
	}

	/**
	 * Create a new connection instance.
	 *
	 * @param  string $driver
	 * @param  string $database
	 * @param  string $prefix
	 * @param  array $config
	 * @return Connection
	 *
	 * @throws \InvalidArgumentException
	 */
	protected function createConnection( $driver, $connection, $database, $prefix = '', array $config = [] )
	{
		Framework::set( $key = 'db.connection' . $driver, BindingBuilder::resolveBinding( $key, [$connection, $database, $prefix, $config] )  );

		switch ( $driver )
		{
			case 'mysql':
				return new MySqlConnection( $connection, $database, $prefix, $config );
			case 'pgsql':
				return new PostgresConnection( $connection, $database, $prefix, $config );
			case 'sqlite':
				return new SQLiteConnection( $connection, $database, $prefix, $config );
			case 'sqlsrv':
				return new SqlServerConnection( $connection, $database, $prefix, $config );
		}

		throw new InvalidArgumentException( "Unsupported driver [$driver]" );
	}
}