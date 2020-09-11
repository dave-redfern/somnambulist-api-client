<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Tests\Support\Behaviours;

use Somnambulist\Components\ApiClient\Manager;
use Somnambulist\Components\ApiClient\Tests\Support\Stubs\Entities\User;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Trait AssertRequestMade
 *
 * @package    Somnambulist\Components\ApiClient\Tests\Support\Behaviours
 * @subpackage Somnambulist\Components\ApiClient\Tests\Support\Behaviours\AssertRequestMade
 */
trait AssertRequestMade
{

    /**
     * @param string $expectedRoute
     * @param array  $expectedParams array of field => value pairs to check for
     * @param array  $expectedBody   array of field => value pairs to check for
     */
    protected function assertRouteWasCalledWith(string $expectedRoute, array $expectedParams = [], array $expectedBody = [])
    {
        Manager::instance()->connect(new User)->onBeforeRequest(function ($method, $route, $params, $body) use ($expectedRoute, $expectedParams, $expectedBody) {
            $this->assertEquals($expectedRoute, $route);

            foreach ($expectedParams as $key => $value) {
                $this->assertArrayHasKey($key, $params);
                $this->assertEquals($value, $params[$key]);
            }
            foreach ($expectedBody as $key => $value) {
                $this->assertArrayHasKey($key, $params);
                $this->assertEquals($value, $params[$key]);
            }
        });
    }

    /**
     * @param string|null $expectedBody    the response string to compare with
     * @param array       $expectedHeaders array of key => value pairs to check for
     */
    protected function assertResponseContains(string $expectedBody = null, array $expectedHeaders = [])
    {
        Manager::instance()->connect(new User)->onAfterRequest(function (ResponseInterface $response) use ($expectedBody, $expectedHeaders) {
            foreach ($expectedHeaders as $key => $value) {
                $this->assertEquals($value, $response->getHeaders()[$key]);
            }

            if ($expectedBody) {
                $this->assertStringContainsString($expectedBody, $response->getContent());
            }
        });
    }
}
