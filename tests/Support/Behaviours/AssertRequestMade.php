<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Tests\Support\Behaviours;

use PHPUnit\Framework\ExpectationFailedException;
use Somnambulist\Components\ApiClient\AbstractModel;
use Somnambulist\Components\ApiClient\Manager;
use Somnambulist\Components\ApiClient\Tests\Support\Stubs\Entities\User;
use Symfony\Contracts\HttpClient\ResponseInterface;
use function asort;

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
    protected function assertRouteWasCalledWith(string $expectedRoute, array $expectedParams = [], array $expectedBody = []): void
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
     * @param string $expectedRoute
     * @param array  $expectedParams array of field => value pairs to check for
     * @param array  $expectedBody   array of field => value pairs to check for
     */
    protected function assertRouteWasNotCalledWith(string $expectedRoute, array $expectedParams = [], array $expectedBody = []): void
    {
        Manager::instance()->connect(new User)->onBeforeRequest(function ($method, $route, $params, $body) use ($expectedRoute, $expectedParams, $expectedBody) {
            if ($expectedRoute == $route) {
                asort($expectedParams);
                asort($params);

                if ($params !== $expectedParams) {
                    return;
                }

                throw new ExpectationFailedException(sprintf('Route "%s" was called but was not expected to be', $expectedRoute));
            }
        });
    }

    /**
     * @param string|null $expectedBody    the response string to compare with
     * @param array       $expectedHeaders array of key => value pairs to check for
     */
    protected function assertResponseContains(string $expectedBody = null, array $expectedHeaders = []): void
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

    /**
     * @param string|null $expectedBody    the response string that should not appear
     * @param array       $expectedHeaders array of key => value pairs to check dont exist
     */
    protected function assertResponseDoesNotContain(string $expectedBody = null, array $expectedHeaders = []): void
    {
        Manager::instance()->connect(new User)->onAfterRequest(function (ResponseInterface $response) use ($expectedBody, $expectedHeaders) {
            foreach ($expectedHeaders as $key => $value) {
                $this->assertNull($response->getHeaders()[$key] ?? null);
            }

            if ($expectedBody) {
                $this->assertStringNotContainsString($expectedBody, $response->getContent());
            }
        });
    }
}
