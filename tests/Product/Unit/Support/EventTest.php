<?php

namespace Arkenstone\Core\Tests\Unit\Support;

use Arkenstone\Core\Tests\TestCase;
use Arkenstone\Core\Support\Event;
use Illuminate\Support\Facades\Event as LaravelEvent;

class EventTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Reset event hooks between tests
        Event::setDispatcher(LaravelEvent::getFacadeRoot());
    }    /** @test */
    public function it_can_register_and_trigger_hooks()
    {
        $callbackExecuted = false;

        Event::hook('test.event', function ($value) use (&$callbackExecuted) {
            $callbackExecuted = true;
            return $value;
        });

        Event::dispatch('test.event', ['test_value']);

        $this->assertTrue($callbackExecuted);
    }

    /** @test */
    public function it_passes_arguments_to_hook_callbacks()
    {
        $receivedArgs = null;

        Event::hook('test.event', function ($args) use (&$receivedArgs) {
            $receivedArgs = $args;
            return $args;
        });

        $result = Event::dispatch('test.event', ['value1', 'value2', 'value3']);

        $this->assertEquals(['value1', 'value2', 'value3'], $receivedArgs);
        $this->assertEquals(['value1', 'value2', 'value3'], $result);
    }

    /** @test */
    public function it_allows_hooks_to_modify_data()
    {
        Event::hook('test.filter', function ($data) {
            if (is_array($data) && !isset($data['modified'])) {
                $data['modified'] = true;
            }
            return $data;
        });

        $result = Event::dispatch('test.filter', ['original' => true]);

        $this->assertTrue(is_array($result));
        $this->assertTrue($result['modified'] ?? false);
        $this->assertTrue($result['original'] ?? false);
    }

    /** @test */
    public function it_executes_multiple_hooks_in_order()
    {
        $executionOrder = [];

        Event::hook('test.event', function ($value) use (&$executionOrder) {
            $executionOrder[] = 'first';
            return $value;
        });

        Event::hook('test.event', function ($value) use (&$executionOrder) {
            $executionOrder[] = 'second';
            return $value;
        });

        Event::hook('test.event', function ($value) use (&$executionOrder) {
            $executionOrder[] = 'third';
            return $value;
        });

        Event::dispatch('test.event', ['value']);

        $this->assertEquals(['first', 'second', 'third'], $executionOrder);
    }

    /** @test */
    public function it_iterates_through_multiple_hooks()
    {
        $executionCount = 0;

        Event::hook('test.chain', function ($value) use (&$executionCount) {
            $executionCount++;
            return $value + 1;
        });

        Event::hook('test.chain', function ($value) use (&$executionCount) {
            $executionCount++;
            return $value * 2;
        });

        Event::hook('test.chain', function ($value) use (&$executionCount) {
            $executionCount++;
            return $value + 10;
        });

        $result = Event::dispatch('test.chain', 5);

        // Each listener gets original value (5), last non-null return wins
        // Last hook: 5 + 10 = 15
        $this->assertEquals(3, $executionCount);
        $this->assertEquals(15, $result);
    }

    /** @test */
    public function it_returns_original_value_when_no_hooks_registered()
    {
        $result = Event::dispatch('unregistered.event', 'original_value');

        $this->assertEquals('original_value', $result);
    }

    /** @test */
    public function it_attaches_laravel_event_dispatcher()
    {
        $dispatcher = LaravelEvent::getFacadeRoot();

        Event::setDispatcher($dispatcher);

        // No exception means attachment worked
        $this->assertTrue(true);
    }
}
