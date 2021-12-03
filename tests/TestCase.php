<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

	/**
	 * Hack to get testing working
	 */
	protected function tearDown(): void
	{
		$config = app('config');
		$events = app('events');
		parent::tearDown();
		app()->instance('config', $config);
		app()->instance('events', $events);
	}
}
