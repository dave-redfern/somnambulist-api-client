<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Tests\Support\Stubs\Entities;

use Somnambulist\Components\ApiClient\Model;

class Group extends Model
{

    protected array $routes = [
        'search' => 'groups.list',
        'view'   => 'groups.view',
    ];

    protected array $casts = [
        'id'         => 'uuid',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
