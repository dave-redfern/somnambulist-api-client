<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient;

use BadMethodCallException;
use IlluminateAgnostic\Str\Support\Str;
use Pagerfanta\Adapter\FixedAdapter;
use Pagerfanta\Pagerfanta;
use Somnambulist\Collection\Contracts\Collection;
use Somnambulist\Components\ApiClient\Client\Contracts\ConnectionInterface;
use Somnambulist\Components\ApiClient\Client\Contracts\ExpressionInterface;
use Somnambulist\Components\ApiClient\Client\Query\Expression\CompositeExpression;
use Somnambulist\Components\ApiClient\Client\Query\Expression\ExpressionBuilder;
use Somnambulist\Components\ApiClient\Client\Query\QueryBuilder;
use Somnambulist\Components\ApiClient\Exceptions\EntityNotFoundException;
use Somnambulist\Components\ApiClient\Exceptions\NoResultsException;
use Somnambulist\Components\ApiClient\Relationships\AbstractRelationship;
use Somnambulist\Components\ApiClient\Utils\GenerateRelationshipsToEagerLoad;
use function array_key_exists;
use function array_key_first;
use function count;
use function get_class;
use function is_array;
use function is_string;
use function json_decode;
use function method_exists;
use function sprintf;
use function strlen;
use function strtolower;
use function substr;
use const JSON_THROW_ON_ERROR;

/**
 * Class ModelBuilder
 *
 * @package    Somnambulist\Components\ApiClient
 * @subpackage Somnambulist\Components\ApiClient\ModelBuilder
 *
 * @method ExpressionBuilder expr()
 *
 * @method ModelBuilder where(ExpressionInterface ...$predicate)
 * @method ModelBuilder andWhere(ExpressionInterface ...$predicates)
 * @method ModelBuilder orWhere(ExpressionInterface ...$predicates)
 * @method ModelBuilder orderBy(string $field, string $dir = 'asc')
 * @method ModelBuilder addOrderBy(string $field, string $dir = 'asc')
 * @method ModelBuilder page(int $page = null)
 * @method ModelBuilder perPage(int $perPage = null)
 * @method ModelBuilder limit(int $limit = null)
 * @method ModelBuilder offset(string $offset = null)
 *
 * @method array getWith()
 * @method array getOrderBy()
 * @method null|CompositeExpression getWhere()
 * @method null|int getLimit()
 * @method null|string getOffset()
 * @method null|int getPage()
 * @method null|int getPerPage()
 */
class ModelBuilder
{

    private Model               $model;
    private QueryBuilder        $query;
    private ConnectionInterface $connection;
    private array               $eagerLoad = [];
    private ?string             $route     = null;

    public function __construct(Model $model)
    {
        $this->model      = $model;
        $this->route      = $model->getRoute('search');
        $this->query      = new QueryBuilder();
        $this->connection = Manager::instance()->connect($model);
    }

    private function useRoute(string $route): self
    {
        $this->route = $this->model->getRoute($route);

        return $this;
    }

    public function newQuery(): self
    {
        return new static($this->model);
    }

    /**
     * Find the model by primary key, optionally returning just the specified columns
     *
     * @param string $id
     *
     * @return Model|null
     */
    public function find($id): ?Model
    {
        return $this->useRoute('view')->wherePrimaryKey($id)->fetch()->first();
    }

    /**
     * Find records by the given criteria similar to EntityRepository findBy
     *
     * @param array       $criteria An array of field name -> value pairs to search
     * @param array       $orderBy  An array of field name -> asc|desc values to order by
     * @param int|null    $limit
     * @param string|null $offset
     *
     * @return Collection
     */
    public function findBy(array $criteria = [], array $orderBy = [], int $limit = null, string $offset = null): Collection
    {
        foreach ($criteria as $field => $value) {
            $this->whereField($field, 'eq', $value);
        }
        foreach ($orderBy as $field => $dir) {
            $this->addOrderBy($field, strtolower($dir));
        }

        if ($limit) {
            $this->limit($limit);
        }
        if ($offset) {
            $this->offset($offset);
        }

        return $this->fetch();
    }

    /**
     * Returns the first record matching the criteria and order or null
     *
     * @param array $criteria An array of field name -> value pairs to search
     * @param array $orderBy  An array of field name -> asc|desc values to order by
     *
     * @return Model|null
     */
    public function findOneBy(array $criteria = [], array $orderBy = []): ?Model
    {
        return $this->findBy($criteria, $orderBy, 1)->first();
    }

    /**
     * Find the model by the primary key, but raise an exception if not found
     *
     * @param string $id
     *
     * @return Model
     * @throws EntityNotFoundException
     */
    public function findOrFail($id): Model
    {
        if (null === $model = $this->find($id)) {
            throw EntityNotFoundException::noMatchingRecordFor(get_class($this->model), $this->model->getPrimaryKey(), $id);
        }

        return $model;
    }

    public function fetchFirstOrFail(): Model
    {
        if (null === $model = $this->fetch()->first()) {
            throw NoResultsException::noResultsForQuery(get_class($this->model), $this->query);
        }

        return $model;
    }

    public function fetchFirstOrNull(): ?Model
    {
        return $this->fetch()->first();
    }

