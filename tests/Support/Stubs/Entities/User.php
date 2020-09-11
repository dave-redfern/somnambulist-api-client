<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Tests\Support\Stubs\Entities;

use Somnambulist\Components\ApiClient\Model;
use Somnambulist\Components\ApiClient\Relationships\HasMany;
use Somnambulist\Components\ApiClient\Relationships\HasOne;

class User extends Model
{

    protected array $routes = [
        'search' => 'users.list',
        'view'   => 'users.view',
    ];

    protected array $casts = [
        'id'         => 'uuid',
        'email'      => 'email',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected string $collectionClass = UserCollection::class;

    protected function address(): HasOne
    {
        return $this->hasOne(Address::class, 'address', false);
    }

    protected function addresses(): HasMany
    {
        return $this->hasMany(Address::class, 'addresses', 'type');
    }

    protected function contacts(): HasMany
    {
        return $this->hasMany(Contact::class, 'contacts', 'type');
    }

//    protected function group(): BelongsTo
//    {
//        return $this->belongsTo(Group::class, 'group', 'group_id');
//    }

    protected function groups(): HasMany
    {
        return $this->hasMany(Group::class, 'groups');
    }
}
