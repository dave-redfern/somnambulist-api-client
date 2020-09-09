<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Persistence\Contracts;

use Assert\InvalidArgumentException;

/**
 * Interface ApiActionInterface
 *
 * @package    Somnambulist\Components\ApiClient\Contracts
 * @subpackage Somnambulist\Components\ApiClient\Persistence\Contracts\ApiActionInterface
 */
interface ApiActionInterface
{
    /**
     * Provide assertions that the action can be executed
     *
     * @return bool
     * @throws InvalidArgumentException
     */
    public function isValid(): bool;

    public function getClass(): string;

    public function getProperties(): array;

    public function getRoute(): string;

    public function getRouteParams(): array;

    public function getMethod(): ?string;
}
