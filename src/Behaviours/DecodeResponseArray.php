<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Behaviours;

use function is_string;

/**
 * Trait DecodeResponseArray
 *
 * @package    Somnambulist\Components\ApiClient\Behaviours
 * @subpackage Somnambulist\Components\ApiClient\Behaviours\DecodeResponseArray
 */
trait DecodeResponseArray
{

    /**
     * Ensures that the array data is a set of arrays
     *
     * i.e.: is: [[], [], [], []]  not [data => [[],[]]] or [key->value, key->value]
     *
     * @param array $data
     *
     * @return array|array[]
     */
    protected function ensureFlatArrayOfArrays(array $data): array
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
            $data = [$data];
        }

        return $data;
    }
}
