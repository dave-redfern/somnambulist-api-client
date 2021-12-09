<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Client;

use Symfony\Component\Routing\Route;

/**
 * Class ApiRoute
 *
 * Simplifies the Symfony Route constructor so it is more appropriate for defining API
 * resource routes.
 *
 * @package    Somnambulist\Components\ApiClient\Client
 * @subpackage Somnambulist\Components\ApiClient\Client\ApiRoute
 */
class ApiRoute extends Route
{
    public function __construct(string $route, array $rules = [], array $methods = [])
    {
        parent::__construct($route, [], $rules, [], null, [], $methods);
    }

    public static function get(string $route, array $rules = []): self
    {
        return new self($route, $rules, ['GET']);
    }

    public static function post(string $route, array $rules = []): self
    {
        return new self($route, $rules, ['POST']);
    }

    public static function put(string $route, array $rules = []): self
    {
        return new self($route, $rules, ['PUT']);
    }

    public static function delete(string $route, array $rules = []): self
    {
        return new self($route, $rules, ['DELETE']);
    }
}
