<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Persistence\Behaviours;

use function is_null;

/**
 * Trait HasObjectData
 *
 * @package    Somnambulist\Components\ApiClient\Persistence\Behaviours
 * @subpackage Somnambulist\Components\ApiClient\Persistence\Behaviours\HasObjectData
 */
trait HasObjectData
{

    /**
     * The class that will be returned after calling the API
     *
     * @var string
     */
    protected $class;

    /**
     * The array of required properties to create the object
     *
     * @var array
     */
    protected $properties;

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

    /**
     * @param string|array $name  The property name to set/change or an array of properties to set
     * @param mixed        $value
     *
     * @return $this
     */
    public function set($name, $value = null): self
    {
        if (is_array($name) && is_null($value)) {
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
