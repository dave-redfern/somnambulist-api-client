<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Persistence\Behaviours;

use function is_null;
use function trigger_deprecation;

/**
 * Trait HasObjectData
 *
 * @package    Somnambulist\Components\ApiClient\Persistence\Behaviours
 * @subpackage Somnambulist\Components\ApiClient\Persistence\Behaviours\HasObjectData
 */
trait HasObjectData
{
    protected ?string $class = null;
    protected array $properties;

    public function hydrateClass(string $class): self
    {
        $this->class = $class;

        return $this;
    }

    public function with(array $properties): self
    {
        $this->properties = $properties;

        return $this;
    }

    public function set(string|array $name, mixed $value = null): self
    {
        if (is_array($name) && is_null($value)) {
            trigger_deprecation('somnambulist/api-client', '3.2.2', 'Passing an array as first arg is deprecated, use "with()"');
            return $this->with($name);
        }

        $this->properties[$name] = $value;

        return $this;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }
}
