<?php namespace Milky\Queue;

use Milky\Contracts\Queue\Job as JobContract;

trait InteractsWithQueue
{
	/**
	 * The underlying queue job instance.
	 *
	 * @var \Job
	 */
	protected $job;

	/**
	 * Get the number of times the job has been attempted.
	 *
	 * @return int
	 */
	public function attempts()
	{
		return $this->job ? $this->job->attempts() : 1;
	}

	/**
	 * Delete the job from the queue.
	 *
	 * @return void
	 */
	public function delete()
	{
		if ($this->job) {
			return $this->job->delete();
		}
	}

	/**
	 * Fail the job from the queue.
	 *
	 * @return void
	 */
	public function failed()
	{
		if ($this->job) {
			return $this->job->failed();
		}
	}

	/**
	 * Release the job back into the queue.
	 *
	 * @param  int   $delay
	 * @return void
	 */
	public function release($delay = 0)
	{
		if ($this->job) {
			return $this->job->release($delay);
		}
	}

	/**
	 * Set the base queue job instance.
	 *
	 * @param  \Illuminate\Contracts\Queue\Job  $job
	 * @return $this
	 */
	public function setJob(JobContract $job)
	{
		$this->job = $job;

		return $this;
	}
}