<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Persistence;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Somnambulist\Components\ApiClient\Behaviours\EntityLocator\HydrateSingleObject;
use Somnambulist\Components\ApiClient\Behaviours\LoggerWrapper;
use Somnambulist\Components\ApiClient\Client\Contracts\ConnectionInterface;
use Somnambulist\Components\ApiClient\Mapper\ObjectMapper;
use Somnambulist\Components\ApiClient\Persistence\Behaviours\MakeCreateRequest;
use Somnambulist\Components\ApiClient\Persistence\Behaviours\MakeDestroyRequest;
use Somnambulist\Components\ApiClient\Persistence\Behaviours\MakeRequest;
use Somnambulist\Components\ApiClient\Persistence\Behaviours\MakeUpdateRequest;
use Somnambulist\Components\ApiClient\Persistence\Contracts\EntityPersisterInterface;

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
 * @package    Somnambulist\Components\ApiClient
 * @subpackage Somnambulist\Components\ApiClient\Persistence\EntityPersister
 */
class EntityPersister implements EntityPersisterInterface, LoggerAwareInterface
{

    use HydrateSingleObject;
    use LoggerAwareTrait;

    use MakeRequest;
    use MakeCreateRequest;
    use MakeUpdateRequest;
    use MakeDestroyRequest;

    /**
     * @var ConnectionInterface
     */
    protected $client;

    /**
     * @var ObjectMapper
     */
    protected $mapper;

    public function __construct(ConnectionInterface $client, ObjectMapper $mapper)
    {
        $this->mapper = $mapper;
        $this->client = $client;
    }

    public function getClient(): ConnectionInterface
    {
        return $this->client;
    }

    protected function log(string $level, string $message, array $context = []): void
    {
        if ($this->logger) {
            $this->logger->log($level, $message, $context);
        }
    }
}
