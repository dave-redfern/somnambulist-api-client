<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Client\EventListeners;

use Somnambulist\Components\ApiClient\Client\Events\PreRequestEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class InjectHeadersFromRequestStack
 *
 * Allows injecting headers from the Symfony master request on the RequestStack.
 *
 * @package    Somnambulist\Components\ApiClient\Client\EventListeners
 * @subpackage Somnambulist\Components\ApiClient\Client\EventListeners\InjectHeadersFromRequestStack
 */
class InjectHeadersFromRequestStack implements EventSubscriberInterface
{
    private RequestStack $stack;
    private array $headers;

    public function __construct(RequestStack $stack, array $headers = [])
    {
        $this->stack   = $stack;
        $this->headers = $headers;
    }

    public static function getSubscribedEvents(): array
    {
        return [PreRequestEvent::class => 'onPreRequest'];
    }

    public function onPreRequest(PreRequestEvent $event): void
    {
        if (null === $request = $this->stack->getMainRequest()) {
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
