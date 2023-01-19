<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Persistence\Behaviours;

use function is_null;
use function trigger_deprecation;

trait HasObjectData
{
    protected ?string $class = null;
    protected array $properties;

    public function hydrateClass(string $class): self
    {
        $this->class = $class;

        return $this;
    }

    public function include(array $properties): self
    {
        $this->properties = $properties;

        return $this;
    }

    public function set(string $name, mixed $value = null): self
    {
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
