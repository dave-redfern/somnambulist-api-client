<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Persistence\Contracts;

use Somnambulist\Components\ApiClient\Persistence\Exceptions\EntityPersisterException;

/**
 * Interface EntityPersisterInterface
 *
 * @package    Somnambulist\Components\ApiClient\Contracts
 * @subpackage Somnambulist\Components\ApiClient\Persistence\Contracts\EntityPersisterInterface
 */
interface EntityPersisterInterface
{

    /**
     * Create a new record returning the hydrated result
     *
     * All exceptions should be converted to the EntityPersisterException type and
     * include an appropriate ClientExceptionInterface trace exception that includes
     * the API response object.
     *
     * @param ApiActionInterface $action
     *
     * @return object
     * @throws \Somnambulist\Components\ApiClient\Persistence\Exceptions\EntityPersisterException
     */
    public function create(ApiActionInterface $action): object;

    /**
     * Update an existing record, returning the new representation
     *
     * All exceptions should be converted to the EntityPersisterException type and
     * include an appropriate ClientExceptionInterface trace exception that includes
     * the API response object.
     *
     * @param ApiActionInterface $action
     *
     * @return object
     */
    public function update(ApiActionInterface $action): object;

    /**
     * Destroy an existing record
     *
     * For the given identity, send a DELETE request to the API and handle the
     * response. True should be returned if successfully removed, or an exception
     * raised for all other cases.
     *
     * All exceptions should be converted to the EntityPersisterException type and
     * include an appropriate ClientExceptionInterface trace exception that includes
     * the API response object.
     *
     * @param ApiActionInterface $action
     *
     * @return bool
     */
    public function destroy(ApiActionInterface $action): bool;
}
