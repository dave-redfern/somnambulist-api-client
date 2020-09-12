<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Persistence\Exceptions;

use Exception;
use Somnambulist\Collection\Contracts\Immutable;
use Somnambulist\Collection\FrozenCollection;
use Somnambulist\Collection\MutableCollection;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use function json_decode;

/**
 * Class ActionPersisterException
 *
 * @package    Somnambulist\Components\ApiClient\Persistence\Exceptions
 * @subpackage Somnambulist\Components\ApiClient\Persistence\Exceptions\ActionPersisterException
 *
 * @method ClientExceptionInterface getPrevious()
 */
class ActionPersisterException extends Exception
{

    private ResponseInterface $response;
    private MutableCollection $payload;
    private MutableCollection $errors;

    public function __construct($message, ClientExceptionInterface $error)
    {
        parent::__construct($message, $error->getCode(), $error);

        $this->response = $error->getResponse();
        $this->code     = $this->response->getStatusCode();
        $this->payload  = $payload = new MutableCollection(json_decode((string)$this->response->getContent(false), true, $depth = 512) ?? []);
        $this->errors   = $payload->value('errors', new MutableCollection());
    }

    public static function serverError($message, ClientExceptionInterface $e): self
    {
        $err = new static($message, $e);
        $err->code = 500;

        return $err;
    }

    public static function entityNotCreated(string $class, ClientExceptionInterface $e): self
    {
        $err = new static(sprintf('Entity of type "%s" could not be created', $class), $e);
        $err->code = 422;

        return $err;
    }

    public static function entityNotUpdated(string $class, string $id, ClientExceptionInterface $e): self
    {
        $err = new static(sprintf('Entity of type "%s" with identity "%s" could not be updated', $class, $id), $e);
        $err->code = 422;

        return $err;
    }

    public static function entityNotDestroyed(string $class, string $id, ClientExceptionInterface $e): self
    {
        $err = new static(sprintf('Entity of type "%s" with identity "%s" was not destroyed', $class, $id), $e);
        $err->code = 400;

        return $err;
    }

    public function getResponseMessage(): string
    {
        return $this->payload->value('message', 'There was an error from the API, the response could not be decoded');
    }

    public function getResponseTrace(): array
    {
        return $this->payload->value('trace', []);
    }

    public function getPayload(): Immutable
    {
        return $this->payload->freeze();
    }

    public function getErrors(): Immutable
    {
        return $this->errors->freeze();
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * Returns a collection of error fields mapped to form fields
     *
     * As APIs may have differently named fields to a UI form, this allows the API errors to be
     * pulled back to the form field for better correlation of errors from the API to the form
     * that was used for submission.
     *
     * Structure is an associative array: API field -> form field e.g.: user_id => user
     *
     * @param array $map
     *
     * @return Immutable
     */
    public function remapErrorFieldsToFormFieldNames(array $map): Immutable
    {
        $tmp = [];

        foreach ($this->errors as $key => $value) {
            $tmp[($map[$key] ?? $key)] = $value;
        }

        return new FrozenCollection($tmp);
    }
}
