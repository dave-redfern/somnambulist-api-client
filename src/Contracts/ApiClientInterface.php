<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Contracts;

use Somnambulist\ApiClient\Client\ApiRouter;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Interface ApiClientInterface
 *
 * @package Somnambulist\ApiClient\Contracts
 * @subpackage Somnambulist\ApiClient\Contracts\ApiClientInterface
 */
interface ApiClientInterface
{

    /**
     * Return the current router instance being used for this client
     *
     * The ApiRouter includes the service definition and request context.
     *
     * @return ApiRouter
     */
    public function router(): ApiRouter;

    /**
     * Generate a route from the name and parameters
     *
     * Used for error logging and testing that the expected route will be created
     * with the given parameters.
     *
     * @param string $route
     * @param array  $parameters
     *
     * @return string
     */
    public function route(string $route, array $parameters = []): string;

    /**
     * Makes a GET request to the specified route
     *
     * @param string $route
     * @param array  $parameters
     *
     * @return ResponseInterface
     */
    public function get(string $route, array $parameters = []): ResponseInterface;

    /**
     * Makes a HEAD request to the specified route
     *
     * @param string $route
     * @param array  $parameters
     *
     * @return ResponseInterface
     */
    public function head(string $route, array $parameters = []): ResponseInterface;

    /**
     * Makes a POST request to the specified route
     *
     * @param string $route
     * @param array  $parameters
     * @param array  $body
     *
     * @return ResponseInterface
     */
    public function post(string $route, array $parameters = [], array $body = []): ResponseInterface;

    /**
     * Makes a PUT request to the specified route
     *
     * @param string $route
     * @param array  $parameters
     * @param array  $body
     *
     * @return ResponseInterface
     */
    public function put(string $route, array $parameters = [], array $body = []): ResponseInterface;

    /**
     * Makes a PATCH request to the specified route
     *
     * @param string $route
     * @param array  $parameters
     * @param array  $body
     *
     * @return ResponseInterface
     */
    public function patch(string $route, array $parameters = [], array $body = []): ResponseInterface;

    /**
     * Makes a DELETE request to the specified route
     *
     * @param string $route
     * @param array  $parameters
     *
     * @return ResponseInterface
     */
    public function delete(string $route, array $parameters = []): ResponseInterface;
}
