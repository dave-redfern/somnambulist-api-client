<?php declare(strict_types=1);

namespace Somnambulist\ApiClient;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Somnambulist\ApiClient\Behaviours\EntityLocator\CanAppendIncludes;
use Somnambulist\ApiClient\Behaviours\EntityLocator\Find;
use Somnambulist\ApiClient\Behaviours\EntityLocator\FindBy;
use Somnambulist\ApiClient\Behaviours\EntityLocator\HydrateAsCollection;
use Somnambulist\ApiClient\Behaviours\EntityLocator\HydrateSingleObject;
use Somnambulist\ApiClient\Behaviours\LoggerWrapper;
use Somnambulist\ApiClient\Behaviours\RoutePrefixer;
use Somnambulist\ApiClient\Client\ApiRequestHelper;
use Somnambulist\ApiClient\Contracts\ApiClientInterface;
use Somnambulist\ApiClient\Contracts\EntityLocatorInterface;
use Somnambulist\ApiClient\Mapper\ObjectMapper;
use Somnambulist\Collection\MutableCollection;

/**
 * Class EntityLocator
 *
 * The EntityLocator is a Doctrine EntityRepository like base class that provides some
 * standard find methods for common operations. This includes: find, findBy, findOneBy
 * and findAll.
 *
 * Results are hydrated via the ObjectMapper that can return collections or single object
 * instances.
 *
 * To load additional data during a request; use `with()` to specify which includes should
 * be requested from the API end point. Note that this requires support from the Api
 * end point.
 *
 * @package    Somnambulist\ApiClient\Client
 * @subpackage Somnambulist\ApiClient\Client\EntityLocator
 */
class EntityLocator implements LoggerAwareInterface, EntityLocatorInterface
{

    use CanAppendIncludes;
    use Find;
    use FindBy;
    use HydrateAsCollection;
    use HydrateSingleObject;
    use LoggerAwareTrait;
    use LoggerWrapper;
    use RoutePrefixer;

    /**
     * @var ApiClientInterface
     */
    protected $client;

    /**
     * @var ObjectMapper
     */
    protected $mapper;

    /**
     * @var ApiRequestHelper
     */
    protected $apiHelper;

    /**
     * The type of objects that will be hydrated
     *
     * @var string
     */
    protected $className;

    /**
     * The name of the field / key for the primary id of the mapped objects
     *
     * @var string
     */
    protected $identityField;

    /**
     * The collection class to return when hydrating collections
     *
     * @var string
     */
    protected $collectionClass = MutableCollection::class;

    public function __construct(ApiClientInterface $client, ObjectMapper $mapper, string $class, string $identity = 'id')
    {
        $this->mapper        = $mapper;
        $this->client        = $client;
        $this->className     = $class;
        $this->identityField = $identity;

        $this->apiHelper = new ApiRequestHelper();
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getClient(): ApiClientInterface
    {
        return $this->client;
    }

    public function with(string ...$include): self
    {
        $this->includes = $include;

        return $this;
    }
}
