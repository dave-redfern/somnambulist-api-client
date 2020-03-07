<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Behaviours;

/**
 * Trait LoggerWrapper
 *
 * @package    Somnambulist\ApiClient\Behaviours
 * @subpackage Somnambulist\ApiClient\Behaviours\LoggerWrapper
 */
trait LoggerWrapper
{

    protected function log(string $level, string $message, array $context = []): void
    {
        if ($this->logger) {
            $this->logger->log($level, $message, $context);
        }
    }
}
