<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Tests\Support\Stubs\Entities;

use Somnambulist\Components\ApiClient\Relationships\BelongsTo;
use Somnambulist\Components\ApiClient\ValueObject;

class AccountRelation extends ValueObject
{
    protected array $casts = [
        'related_account_id' => 'uuid',
    ];

    protected function account(): BelongsTo
    {
        return new BelongsTo($this, new Account(), 'related', 'related_account_id', false);
    }
}
