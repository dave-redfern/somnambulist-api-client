<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Client\Injectors;

use Somnambulist\ApiClient\Contracts\ApiClientHeaderInjectorInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class InjectHeadersFromRequestStack
 *
 * Allows injecting headers from the Symfony master request on the RequestStack.
 *
 * @package    Somnambulist\ApiClient\Client\Injectors
 * @subpackage Somnambulist\ApiClient\Client\Injectors\InjectHeadersFromRequestStack
 */
class InjectHeadersFromRequestStack implements ApiClientHeaderInjectorInterface
{

    /**
     * @var RequestStack
     */
    private $stack;

    /**
     * @var array|string[]
     */
    private $headers;

    public function __construct(RequestStack $stack, array $headers = [])
    {
        $this->stack   = $stack;
        $this->headers = $headers;
    }

    public function getHeaders(): array
    {
        if (null === $request = $this->stack->getMasterRequest()) {
            return [];
        }

        $toInject = [];

        foreach ($this->headers as $header) {
            if (!$request->headers->has($header)) {
                continue;
            }

            $toInject[$header] = $request->headers->get($header);
        }

        return $toInject;
    }
}
