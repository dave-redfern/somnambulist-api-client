<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient;

use RuntimeException;
use Somnambulist\Components\ApiClient\Client\Contracts\ConnectionInterface;
use Somnambulist\Components\AttributeModel\AttributeCaster;
use function get_class;
use function sprintf;

/**
 * Class Manager
 *
 * @package    Somnambulist\Components\ApiClient
 * @subpackage Somnambulist\Components\ApiClient\Manager
 */
final class Manager
{

    private static ?Manager $instance = null;
    private ConnectionManager $connections;
    private AttributeCaster $caster;

    public function __construct(array $connections = [], iterable $casters = [])
    {
        $this->connections = new ConnectionManager($connections);
        $this->caster      = new AttributeCaster($casters);

        self::$instance = $this;
    }

    /**
     * Build or extend an existing Manager instance
     *
     * @param array          $connections
     * @param array|iterable $casters
     *
     * @return Manager
     */
    public static function factory(array $connections, iterable $casters): Manager
    {
        if (!self::$instance instanceof Manager) {
            return new Manager($connections, $casters);
        }

        self::$instance->connections->forAll($connections);
        self::$instance->caster->addAll($casters);

        return self::$instance;
    }

    public static function instance(): self
    {
        if (!self::$instance instanceof Manager) {
            throw new RuntimeException(
                sprintf(
                    '%s has not been instantiated; you must first create a new instance before accessing the registry statically',
                    static::class
                )
            );
        }

        return self::$instance;
    }

    public function connect(Model $model): ConnectionInterface
    {
        return $this->connections->for(get_class($model));
    }

    public function connection(): ConnectionManager
    {
        return $this->connections;
    }

    public function caster(): AttributeCaster
    {
        return $this->caster;
    }
}
