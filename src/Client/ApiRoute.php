<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Client;

use Symfony\Component\Routing\Route;

/**
 * Class ApiRoute
 *
 * Simplifies the Symfony Route constructor so it is more appropriate for defining API
 * resource routes.
 *
 * @package Somnambulist\ApiClient\Client
 * @subpackage Somnambulist\ApiClient\Client\ApiRoute
 */
class ApiRoute extends Route
{

    /**
     * Constructor.
     *
     * @param string $route
     * @param array  $rules
     * @param array  $methods
     */
    public function __construct(string $route, array $rules = [], array $methods = [])
    {
        parent::__construct($route, [], $rules, [], null, [], $methods);
    }
}
