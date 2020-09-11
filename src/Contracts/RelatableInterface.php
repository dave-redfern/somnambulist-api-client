<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Contracts;

use Somnambulist\Collection\Contracts\Collection;

/**
 * Interface RelatableInterface
 *
 * @package    Somnambulist\Components\ApiClient\Contracts
 * @subpackage Somnambulist\Components\ApiClient\Contracts\RelatableInterface
 */
interface RelatableInterface
{

    public function new(array $attributes = []): RelatableInterface;

    public function getCollection(): Collection;

    public function getAttributes(): array;

    public function getAttribute(string $name);

    public function getRawAttribute(string $name);
}
