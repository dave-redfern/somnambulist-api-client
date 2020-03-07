<?php declare(strict_types=1);

namespace Somnambulist\ApiClient;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Somnambulist\ApiClient\Behaviours\EntityLocator\HydrateSingleObject;
use Somnambulist\ApiClient\Behaviours\EntityPersister\CanDestroyEntity;
use Somnambulist\ApiClient\Behaviours\EntityPersister\CanStoreEntity;
use Somnambulist\ApiClient\Behaviours\EntityPersister\CanUpdateEntity;
use Somnambulist\ApiClient\Behaviours\LoggerWrapper;
use Somnambulist\ApiClient\Behaviours\RoutePrefixer;
use Somnambulist\ApiClient\Contracts\ApiClientInterface;
use Somnambulist\ApiClient\Contracts\EntityPersisterInterface;
use Somnambulist\ApiClient\Mapper\ObjectMapper;

/**
 * Class EntityPersister
 *
 * Provides a set of common methods for creating (storing) new objects,
 * updating an existing object or deleting (destroying) an existing object.
 * This class can be extended or re-implemented to provide the necessary
 * functionality your client requires.
 *
 * store and update methods will return a hydrated object of the specified type.
 * If an error occurs, or the API returns an unexpected response code, an
 * exception will be raised. If the error is from the API itself and not a curl
 * or client error, then the JSON error message will be added to the exception
 * history via the ApiErrorException wrapper class.
 *
 * This implementation does not implement request validation for store or update
 * but does provide hook methods to implement checks if necessary via:
 * validateStoreRequest and validateUpdateRequest.
 *
 * @package    Somnambulist\ApiClient
 * @subpackage Somnambulist\ApiClient\EntityPersister
 */
class EntityPersister implements LoggerAwareInterface, EntityPersisterInterface
{

    use HydrateSingleObject;
    use LoggerAwareTrait;
    use LoggerWrapper;
    use RoutePrefixer;

    use CanStoreEntity;
    use CanUpdateEntity;
    use CanDestroyEntity;

    /**
     * @var ApiClientInterface
     */
    protected $client;

    /**
     * @var ObjectMapper
     */
    protected $mapper;

    /**
     * @var string
     */
    protected $className;

    /**
     * @var string
     */
    protected $identityField;

    public function __construct(ApiClientInterface $client, ObjectMapper $mapper, string $class, string $identity = 'id')
    {
        $this->mapper        = $mapper;
        $this->client        = $client;
        $this->className     = $class;
        $this->identityField = $identity;
    }

    public function getClassName(): string
    {
        return $this->className;
    }
}
