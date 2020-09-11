<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Tests\Support\Stubs\Entities;

use Somnambulist\Components\ApiClient\ValueObject;

/**
 * Class Permission
 *
 * @package    Somnambulist\Components\ApiClient\Tests\Support\Stubs\Entities
 * @subpackage Somnambulist\Components\ApiClient\Tests\Support\Stubs\Entities\Permission
 */
class Permission extends ValueObject
{

    protected array $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
