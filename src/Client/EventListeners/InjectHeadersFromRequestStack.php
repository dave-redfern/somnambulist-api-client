<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Client\EventListeners;

use Somnambulist\Components\ApiClient\Client\Events\PreRequestEvent;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class InjectHeadersFromRequestStack
 *
 * Allows injecting headers from the Symfony master request on the RequestStack.
 *
 * @package    Somnambulist\Components\ApiClient\Client\EventListeners
 * @subpackage Somnambulist\Components\ApiClient\Client\EventListeners\InjectHeadersFromRequestStack
 */
class InjectHeadersFromRequestStack
{

    private RequestStack $stack;
    private array $headers;

    public function __construct(RequestStack $stack, array $headers = [])
    {
        $this->stack   = $stack;
        $this->headers = $headers;
    }

    public function onPreRequest(PreRequestEvent $event): void
    {
        if (null === $request = $this->stack->getMasterRequest()) {
            return;
        }

        $toInject = [];

        foreach ($this->headers as $header) {
            if (!$request->headers->has($header)) {
                continue;
            }

            $toInject[$header] = $request->headers->get($header);
        }

        $event->setHeaders($toInject);
    }
}
