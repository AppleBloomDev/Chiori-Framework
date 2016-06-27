<?php

namespace Foundation\Pagination;

use Foundation\Support\HtmlString;
use Foundation\Contracts\Pagination\Paginator as PaginatorContract;

class SimpleBootstrapFourPresenter extends BootstrapFourPresenter
{
	/**
	 * Create a simple Bootstrap 4 presenter.
	 *
	 * @param  \Foundation\Contracts\Pagination\Paginator  $paginator
	 * @return void
	 */
	public function __construct(PaginatorContract $paginator)
	{
		$this->paginator = $paginator;
	}

	/**
	 * Determine if the underlying paginator being presented has pages to show.
	 *
	 * @return bool
	 */
	public function hasPages()
	{
		return $this->paginator->hasPages() && count($this->paginator->items()) > 0;
	}

	/**
	 * Convert the URL window into Bootstrap HTML.
	 *
	 * @return \Foundation\Support\HtmlString
	 */
	public function render()
	{
		if ($this->hasPages()) {
			return new HtmlString(sprintf(
				'<ul class="pager">%s %s</ul>',
				$this->getPreviousButton(),
				$this->getNextButton()
			));
		}

		return '';
	}
}
