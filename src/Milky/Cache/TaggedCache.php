<?php namespace Milky\Cache;

class TaggedCache extends Repository
{
	use RetrievesMultipleKeys;

	/**
	 * The tag set instance.
	 *
	 * @var TagSet
	 */
	protected $tags;

	/**
	 * Create a new tagged cache instance.
	 *
	 * @param  Store $store
	 * @param  TagSet $tags
	 */
	public function __construct( Store $store, TagSet $tags )
	{
		parent::__construct( $store );

		$this->tags = $tags;
	}

	/**
	 * {@inheritdoc
	 */
	protected function fireCacheEvent( $event, $payload )
	{
		$payload[] = $this->tags->getNames();

		parent::fireCacheEvent( $event, $payload );
	}

	/**
	 * Increment the value of an item in the cache.
	 *
	 * @param  string $key
	 * @param  mixed $value
	 */
	public function increment( $key, $value = 1 )
	{
		$this->store->increment( $this->itemKey( $key ), $value );
	}

	/**
	 * Increment the value of an item in the cache.
	 *
	 * @param  string $key
	 * @param  mixed $value
	 */
	public function decrement( $key, $value = 1 )
	{
		$this->store->decrement( $this->itemKey( $key ), $value );
	}

	/**
	 * Remove all items from the cache.
	 *
	 */
	public function flush()
	{
		$this->tags->reset();
	}

	/**
	 * {@inheritdoc
	 */
	protected function itemKey( $key )
	{
		return $this->taggedItemKey( $key );
	}

	/**
	 * Get a fully qualified key for a tagged item.
	 *
	 * @param  string $key
	 * @return string
	 */
	public function taggedItemKey( $key )
	{
		return sha1( $this->tags->getNamespace() ) . ':' . $key;
	}
}
