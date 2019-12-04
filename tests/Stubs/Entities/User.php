<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Tests\Stubs\Entities;

use Somnambulist\Collection\MutableCollection;
use Somnambulist\Domain\Entities\Types\Identity\EmailAddress;
use Somnambulist\Domain\Entities\Types\Identity\Uuid;

/**
 * Class Organization
 *
 * @package    Somnambulist\ApiClient\Tests\Stubs\Entities
 * @subpackage Somnambulist\ApiClient\Tests\Stubs\Entities\Organization
 */
class User
{

    /**
     * @var Uuid
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var EmailAddress
     */
    public $email;

    /**
     * @var MutableCollection|Address[]
     */
    public $addresses;

    /**
     * @var MutableCollection|Contact[]
     */
    public $contacts;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->addresses = new MutableCollection();
        $this->contacts  = new MutableCollection();
    }

    /**
     * @return Uuid
     */
    public function id()
    {
        return $this->id;
    }
}
