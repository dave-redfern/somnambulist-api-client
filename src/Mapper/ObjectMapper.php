<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Mapper;

use Assert\Assert;
use Somnambulist\ApiClient\Contracts\ObjectHydratorInterface;
use Somnambulist\Collection\AbstractCollection;
use Somnambulist\Collection\Contracts\Collection;
use Somnambulist\Collection\MutableCollection;

/**
 * Class ObjectMapper
 *
 * @package Somnambulist\ApiClient\Mapper
 * @subpackage Somnambulist\ApiClient\Mapper\ObjectMapper
 */
final class ObjectMapper
{

    /**
     * @var MutableCollection|ObjectHydratorInterface[]
     */
    private $hydrators;

    /**
     * @var string
     */
    private $collectionClass = MutableCollection::class;

    /**
     * Constructor.
     *
     * @param iterable|ObjectHydratorInterface[] $hydrators
     */
    public function __construct(iterable $hydrators = [])
    {
        $this->hydrators = new MutableCollection();

        $this->addHydrators($hydrators);
    }

    /**
     * Change the collection class returned for the current call to mapArray
     *
     * This allows a typed collection class to be used e.g. UserCollection, instead
     * of a generic collection. This can be useful when you need to include custom
     * logic to manipulate the collection.
     *
     * The collection class is reset to the MutableCollection class after any call
     * to mapArray to ensure consistent behaviour.
     *
     * @param string $collectionClass
     *
     * @return $this
     */
    public function setCollectionClass(string $collectionClass)
    {
        Assert::that($collectionClass)
            ->satisfy(function ($value) {
                return
                    in_array(Collection::class, class_implements($value))
                    ||
                    is_a($value, AbstractCollection::class, true)
                ;
            }, sprintf('Collection class must implement %s interface or extend %s', Collection::class, AbstractCollection::class))
        ;

        $this->collectionClass = $collectionClass;

        return $this;
    }

    /**
     * @param array|ObjectHydratorInterface[] $hydrators
     */
    public function addHydrators(iterable $hydrators): void
    {
        foreach ($hydrators as $class => $hydrator) {
            $this->addHydrator($class, $hydrator);
        }
    }

    public function addHydrator(string $class, ObjectHydratorInterface $handler): void
    {
        $this->hydrators->set($class, $handler);
    }

    /**
     * Hydrates a single result object
     *
     * @param string                $class
     * @param array|object          $resource
     * @param ObjectHydratorContext $context
     *
     * @return object
     */
    public function map(string $class, $resource, ObjectHydratorContext $context): object
    {
        Assert::that($this->hydrators->toArray(), 'ObjectMapper does not contain a hydrator for "%s"')->keyExists($class);

        return $this->hydrators->get($class)->hydrate($resource, $context);
    }

    /**
     * Hydrate an array of similar results, returning a Collection instance of objects
     *
     * @param string                $class
     * @param array                 $resource
     * @param ObjectHydratorContext $context
     *
     * @return Collection
     */
    public function mapArray(string $class, array $resource, ObjectHydratorContext $context): Collection
    {
        $i       = 0;
        $cnt     = count($resource);
        $results = new $this->collectionClass();

        foreach ($resource as $element) {
            $context->set('element_position', ++$i);
            $context->set('element_count', $cnt);

            $results->add($this->map($class, $element, $context));
        }

        $this->setCollectionClass(MutableCollection::class);

        return $results;
    }
}
