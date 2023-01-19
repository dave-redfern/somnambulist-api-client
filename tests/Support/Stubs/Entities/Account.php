<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Tests\Support\Stubs\Entities;

use Somnambulist\Components\ApiClient\Model;
use Somnambulist\Components\ApiClient\Relationships\HasMany;

class Account extends Model
{
    protected array $routes = [
        'search' => 'accounts.list',
        'view'   => 'accounts.view',
    ];

    protected array $casts = [
        'id'         => 'uuid',
        'account_id' => 'uuid',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected function related(): HasMany
    {
        return $this->hasMany(AccountRelation::class, 'related');
    }

    protected function relatedAccounts(): HasMany
    {
        return $this->hasMany(AccountRelation::class, 'related');
    }

    protected function related_accounts(): HasMany
    {
        return $this->relatedAccounts();
    }
}
