<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Contracts;

use Somnambulist\ApiClient\Exceptions\EntityPersisterException;

/**
 * Interface EntityPersisterInterface
 *
 * @package    Somnambulist\ApiClient\Contracts
 * @subpackage Somnambulist\ApiClient\Contracts\EntityPersisterInterface
 */
interface EntityPersisterInterface
{

    /**
     * Create a new record returning the hydrated result
     *
     * Properties is an array of key => value pairs, or nested arrays of scalar data
     * to send via a POST request to an API end point. For complex data types or
     * file uploads, custom handling of resources will be necessary.
     *
     * All exceptions should be converted to the EntityPersisterException type and
     * include an appropriate ClientExceptionInterface trace exception that includes
     * the API response object.
     *
     * @param array $properties
     *
     * @return object
     * @throws EntityPersisterException
     */
    public function store(array $properties): object;

    /**
     * Update an existing record with identity $id
     *
     * Properties is an array of the values to change. This could be the new object
     * representation depending on the API implementation. Similar to store, complex
     * types require specific handling. Updates should make either PUT or PATCH requests
     * depending on the API implementation.
     *
     * All exceptions should be converted to the EntityPersisterException type and
     * include an appropriate ClientExceptionInterface trace exception that includes
     * the API response object.
     *
     * @param int|string $id
     * @param array      $properties
     *
     * @return object
     */
    public function update($id, array $properties): object;

    /**
     * Destroy an existing record with identity $id
     *
     * For the given identity, send a DELETE request to the API and handle the
     * response. True should be returned if successfully removed, or an exception
     * raised for all other cases.
     *
     * All exceptions should be converted to the EntityPersisterException type and
     * include an appropriate ClientExceptionInterface trace exception that includes
     * the API response object.
     *
     * @param int|string $id
     *
     * @return bool
     */
    public function destroy($id): bool;
}
