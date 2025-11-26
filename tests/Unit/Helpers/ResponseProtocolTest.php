<?php

namespace Arkenstone\Core\Tests\Unit\Helpers;

use Arkenstone\Core\Tests\TestCase;
use Arkenstone\Core\Helpers\ResponseProtocol;
use Arkenstone\Core\Support\Event;
use Illuminate\Http\JsonResponse;

class ResponseProtocolTest extends TestCase
{
    /** @test */
    public function it_returns_success_response_with_correct_structure()
    {
        $data = ['id' => 1, 'name' => 'Test Product'];
        $message = 'Product retrieved successfully';
        $code = 200;

        $response = ResponseProtocol::success($data, $message, $code);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals($code, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertEquals('success', $content['status']);
        $this->assertEquals($message, $content['message']);
        $this->assertEquals($data, $content['data']);
    }

    /** @test */
    public function it_returns_error_response_with_correct_structure()
    {
        $errors = ['field' => ['Validation failed']];
        $message = 'The given data was invalid';
        $code = 422;

        $response = ResponseProtocol::failed($errors, $message, $code);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals($code, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertEquals('error', $content['status']);
        $this->assertEquals($message, $content['message']);
        $this->assertEquals($errors, $content['errors']);
    }

    /** @test */
    public function it_dispatches_success_event_on_success_response()
    {
        $eventFired = false;
        $receivedArgs = null;

        Event::hook('response.success', function ($args) use (&$eventFired, &$receivedArgs) {
            $eventFired = true;
            $receivedArgs = $args;
            return $args;
        });

        $data = ['id' => 1];
        ResponseProtocol::success($data, 'Success', 200);

        $this->assertTrue($eventFired);
        $this->assertEquals([$data, 'Success', 200], $receivedArgs);
    }

    /** @test */
    public function it_dispatches_error_event_on_error_response()
    {
        $eventFired = false;
        $receivedArgs = null;

        Event::hook('response.error', function ($args) use (&$eventFired, &$receivedArgs) {
            $eventFired = true;
            $receivedArgs = $args;
            return $args;
        });

        $errors = ['field' => ['Error message']];
        ResponseProtocol::failed($errors, 'Error', 422);

        $this->assertTrue($eventFired);
        $this->assertEquals([$errors, 'Error', 422], $receivedArgs);
    }

    /** @test */
    public function it_can_omit_message_parameter()
    {
        $response = ResponseProtocol::success(['id' => 1]);
        $content = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('message', $content);
    }

    /** @test */
    public function it_can_omit_error_message_parameter()
    {
        $response = ResponseProtocol::failed(['field' => ['error']]);
        $content = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('message', $content);
    }

    /** @test */
    public function it_uses_default_status_code_when_not_provided()
    {
        $successResponse = ResponseProtocol::success(['id' => 1], 'Success');
        $this->assertEquals(200, $successResponse->getStatusCode());

        $errorResponse = ResponseProtocol::failed(['field' => ['error']], 'Error');
        $this->assertEquals(400, $errorResponse->getStatusCode());
    }

    /** @test */
    public function it_allows_event_hooks_to_be_registered()
    {
        $called = false;

        Event::hook('response.success', function ($args) use (&$called) {
            $called = true;
            return $args;
        });

        ResponseProtocol::success(['id' => 1], 'Success', 200);

        $this->assertTrue($called);
    }
}
