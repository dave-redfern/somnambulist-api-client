<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Tests\Stubs\Entities;

use Somnambulist\Domain\Entities\Types\Geography\Country;

/**
 * Class Address
 *
 * @package    Somnambulist\ApiClient\Tests\Stubs\Entities
 * @subpackage Somnambulist\ApiClient\Tests\Stubs\Entities\Address
 */
class Address
{

    /**
     * @var string|null
     */
    public $addressLine1;

    /**
     * @var string|null
     */
    public $addressLine2;

    /**
     * @var string|null
     */
    public $addressLine3;

    /**
     * @var string|null
     */
    public $town;

    /**
     * @var string|null
     */
    public $county;

    /**
     * @var string|null
     */
    public $postcode;

    /**
     * @var Country|null
     */
    public $country;

    /**
     * Constructor.
     *
     * @param string|null  $addressLine1
     * @param string|null  $addressLine2
     * @param string|null  $addressLine3
     * @param string|null  $town
     * @param string|null  $county
     * @param string|null  $postcode
     * @param Country|null $country
     */
    public function __construct(?string $addressLine1, ?string $addressLine2, ?string $addressLine3, ?string $town, ?string $county, ?string $postcode, ?Country $country)
    {
        $this->addressLine1 = $addressLine1;
        $this->addressLine2 = $addressLine2;
        $this->addressLine3 = $addressLine3;
        $this->town         = $town;
        $this->county       = $county;
        $this->postcode     = $postcode;
        $this->country      = $country;
    }
}
