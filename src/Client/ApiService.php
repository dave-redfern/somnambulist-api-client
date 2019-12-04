<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Client;

use Somnambulist\Collection\MutableCollection;
use Symfony\Component\Routing\RequestContext;
use function explode;
use function parse_url;

/**
 * Class ApiService
 *
 * @package Somnambulist\ApiClient\Client
 * @subpackage Somnambulist\ApiClient\Client\ApiService
 */
final class ApiService
{

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $alias;

    /**
     * @var RequestContext
     */
    private $context;

    /**
     * Constructor
     *
     * @param string      $url
     * @param string|null $alias
     */
    public function __construct(string $url, string $alias = null)
    {
        $this->url   = $url;
        $this->alias = $alias;
    }

    public function url(): string
    {
        return $this->url;
    }

    public function alias(): string
    {
        if ($this->alias) {
            return $this->alias;
        }

        return explode('.', $this->context()->getHost())[0];
    }

    public function context(): RequestContext
    {
        if ($this->context) {
            return $this->context;
        }

        $parsed = MutableCollection::collect(parse_url($this->url));

        return $this->context = new RequestContext(
            $parsed->get('path', ''),
            'GET',
            $parsed->get('host', 'localhost'),
            $parsed->get('scheme', 'http'),
            $parsed->get('port', 80),
            $parsed->get('port', 443),
            $parsed->get('path', '/'),
            $parsed->get('query', ''),
        );
    }
}
