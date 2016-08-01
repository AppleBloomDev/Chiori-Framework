<?php namespace Milky\Http\View\Engines;

use Exception;
use Milky\Framework;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Symfony\Component\Finder\Finder;
use Throwable;

class PhpEngine implements EngineInterface
{
	/**
	 * Get the evaluated contents of the view.
	 *
	 * @param  string $path
	 * @param  array $data
	 * @return string
	 */
	public function get( $path, array $data = [] )
	{
		return $this->evaluatePath( $path, $data );
	}

	/**
	 * Get the evaluated contents of the view at the given path.
	 *
	 * @param  string $__path
	 * @param  array $__data
	 * @return string
	 */
	protected function evaluatePath( $__path, $__data )
	{
		$obLevel = ob_get_level();

		ob_start();

		extract( $__data, EXTR_SKIP );

		// We'll evaluate the contents of the view inside a try/catch block so we can
		// flush out any stray output that might get out before an error occurs or
		// an exception is thrown. This prevents any partial views from leaking.
		try
		{
			//include $__path;

			$source = "";

			foreach ( Finder::create()->files()->in( __DIR__ . '/../../../Facades' )->name( '*.php' ) as $file )
				$source .= "use \\Milky\\Facades\\" . str_replace( '.php', '', $file->getFilename() ) . ";\n";

			$source .= "?>" . file_get_contents( $__path );

			eval( $source );
		}
		catch ( Exception $e )
		{
			$this->handleViewException( $e, $obLevel );
		}
		catch ( Throwable $e )
		{
			$this->handleViewException( new FatalThrowableError( $e ), $obLevel );
		}

		return ltrim( ob_get_clean() );
	}

	/**
	 * Handle a view exception.
	 *
	 * @param  \Exception $e
	 * @param  int $obLevel
	 * @return void
	 *
	 * @throws $e
	 */
	protected function handleViewException( Exception $e, $obLevel )
	{
		while ( ob_get_level() > $obLevel )
		{
			ob_end_clean();
			// echo( ob_get_clean() ); // TODO Output ob config option
		}

		throw $e;
	}
}