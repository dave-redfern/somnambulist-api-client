<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient;

use Somnambulist\Components\ApiClient\Client\Connection;
use Somnambulist\Components\ApiClient\Client\Contracts\ConnectionInterface;
use Somnambulist\Components\ApiClient\Exceptions\ConnectionManagerException;

use function array_key_exists;

final class ConnectionManager
{
    private array $connections = [];

    public function __construct(array $connections = [])
    {
        $this->forAll($connections);
    }

    public function forAll(array $connections)
    {
        foreach ($connections as $model => $connection) {
            $this->add($connection, $model);
        }
    }

    /**
     * Set the Connection to use by default or for a specific model
     *
     * The model class name should be used and then that connection will be used with all
     * instances of that model. A default connection should still be provided as a fallback.
     *
     * @param ConnectionInterface $connection
     * @param string              $model
     */
    public function add(ConnectionInterface $connection, string $model = 'default'): void
    {
        $this->connections[$model] = $connection;
    }

    /**
     * Get the model or default connection
     *
     * @param string $model
     *
     * @return Connection
     * @throws ConnectionManagerException
     */
    public function for(string $model = 'default'): ConnectionInterface
    {
        $try = $model;

        if ('default' !== $model && !array_key_exists($try, $this->connections)) {
            $try = 'default';
        }

        if (null === $connection = ($this->connections[$try] ?? null)) {
            throw ConnectionManagerException::missingConnectionFor($model);
        }

        return $connection;
    }
}
