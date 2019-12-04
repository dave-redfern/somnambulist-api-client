<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Tests\Stubs\Entities;

use Somnambulist\Domain\Entities\Types\Identity\EmailAddress;
use Somnambulist\Domain\Entities\Types\PhoneNumber;

/**
 * Class Contact
 *
 * @package    Somnambulist\ApiClient\Tests\Stubs\Entities
 * @subpackage Somnambulist\ApiClient\Tests\Stubs\Entities\Contact
 */
class Contact
{

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $name;

    /**
     * @var EmailAddress
     */
    public $email;

    /**
     * @var PhoneNumber
     */
    public $phoneNumber;
}
