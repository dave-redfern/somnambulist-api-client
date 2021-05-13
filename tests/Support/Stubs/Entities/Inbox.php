<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient\Tests\Support\Stubs\Entities;

use Somnambulist\Components\ApiClient\Model;

/**
 * Class Inbox
 *
 * @package    Somnambulist\Components\ApiClient\Tests\Support\Stubs\Entities
 * @subpackage Somnambulist\Components\ApiClient\Tests\Support\Stubs\Entities\Inbox
 */
class Inbox extends Model
{

    protected array $routes = [
        'search' => 'inbox.list',
        'view'   => 'inbox.item',
    ];

    protected array $casts = [

    ];
}
