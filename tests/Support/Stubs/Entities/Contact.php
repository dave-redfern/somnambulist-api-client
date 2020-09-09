<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Tests\Support\Stubs\Entities;

use Somnambulist\Components\ApiClient\ValueObject;

class Contact extends ValueObject
{

    protected array $casts = [
        'email' => 'email',
        'phone' => 'phone',
    ];

}
