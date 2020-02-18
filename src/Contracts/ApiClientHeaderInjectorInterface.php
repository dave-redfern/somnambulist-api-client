<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Contracts;

/**
 * Interface ApiClientHeaderInjectorInterface
 *
 * @package    Somnambulist\ApiClient\Contracts
 * @subpackage Somnambulist\ApiClient\Contracts\ApiClientHeaderInjectorInterface
 */
interface ApiClientHeaderInjectorInterface
{

    /**
     * Return an array of headers to inject into the HttpClient instance
     *
     * The array should return an array of key -> value pairs that can be added to the
     * options 'header' array key.
     *
     * The injector allows to inject custom values from the framework at runtime e.g.:
     * computed values that may not be available when the client is first created. An
     * example use case is to automatically inject the X-Request-Id header into the
     * client instance so the request id can be forwarded.
     *
     * @return array
     */
    public function getHeaders(): array;
}
