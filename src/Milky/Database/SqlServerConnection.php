<?php namespace Milky\Database;

use Closure;
use Exception;
use Throwable;
use Doctrine\DBAL\Driver\PDOSqlsrv\Driver as DoctrineDriver;
use Milky\Database\Query\Processors\SqlServerProcessor;
use Milky\Database\Query\Grammars\SqlServerGrammar as QueryGrammar;
use Milky\Database\Schema\Grammars\SqlServerGrammar as SchemaGrammar;

class SqlServerConnection extends Connection
{
	/**
	 * Execute a Closure within a transaction.
	 *
	 * @param  \Closure  $callback
	 * @return mixed
	 *
	 * @throws Throwable
	 */
	public function transaction(Closure $callback)
	{
		if ($this->getDriverName() == 'sqlsrv') {
			return parent::transaction($callback);
		}

		$this->getPdo()->exec('BEGIN TRAN');

		// We'll simply execute the given callback within a try / catch block
		// and if we catch any exception we can rollback the transaction
		// so that none of the changes are persisted to the database.
		try {
			$result = $callback($this);

			$this->getPdo()->exec('COMMIT TRAN');
		}

		// If we catch an exception, we will roll back so nothing gets messed
		// up in the database. Then we'll re-throw the exception so it can
		// be handled how the developer sees fit for their applications.
		catch (Exception $e) {
			$this->getPdo()->exec('ROLLBACK TRAN');

			throw $e;
		} catch (Throwable $e) {
			$this->getPdo()->exec('ROLLBACK TRAN');

			throw $e;
		}

		return $result;
	}

	/**
	 * Get the default query grammar instance.
	 *
	 * @return SqlServerGrammar
	 */
	protected function getDefaultQueryGrammar()
	{
		return $this->withTablePrefix(new QueryGrammar);
	}

	/**
	 * Get the default schema grammar instance.
	 *
	 * @return SqlServerGrammar
	 */
	protected function getDefaultSchemaGrammar()
	{
		return $this->withTablePrefix(new SchemaGrammar);
	}

	/**
	 * Get the default post processor instance.
	 *
	 * @return SqlServerProcessor
	 */
	protected function getDefaultPostProcessor()
	{
		return new SqlServerProcessor;
	}

	/**
	 * Get the Doctrine DBAL driver.
	 *
	 * @return Driver
	 */
	protected function getDoctrineDriver()
	{
		return new DoctrineDriver;
	}
}