    public function fetch(): Collection
    {
        $models   = $this->model->getCollection();
        $response = $this->connection->get(
            $this->route,
            $this->model->getQueryEncoder()->encode($this->query->with($this->eagerLoad))
        );

        if (200 !== $response->getStatusCode()) {
            return $models;
        }

        $data = json_decode((string)$response->getContent(), true, $depth = 512, JSON_THROW_ON_ERROR);

        if (!$data || !is_array($data)) {
            return $models;
        }
        if (isset($data['data'])) {
            // external response could contain a data element
            $data = $data['data'];
        }
        if (is_string(array_key_first($data))) {
            // treat single object responses like collections
            $data = [$data];
        }

        foreach ($data as $row) {
            $models->add($this->model->new($row));
        }

        if ($models->count() > 0) {
            $this->eagerLoadRelationships($models, $data);
        }

        return $models;
    }

    /**
     * Returns a paginator that can be iterated with results
     *
     * @param int $page
     * @param int $perPage
     *
     * @return Pagerfanta
     */
    public function paginate(int $page = 1, int $perPage = 30): Pagerfanta
    {
        $models   = $this->model->getCollection();
        $response = $this->connection->get(
            $this->model->getRoute(),
            $this->model->getQueryEncoder()->encode($this->query->with($this->eagerLoad)->page($page)->perPage($perPage))
        );

        if (200 !== $response->getStatusCode()) {
            return new Pagerfanta(new FixedAdapter(0, $models));
        }

        $decoded = json_decode($response->getContent(), true, $depth = 512, JSON_THROW_ON_ERROR);
        $data    = $decoded;
        $total   = $perPage = count($data);

        if (!$decoded || !is_array($decoded)) {
            return new Pagerfanta(new FixedAdapter(0, $models));
        }

        if (isset($decoded['data'])) {
            // external response could contain a data element
            $data  = $decoded['data'];
            $total = $perPage = count($data);
        }

        $total   = $decoded['meta']['pagination']['total'] ?? $total;
        $page    = $decoded['meta']['pagination']['current_page'] ?? $page;
        $perPage = $decoded['meta']['pagination']['per_page'] ?? $perPage;

        foreach ($data as $row) {
            $models->add($this->model->new($row));
        }

        if ($models->count() > 0) {
            $this->eagerLoadRelationships($models, $data);
        }

        return (new Pagerfanta(new FixedAdapter($total, $models)))
            ->setMaxPerPage($perPage)
            ->setCurrentPage($page)
        ;
    }

    /**
     * Set the relationships that should be eager loaded
     *
     * @param mixed $relations Strings of relationship names, or an array
     *
     * @return $this
     */
    public function with(...$relations): self
    {
        $this->eagerLoad = (new GenerateRelationshipsToEagerLoad())($this->eagerLoad, ...$relations);

        return $this;
    }

    private function eagerLoadRelationships(Collection $models, array $data): void
    {
        foreach ($this->eagerLoad as $name) {
            if (false === strpos($name, '.')) {
                /** @var AbstractRelationship $load */
                $rel = $this->model->new()->getRelationship($name);
                $rel
                    ->with($this->findNestedRelationshipsFor($name))
                    ->addRelationshipResultsToModels($models, $name, $data)
                ;
            }
        }
    }

    /**
     * Get the deeply nested relations for a given top-level relation.
     *
     * @param string $relation
     *
     * @return array
     */
    private function findNestedRelationshipsFor(string $relation): array
    {
        $nested = [];

        // We are basically looking for any relationships that are nested deeper than
        // the given top-level relationship. We will just check for any relations
        // that start with the given top relations and add them to our arrays.
        foreach ($this->eagerLoad as $name) {
            if (Str::contains($name, '.') && Str::startsWith($name, $relation . '.')) {
                $nested[] = substr($name, strlen($relation . '.'));
            }
        }

        return $nested;
    }

    public function wherePrimaryKey($id): self
    {
        return $this->whereField($this->model->getPrimaryKeyName(), 'eq', $id);
    }

    /**
     * And a condition to filter by; field can be dot notation if the API supports it
     *
     * @param string $field
     * @param string $operator Equality operator e.g. lt, gt, eq, neq, lte, gte, like, in, !in
     * @param mixed  $value
     * @param string $andOr    Should the where be AND (expression) or OR (expression)
     *
     * @return ModelBuilder
     */
    public function whereField(string $field, string $operator, $value, string $andOr = 'and'): self
    {
        $map = [
            '='        => ExpressionBuilder::EQ,
            '!='       => ExpressionBuilder::NEQ,
            '>'        => ExpressionBuilder::GT,
            '>='       => ExpressionBuilder::GTE,
            '<'        => ExpressionBuilder::LT,
            '<='       => ExpressionBuilder::LTE,
            'LIKE'     => ExpressionBuilder::LIKE,
            'NOT LIKE' => ExpressionBuilder::NOT_LIKE,
            'IN'       => ExpressionBuilder::IN,
            'NOT IN'   => ExpressionBuilder::NOT_IN,
        ];

        if (array_key_exists($operator, $map)) {
            $operator = $map[$operator];
        }

        $method = 'or' === $andOr ? 'orWhere' : 'andWhere';

        $this->query->{$method}($this->query->expr()->comparison($field, $operator, $value));

        return $this;
    }

    public function whereIn(string $field, array $values): self
    {
        $this->query->andWhere($this->expr()->in($field, $values));

        return $this;
    }

    public function __call($name, $arguments)
    {
        if (method_exists($this->query, $name)) {
            $ret = $this->query->{$name}(...$arguments);

            if (Str::startsWith($name, 'get') || 'expr' === $name) {
                return $ret;
            }

            return $this;
        }

        throw new BadMethodCallException(sprintf('Method "%s" is not supported for pass through on "%s"', $name, static::class));
    }
}
