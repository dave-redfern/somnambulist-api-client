<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Tests\Support;

use Somnambulist\ApiClient\Contracts\ObjectHydratorInterface;
use Somnambulist\ApiClient\Mapper\ObjectMapper;
use Somnambulist\ApiClient\Mapper\ObjectHydratorContext;
use Somnambulist\ApiClient\Tests\Stubs\Entities\Address;
use Somnambulist\ApiClient\Tests\Stubs\Entities\Contact;
use Somnambulist\ApiClient\Tests\Stubs\Entities\User;
use Somnambulist\Collection\MutableCollection;
use Somnambulist\Domain\Entities\Types\Geography\Country;
use Somnambulist\Domain\Entities\Types\Identity\EmailAddress;
use Somnambulist\Domain\Entities\Types\Identity\Uuid;
use Somnambulist\Domain\Entities\Types\PhoneNumber;

/**
 * Class Factory
 *
 * @package Somnambulist\ApiClient\Tests\Support
 * @subpackage Somnambulist\ApiClient\Tests\Support\Factory
 */
class Factory
{

    public function makeMapper(array $hydrators = []): ObjectMapper
    {
        return new ObjectMapper($hydrators);
    }

    public function makeUserMapper(): ObjectMapper
    {
        $mapper = $this->makeMapper();
        $mapper->addHydrator(new class implements ObjectHydratorInterface {
            public function supports(): string
            {
                return User::class;
            }

            public function hydrate($resource, ObjectHydratorContext $context): object
            {
                $resource = new MutableCollection($resource);

                $user        = new User();
                $user->id    = new Uuid($resource->get('id'));
                $user->name  = $resource->get('name');
                $user->email = new EmailAddress($resource->get('email'));

                $resource->value('addresses', new MutableCollection())->each(function ($address) use ($user) {
                    $addr = new MutableCollection($address);

                    $user->addresses->add(
                        new Address(
                            $addr->get('address_line_1'),
                            $addr->get('address_line_2'),
                            $addr->get('address_line_3'),
                            $addr->get('town'),
                            $addr->get('county'),
                            $addr->get('postcode'),
                            $addr->get('country') ? Country::memberOrNullBy('name', $addr->get('country')) : null
                        )
                    );
                });

                $resource->value('contacts', new MutableCollection())->each(function ($con) use ($user, $context) {
                    $contact              = new Contact();
                    $contact->type        = $con['type'];
                    $contact->name        = $con['contact']['name'];
                    $contact->email       = $con['contact']['email'] ? new EmailAddress($con['contact']['email']) : null;
                    $contact->phoneNumber = $con['contact']['phone'] ? new PhoneNumber($con['contact']['phone']) : null;

                    $user->contacts->add($contact);
                });

                return $user;
            }
        });

        return $mapper;
    }
}
