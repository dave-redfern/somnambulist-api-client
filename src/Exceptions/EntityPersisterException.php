<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Exceptions;

use Exception;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Throwable;

/**
 * Class EntityPersisterException
 *
 * @package    Somnambulist\ApiClient\Exceptions
 * @subpackage Somnambulist\ApiClient\Exceptions\EntityPersisterException
 *
 * @method ClientExceptionInterface getPrevious()
 */
class EntityPersisterException extends Exception
{

    public function __construct($message, Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }

    public static function serverError($message, ClientExceptionInterface $e): self
    {
        $err = new static($message, $e);
        $err->code = 500;

        return $err;
    }

    public static function entityNotCreated(string $class, ClientExceptionInterface $error): self
    {
        $err = new static(sprintf('Entity of type "%s" could not be created', $class), $error);
        $err->code = 422;

        return $err;
    }

    public static function entityNotUpdated(string $class, string $id, ClientExceptionInterface $error): self
    {
        $err = new static(sprintf('Entity of type "%s" with identity "%s" could not be updated', $class, $id), $error);
        $err->code = 422;

        return $err;
    }

    public static function entityNotDestroyed(string $class, string $id, ClientExceptionInterface $error): self
    {
        $err = new static(sprintf('Entity of type "%s" with identity "%s" was not destroyed', $class, $id), $error);
        $err->code = 400;

        return $err;
    }

    public function getClientErrorMessage(): string
    {
        return $this->getPrevious()->getMessage();
    }

    public function getClientTrace(): array
    {
        return $this->getPrevious()->getTrace();
    }

    public function getClientResponse(): ResponseInterface
    {
        return $this->getPrevious()->getResponse();
    }
}
