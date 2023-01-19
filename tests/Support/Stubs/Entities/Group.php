<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Tests\Support\Stubs\Entities;

use Somnambulist\Components\ApiClient\Model;
use Somnambulist\Components\ApiClient\Relationships\HasMany;

class Group extends Model
{
    protected array $routes = [
        'search' => 'groups.list',
        'view'   => 'groups.view',
    ];

    protected array $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected function permissions(): HasMany
    {
        return $this->hasMany(Permission::class, 'permissions');
    }
}
