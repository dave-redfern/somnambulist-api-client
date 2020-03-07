<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Exceptions;

use Exception;
use Somnambulist\Collection\Contracts\Collection;
use Somnambulist\Collection\FrozenCollection;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Throwable;
use function json_decode;
use const JSON_ERROR_NONE;
use const JSON_THROW_ON_ERROR;

/**
 * Class ApiErrorException
 *
 * @package    Somnambulist\ApiClient\Exceptions
 * @subpackage Somnambulist\ApiClient\Exceptions\ApiErrorException
 */
class ApiErrorException extends Exception implements ClientExceptionInterface
{

    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * @var FrozenCollection
     */
    private $payload;

    public function __construct(ResponseInterface $response, string $message = '', Throwable $previous = null)
    {
        $this->response = $response;
        $this->payload  = new FrozenCollection(json_decode((string)$response->getContent(false), true, $depth = 512) ?? []);

        parent::__construct(
            $message ?: $this->payload['message'] ?? 'Error from calling API',
            $response->getStatusCode(false),
            $previous
        );
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function getPayload(): Collection
    {
        return $this->payload;
    }
}
