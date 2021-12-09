<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Persistence;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Somnambulist\Components\ApiClient\Client\Connection\Decoders\SimpleJsonDecoder;
use Somnambulist\Components\ApiClient\Client\Contracts\ConnectionInterface;
use Somnambulist\Components\ApiClient\Client\Contracts\ResponseDecoderInterface;
use Somnambulist\Components\ApiClient\Persistence\Behaviours\MakeCreateRequest;
use Somnambulist\Components\ApiClient\Persistence\Behaviours\MakeDestroyRequest;
use Somnambulist\Components\ApiClient\Persistence\Behaviours\MakeRequest;
use Somnambulist\Components\ApiClient\Persistence\Behaviours\MakeUpdateRequest;
use Somnambulist\Components\ApiClient\Persistence\Contracts\ActionPersisterInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Class ActionPersister
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
 * @subpackage Somnambulist\Components\ApiClient\Persistence\ActionPersister
 */
class ActionPersister implements ActionPersisterInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    use MakeRequest;
    use MakeCreateRequest;
    use MakeUpdateRequest;
    use MakeDestroyRequest;

    protected ConnectionInterface $connection;
    protected ResponseDecoderInterface $decoder;

    public function __construct(ConnectionInterface $connection, ResponseDecoderInterface $decoder = null)
    {
        $this->connection = $connection;
        $this->decoder    = $decoder ?? new SimpleJsonDecoder();
    }

    public function getConnection(): ConnectionInterface
    {
        return $this->connection;
    }

    protected function log(string $level, string $message, array $context = []): void
    {
        $this->logger?->log($level, $message, $context);
    }

    protected function hydrateObject(ResponseInterface $response, string $className): ?object
    {
        $data = $this->decoder->object($this->decoder->decode($response, [200, 201]));

        if (empty($data)) {
            return null;
        }

        return new $className($data);
    }
}
