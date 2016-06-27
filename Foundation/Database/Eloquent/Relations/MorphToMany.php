<?php

namespace Foundation\Database\Eloquent\Relations;

use Foundation\Support\Arr;
use Foundation\Database\Eloquent\Model;
use Foundation\Database\Eloquent\Builder;

class MorphToMany extends BelongsToMany
{
	/**
	 * The type of the polymorphic relation.
	 *
	 * @var string
	 */
	protected $morphType;

	/**
	 * The class name of the morph type constraint.
	 *
	 * @var string
	 */
	protected $morphClass;

	/**
	 * Indicates if we are connecting the inverse of the relation.
	 *
	 * This primarily affects the morphClass constraint.
	 *
	 * @var bool
	 */
	protected $inverse;

	/**
	 * Create a new morph to many relationship instance.
	 *
	 * @param  \Foundation\Database\Eloquent\Builder  $query
	 * @param  \Foundation\Database\Eloquent\Model  $parent
	 * @param  string  $name
	 * @param  string  $table
	 * @param  string  $foreignKey
	 * @param  string  $otherKey
	 * @param  string  $relationName
	 * @param  bool  $inverse
	 * @return void
	 */
	public function __construct(Builder $query, Model $parent, $name, $table, $foreignKey, $otherKey, $relationName = null, $inverse = false)
	{
		$this->inverse = $inverse;
		$this->morphType = $name.'_type';
		$this->morphClass = $inverse ? $query->getModel()->getMorphClass() : $parent->getMorphClass();

		parent::__construct($query, $parent, $table, $foreignKey, $otherKey, $relationName);
	}

	/**
	 * Set the where clause for the relation query.
	 *
	 * @return $this
	 */
	protected function setWhere()
	{
		parent::setWhere();

		$this->query->where($this->table.'.'.$this->morphType, $this->morphClass);

		return $this;
	}

	/**
	 * Add the constraints for a relationship count query.
	 *
	 * @param  \Foundation\Database\Eloquent\Builder  $query
	 * @param  \Foundation\Database\Eloquent\Builder  $parent
	 * @param  array|mixed  $columns
	 * @return \Foundation\Database\Eloquent\Builder
	 */
	public function getRelationQuery(Builder $query, Builder $parent, $columns = ['*'])
	{
		$query = parent::getRelationQuery($query, $parent, $columns);

		return $query->where($this->table.'.'.$this->morphType, $this->morphClass);
	}

	/**
	 * Set the constraints for an eager load of the relation.
	 *
	 * @param  array  $models
	 * @return void
	 */
	public function addEagerConstraints(array $models)
	{
		parent::addEagerConstraints($models);

		$this->query->where($this->table.'.'.$this->morphType, $this->morphClass);
	}

	/**
	 * Create a new pivot attachment record.
	 *
	 * @param  int   $id
	 * @param  bool  $timed
	 * @return array
	 */
	protected function createAttachRecord($id, $timed)
	{
		$record = parent::createAttachRecord($id, $timed);

		return Arr::add($record, $this->morphType, $this->morphClass);
	}

	/**
	 * Create a new query builder for the pivot table.
	 *
	 * @return \Foundation\Database\Query\Builder
	 */
	protected function newPivotQuery()
	{
		$query = parent::newPivotQuery();

		return $query->where($this->morphType, $this->morphClass);
	}

	/**
	 * Create a new pivot model instance.
	 *
	 * @param  array  $attributes
	 * @param  bool   $exists
	 * @return \Foundation\Database\Eloquent\Relations\Pivot
	 */
	public function newPivot(array $attributes = [], $exists = false)
	{
		$pivot = new MorphPivot($this->parent, $attributes, $this->table, $exists);

		$pivot->setPivotKeys($this->foreignKey, $this->otherKey)
			  ->setMorphType($this->morphType)
			  ->setMorphClass($this->morphClass);

		return $pivot;
	}

	/**
	 * Get the foreign key "type" name.
	 *
	 * @return string
	 */
	public function getMorphType()
	{
		return $this->morphType;
	}

	/**
	 * Get the class name of the parent model.
	 *
	 * @return string
	 */
	public function getMorphClass()
	{
		return $this->morphClass;
	}
}
