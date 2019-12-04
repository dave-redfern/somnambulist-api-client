<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Mapper;

use Somnambulist\Collection\MutableCollection;

/**
 * Class ObjectHydratorContext
 *
 * @package Somnambulist\ApiClient\Mapper
 * @subpackage Somnambulist\ApiClient\Mapper\ObjectHydratorContext
 */
final class ObjectHydratorContext
{

    /**
     * @var MutableCollection
     */
    private $context;

    /**
     * Constructor.
     *
     * @param array $context
     */
    public function __construct(array $context = [])
    {
        $this->context = new MutableCollection($context);
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function get(string $key)
    {
        return $this->context->get($key);
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function set(string $key, $value): self
    {
        $this->context->set($key, $value);

        return $this;
    }

    public function has(string $key): bool
    {
        return $this->context->has($key);
    }
}
