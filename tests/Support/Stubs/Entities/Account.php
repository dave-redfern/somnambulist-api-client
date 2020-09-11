<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Tests\Support\Stubs\Entities;

use Somnambulist\Components\ApiClient\Model;

/**
 * Class Account
 *
 * @package    Somnambulist\Components\ApiClient\Tests\Support\Stubs\Entities
 * @subpackage Somnambulist\Components\ApiClient\Tests\Support\Stubs\Entities\Account
 */
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

    protected function related()
    {
        return $this->hasMany(AccountRelation::class, 'related');
    }
}
