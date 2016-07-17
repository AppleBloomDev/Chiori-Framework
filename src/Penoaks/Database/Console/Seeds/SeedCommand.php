<?php
namespace Penoaks\Database\Console\Seeds;

use Penoaks\Console\Command;
use Penoaks\Console\ConfirmableTrait;
use Penoaks\Database\ConnectionResolverInterface as Resolver;
use Penoaks\Database\Eloquent\Model;
use Penoaks\Facades\Bindings;
use Symfony\Component\Console\Input\InputOption;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class SeedCommand extends Command
{
	use ConfirmableTrait;

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'db:seed';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Seed the database with records';

	/**
	 * The connection resolver instance.
	 *
	 * @var \Penoaks\Database\ConnectionResolverInterface
	 */
	protected $resolver;

	/**
	 * Create a new database seed command instance.
	 *
	 * @param  \Penoaks\Database\ConnectionResolverInterface $resolver
	 * @return void
	 */
	public function __construct( Resolver $resolver )
	{
		parent::__construct();

		$this->resolver = $resolver;
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
		if ( !$this->confirmToProceed() )
		{
			return;
		}

		$this->resolver->setDefaultConnection( $this->getDatabase() );

		Model::unguarded( function ()
		{
			$this->getSeeder()->run();
		} );
	}

	/**
	 * Get a seeder instance from the bindings.
	 *
	 * @return \Penoaks\Database\Seeder
	 */
	protected function getSeeder()
	{
		$class = Bindings::make( $this->input->getOption( 'class' ) );
		if ( method_exists( $class, 'setCommand' ) )
			$class->setCommand( $this );

		return $class;
	}

	/**
	 * Get the name of the database connection to use.
	 *
	 * @return string
	 */
	protected function getDatabase()
	{
		$database = $this->input->getOption( 'database' );

		return $database ?: Bindings::get( 'config' )['database.default'];
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return [
			['class', null, InputOption::VALUE_OPTIONAL, 'The class name of the root seeder', 'DatabaseSeeder'],

			['database', null, InputOption::VALUE_OPTIONAL, 'The database connection to seed'],

			['force', null, InputOption::VALUE_NONE, 'Force the operation to run when in production.'],
		];
	}
}