<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Client\Contracts;

use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Interface ResponseDecoderInterface
 *
 * @package    Somnambulist\Components\ApiClient\Client\Contracts
 * @subpackage Somnambulist\Components\ApiClient\Client\Contracts\ResponseDecoderInterface
 */
interface ResponseDecoderInterface
{
    /**
     * Decodes the response content to a PHP array
     *
     * The decode process can check for expected response codes, but ultimately should
     * return the main content as an array that can then be further processed by other
     * helper methods.
     *
     * This method must raise exceptions if the data fails to decode successfully.
     *
     * @param ResponseInterface $response
     * @param array|int[]       $ok
     *
     * @return array
     */
    public function decode(ResponseInterface $response, array $ok = [200]): array;

    /**
     * Return a single "object" as an array
     *
     * The object should contain only the attributes we are interested in. This should include
     * any related attributes and relationships but without prefixes. For example: in JSON API
     * attributes are usually enclosed in `attributes` tags. These should be removed and for
     * relationships, the related data pulled into an attribute for the relationship.
     *
     * @param array $rawData
     *
     * @return array
     */
    public function object(array $rawData): array;

    /**
     * Return a collection of "objects" as an array
     *
     * i.e.: is: [[], [], [], []]  not [data => [[],[]]] or [key->value, key->value]
     *
     * @param array $rawData
     *
     * @return array
     */
    public function collection(array $rawData): array;
}
