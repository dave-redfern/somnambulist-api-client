<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient;

use InvalidArgumentException;
use Somnambulist\Components\ApiClient\Client\Connection\Decoders\SimpleJsonDecoder;
use Somnambulist\Components\ApiClient\Client\Contracts\QueryEncoderInterface;
use Somnambulist\Components\ApiClient\Client\Contracts\ResponseDecoderInterface;
use Somnambulist\Components\ApiClient\Client\Query\Encoders\SimpleEncoder;
use Somnambulist\Components\ApiClient\Exceptions\EntityNotFoundException;
use function array_key_exists;

/**
 * Class Model
 *
 * @package    Somnambulist\Components\ApiClient
 * @subpackage Somnambulist\Components\ApiClient\Model
 */
abstract class Model extends AbstractModel
{

    /**
     * The primary key for the model
     *
     * This is the name of the field used to store the primary identifier for this Model
     * as it appears in the server response.
     */
    protected string $primaryKey = 'id';

    /**
     * The QueryEncoder to use when making requests to the API endpoint
     *
     * Use one of the built-in encoders, or add your own that can create an array of
     * query arguments as needed by your API.
     */
    protected string $queryEncoder = SimpleEncoder::class;

    /**
     * The response decoder to use to create the internal array structures
     *
     * The default handles only a basic JSON structure as defined in the docs.
     * For other response formats, implement a decoder to convert to the simpler
     * array syntax expected.
     */
    protected string $responseDecoder = SimpleJsonDecoder::class;

    /**
     * The route names to use for searching / loading this Model.
     *
     * The route name should be configured in the ApiRouter on the connection associated
     * with this Model type. The main routes needed are one to search / access a list of
     * resources, and one to fetch a single resource.
     */
    protected array $routes = [
        'search' => null,
        'view'   => null,
    ];

    /**
     * The relationships to eager load on every request
     */
    protected array $with = [];

    /**
     * @param string $id
     *
     * @return Model|null
     */
    public static function find($id): ?Model
    {
        return static::query()->find($id);
    }

    /**
     * @param string $id
     *
     * @return Model
     * @throws EntityNotFoundException
     */
    public static function findOrFail($id): Model
    {
        return static::query()->findOrFail($id);
    }

    /**
     * Eager load the specified relationships on this model
     *
     * Allows dot notation to load related.related objects.
     *
     * @param string ...$relations
     *
     * @return ModelBuilder
     */
    public static function with(...$relations): ModelBuilder
    {
        return static::query()->with(...$relations);
    }

    /**
     * Starts a new query builder process without any constraints
     *
     * @return ModelBuilder
     */
    public static function query(): ModelBuilder
    {
        return (new static)->newQuery();
    }

    public function newQuery(): ModelBuilder
    {
        $builder = new ModelBuilder($this);
        $builder->with($this->with);

        return $builder;
    }

    public function getQueryEncoder(): QueryEncoderInterface
    {
        return new $this->queryEncoder;
    }

    public function getResponseDecoder(): ResponseDecoderInterface
    {
        return new $this->responseDecoder;
    }

    public function getRoute(string $type = 'search'): string
    {
        if (!array_key_exists($type, $this->routes)) {
            throw new InvalidArgumentException(
                sprintf('No route has been configured for "%s", add it to %s::$routes', $type, static::class)
            );
        }

        return $this->routes[$type];
    }

    public function getPrimaryKeyName(): string
    {
        return $this->primaryKey;
    }

    public function getPrimaryKey(): mixed
    {
        return $this->attributes[$this->primaryKey] ?? null;
    }
}
