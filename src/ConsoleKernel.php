<?php

namesapce Penoaks\Console;

use Exception;
use Throwable;
use Foundation\Contracts\Events\Dispatcher;
use Foundation\Console\Scheduling\Schedule;
use Foundation\Console\Application as Artisan;
use Foundation\Framework;
use Foundation\Contracts\Console\Kernel as KernelContract;
use Symfony\Component\Debug\Exception\FatalThrowableError;

class Kernel implements KernelContract
{
	/**
	 * The application implementation.
	 *
	 * @var \Penoaks\Framework
	 */
	protected $fw;

	/**
	 * The event dispatcher implementation.
	 *
	 * @var \Penoaks\Contracts\Events\Dispatcher
	 */
	protected $events;

	/**
	 * The Artisan application instance.
	 *
	 * @var \Penoaks\Console\Application
	 */
	protected $artisan;

	/**
	 * The Artisan commands provided by the application.
	 *
	 * @var array
	 */
	protected $commands = [];

	/**
	 * The bootstrap classes for the application.
	 *
	 * @var array
	 */
	protected $bootstrappers = [
		'Penoaks\Bootstrap\SetRequestForConsole',
	];

	/**
	 * Create a new console kernel instance.
	 *
	 * @param  \Penoaks\Framework  $fw
	 * @param  \Penoaks\Contracts\Events\Dispatcher  $events
	 * @return void
	 */
	public function __construct(Framework $fw, Dispatcher $events)
	{
		if (! defined('ARTISAN_BINARY'))
{
			define('ARTISAN_BINARY', 'artisan');
		}

		$this->fw = $fw;
		$this->events = $events;

		$this->fw->booted(function ()
{
			$this->defineConsoleSchedule();
		});
	}

	/**
	 * Define the application's command schedule.
	 *
	 * @return void
	 */
	protected function defineConsoleSchedule()
	{
		$this->fw->bindings->instance(
			'Penoaks\Console\Scheduling\Schedule', $schedule = new Schedule
		);

		$this->schedule($schedule);
	}

	/**
	 * Run the console application.
	 *
	 * @param  \Symfony\Component\Console\Input\InputInterface  $input
	 * @param  \Symfony\Component\Console\Output\OutputInterface  $output
	 * @return int
	 */
	public function handle($input, $output = null)
	{
		try
{
			$this->bootstrap();

			return $this->getArtisan()->run($input, $output);
		} catch (Exception $e)
{
			$this->reportException($e);

			$this->renderException($output, $e);

			return 1;
		} catch (Throwable $e)
{
			$e = new FatalThrowableError($e);

			$this->reportException($e);

			$this->renderException($output, $e);

			return 1;
		}
	}

	/**
	 * Terminate the application.
	 *
	 * @param  \Symfony\Component\Console\Input\InputInterface  $input
	 * @param  int  $status
	 * @return void
	 */
	public function terminate($input, $status)
	{
		$this->fw->terminate();
	}

	/**
	 * Define the application's command schedule.
	 *
	 * @param  \Penoaks\Console\Scheduling\Schedule  $schedule
	 * @return void
	 */
	protected function schedule(Schedule $schedule)
	{
		//
	}

	/**
	 * Register the given command with the console application.
	 *
	 * @param  \Symfony\Component\Console\Command\Command  $command
	 * @return void
	 */
	public function registerCommand($command)
	{
		$this->getArtisan()->add($command);
	}

	/**
	 * Run an Artisan console command by name.
	 *
	 * @param  string  $command
	 * @param  array  $parameters
	 * @return int
	 */
	public function call($command, array $parameters = [])
	{
		$this->bootstrap();

		return $this->getArtisan()->call($command, $parameters);
	}

	/**
	 * Queue the given console command.
	 *
	 * @param  string  $command
	 * @param  array   $parameters
	 * @return void
	 */
	public function queue($command, array $parameters = [])
	{
		$this->fw->bindings['Penoaks\Contracts\Queue\Queue']->push(
			'Penoaks\Console\QueuedJob', func_get_args()
		);
	}

	/**
	 * Get all of the commands registered with the console.
	 *
	 * @return array
	 */
	public function all()
	{
		$this->bootstrap();

		return $this->getArtisan()->all();
	}

	/**
	 * Get the output for the last run command.
	 *
	 * @return string
	 */
	public function output()
	{
		$this->bootstrap();

		return $this->getArtisan()->output();
	}

	/**
	 * Bootstrap the application for artisan commands.
	 *
	 * @return void
	 */
	public function bootstrap()
	{
		if (! $this->fw->hasBeenBootstrapped())
		{
			$this->fw->bootstrapWith($this->bootstrappers());
		}

		// If we are calling an arbitrary command from within the application, we'll load
		// all of the available deferred providers which will make all of the commands
		// available to an application. Otherwise the command will not be available.
		$this->fw->loadDeferredProviders();
	}

	/**
	 * Get the Artisan application instance.
	 *
	 * @return \Penoaks\Console\Application
	 */
	protected function getArtisan()
	{
		if (is_null($this->artisan))
{
			return $this->artisan = (new Artisan($this->fw, $this->events, $this->fw->version()))
								->resolveCommands($this->commands);
		}

		return $this->artisan;
	}

	/**
	 * Get the bootstrap classes for the application.
	 *
	 * @return array
	 */
	protected function bootstrappers()
	{
		return $this->bootstrappers;
	}

	/**
	 * Report the exception to the exception handler.
	 *
	 * @param  \Exception  $e
	 * @return void
	 */
	protected function reportException(Exception $e)
	{
		$this->fw->bindings['Penoaks\Contracts\Debug\ExceptionHandler']->report($e);
	}

	/**
	 * Report the exception to the exception handler.
	 *
	 * @param  \Symfony\Component\Console\Output\OutputInterface  $output
	 * @param  \Exception  $e
	 * @return void
	 */
	protected function renderException($output, Exception $e)
	{
		$this->fw->bindings['Penoaks\Contracts\Debug\ExceptionHandler']->renderForConsole($output, $e);
	}
}
