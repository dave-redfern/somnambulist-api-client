<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient;

use Pagerfanta\Pagerfanta;
use Somnambulist\Components\Collection\Contracts\Collection;

class EntityLocator
{
    private Manager $manager;
    private string $class;
    private array $include = [];

    public function __construct(Manager $manager, string $class)
    {
        $this->manager = $manager;
        $this->class   = $class;
    }

    public function include(string ...$include): self
    {
        $this->include = $include;

        return $this;
    }

    public function getClassName(): string
    {
        return $this->class;
    }

    public function find($id): ?object
    {
        return $this->query()->find($id);
    }

    public function findOrFail($id): object
    {
        return $this->query()->findOrFail($id);
    }

    public function findBy(array $criteria = [], array $orderBy = [], ?int $limit = null, ?int $offset = null): Collection
    {
        return $this->query()->findBy($criteria, $orderBy, $limit, (string)$offset);
    }

    public function findOneBy(array $criteria = [], array $orderBy = []): ?object
    {
        return $this->query()->findOneBy($criteria, $orderBy);
    }

    public function findByPaginated(array $criteria = [], array $orderBy = [], int $page = 1, int $perPage = 30): Pagerfanta
    {
        $qb = $this->query();

        foreach ($criteria as $field => $value) {
            $qb->andWhere($qb->expr()->eq($field, $value));
        }
        foreach ($orderBy as $field => $dir) {
            $qb->addOrderBy($field, $dir);
        }

        return $qb->paginate($page, $perPage);
    }

    protected function query(): ModelBuilder
    {
        $qb = $this->class::include(...$this->include);

        $this->include = [];

        return $qb;
    }
}
