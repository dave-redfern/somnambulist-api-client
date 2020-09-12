<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Client\Connection\Decoders;

use Somnambulist\Components\ApiClient\Client\Contracts\ResponseDecoderInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use function in_array;
use function is_numeric;
use function is_string;
use function json_decode;
use const JSON_THROW_ON_ERROR;

/**
 * Class SimpleJsonDecoder
 *
 * @package    Somnambulist\Components\ApiClient\Client\Connection\Decoders
 * @subpackage Somnambulist\Components\ApiClient\Client\Connection\Decoders\SimpleJsonDecoder
 */
class SimpleJsonDecoder implements ResponseDecoderInterface
{

    public function decode(ResponseInterface $response, array $ok = [200]): array
    {
        if (!in_array($response->getStatusCode(), $ok)) {
            return [];
        }

        return json_decode((string)$response->getContent(), true, $depth = 512, JSON_THROW_ON_ERROR);
    }

    public function object(array $rawData): array
    {
        return $this->ensureSingleArray($rawData);
    }

    public function collection(array $rawData): array
    {
        return $this->ensureArrayOfArrays($rawData);
    }

    private function ensureSingleArray(array $data): array
    {
        if (isset($data['data'])) {
            // external response could contain a data element
            $data = $data['data'];
        }
        if (is_numeric(array_key_first($data))) {
            // if for some reason we had [data => [ [what we want] ]], grab the first element
            $data = $data[0];
        }

        return $data;
    }

    private function ensureArrayOfArrays(array $data): array
    {
        if (empty($data)) {
            return $data;
        }
        if (isset($data['data'])) {
            // external response could contain a data element
            $data = $data['data'];
        }
        if (is_string(array_key_first($data))) {
            // treat single object responses like collections
            // will fail if the object was [attributes => [what we really want], relations => []] etc.
            $data = [$data];
        }

        return $data;
    }
}
