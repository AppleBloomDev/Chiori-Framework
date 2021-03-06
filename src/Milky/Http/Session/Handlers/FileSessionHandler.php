<?php namespace Milky\Http\Session\Handlers;

use Carbon\Carbon;
use Milky\Filesystem\Filesystem;
use SessionHandlerInterface;
use Symfony\Component\Finder\Finder;

class FileSessionHandler implements SessionHandlerInterface
{
	/**
	 * The filesystem instance.
	 *
	 * @var Filesystem
	 */
	protected $files;

	/**
	 * The path where sessions should be stored.
	 *
	 * @var string
	 */
	protected $path;

	/**
	 * The number of minutes the session should be valid.
	 *
	 * @var int
	 */
	protected $minutes;

	/**
	 * Create a new file driven handler instance.
	 *
	 * @param  Filesystem $files
	 * @param  string $path
	 * @param  int $minutes
	 */
	public function __construct( Filesystem $files, $path, $minutes )
	{
		$this->path = $path;
		$this->files = $files;
		$this->minutes = $minutes;
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
		if ( $this->files->exists( $path = $this->path . '/' . $sessionId ) )
		{
			if ( filemtime( $path ) >= Carbon::now()->subMinutes( $this->minutes )->getTimestamp() )
				return $this->files->get( $path );
		}

		return '';
	}

	/**
	 * {@inheritdoc
	 */
	public function write( $sessionId, $data )
	{
		$this->files->put( $this->path . '/' . $sessionId, $data, true );
	}

	/**
	 * {@inheritdoc
	 */
	public function destroy( $sessionId )
	{
		$this->files->delete( $this->path . '/' . $sessionId );
	}

	/**
	 * {@inheritdoc
	 */
	public function gc( $lifetime )
	{
		$files = Finder::create()->in( $this->path )->files()->ignoreDotFiles( true )->date( '<= now - ' . $lifetime . ' seconds' );

		foreach ( $files as $file )
			$this->files->delete( $file->getRealPath() );
	}
}
