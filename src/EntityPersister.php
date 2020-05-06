<?php declare(strict_types=1);

namespace Somnambulist\ApiClient;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Somnambulist\ApiClient\Behaviours\EntityLocator\HydrateSingleObject;
use Somnambulist\ApiClient\Behaviours\EntityPersister\MakeCreateRequest;
use Somnambulist\ApiClient\Behaviours\EntityPersister\MakeDestroyRequest;
use Somnambulist\ApiClient\Behaviours\EntityPersister\MakeRequest;
use Somnambulist\ApiClient\Behaviours\EntityPersister\MakeUpdateRequest;
use Somnambulist\ApiClient\Behaviours\LoggerWrapper;
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
 * @package    Somnambulist\ApiClient
 * @subpackage Somnambulist\ApiClient\EntityPersister
 */
class EntityPersister implements EntityPersisterInterface, LoggerAwareInterface
{

    use HydrateSingleObject;
    use LoggerAwareTrait;
    use LoggerWrapper;

    use MakeRequest;
    use MakeCreateRequest;
    use MakeUpdateRequest;
    use MakeDestroyRequest;

    /**
     * @var ApiClientInterface
     */
    protected $client;

    /**
     * @var ObjectMapper
     */
    protected $mapper;

    public function __construct(ApiClientInterface $client, ObjectMapper $mapper)
    {
        $this->mapper = $mapper;
        $this->client = $client;
    }

    public function getClient(): ApiClientInterface
    {
        return $this->client;
    }
}
