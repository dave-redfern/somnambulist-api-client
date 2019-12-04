<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Contracts;

use Somnambulist\Collection\Contracts\Collection;

/**
 * Interface EntityLocatorInterface
 *
 * @package Somnambulist\ApiClient\Contracts
 * @subpackage Somnambulist\ApiClient\Contracts\EntityLocatorInterface
 */
interface EntityLocatorInterface
{

    /**
     * Add the included data on the next request
     *
     * Should be cleared once any request has been made.
     *
     * @param string ...$include
     *
     * @return $this
     */
    public function with(string ...$include);

    /**
     * Find a record by primary id
     *
     * @param mixed $id
     *
     * @return object|null
     */
    public function find($id): ?object;

    /**
     * Find records by the criteria, and optionally order them
     *
     * Criteria is a key -> value array of fields and values, dependent on the API
     * implementation.
     *
     * Order by is a key -> value array of fields and direction (ASC, DESC) to order
     * the results by.
     *
     * @param array    $criteria
     * @param array    $orderBy
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return Collection
     */
    public function findBy(array $criteria = [], array $orderBy = [], int $limit = null, int $offset = null): Collection;

    /**
     * Finds the first record matching the criteria
     *
     * @param array $criteria
     * @param array $orderBy
     *
     * @return object|null
     */
    public function findOneBy(array $criteria, array $orderBy = []): ?object;
}
