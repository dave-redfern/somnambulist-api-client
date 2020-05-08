<?php declare(strict_types=1);

namespace Somnambulist\ApiClient\Client\Decorators;

use function array_key_exists;
use function array_merge;
use function is_array;
use function json_encode;
use function ksort;
use function sha1;
use function sprintf;

/**
 * Class RequestTracker
 *
 * Tracks the number of times a particular request has been made. Class must be
 * static as we want it to persistent outside of the kernel getting restarted when
 * under testing.
 *
 * @package    Somnambulist\ApiClient\Client\Decorators
 * @subpackage Somnambulist\ApiClient\Client\Decorators\RequestTracker
 */
class RequestTracker
{

    /**
     * @var RequestTracker
     */
    private static $instance;
    
    /**
     * Array of hash -> counts
     *
     * @var array
     */
    private $requests = [];

    private function __construct()
    {
        
    }

    public static function instance(): self
    {
        if (!self::$instance instanceof RequestTracker) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }

    public function requests(): array
    {
        return $this->requests;
    }

    public function reset(): void
    {
        $this->requests = [];
    }

    public function makeHash(string $route, array $request = []): string
    {
        // ensure that all array keys are sorted consistently for better hashing
        foreach ($request as $key => $value) {
            if (is_array($value)) {
                ksort($value);
            }

            $request[$key] = $value;
        }

        $data = array_merge(['route' => $route, $request]);
        ksort($data);

        return sha1(json_encode($data));
    }

    /**
     * Adds the hash, returning the modified hash_<count> string
     *
     * @param string $hash
     *
     * @return string
     */
    public function add(string $hash): string
    {
        if (!$this->has($hash)) {
            $this->requests[$hash] = 0;
        }

        $this->requests[$hash]++;

        return sprintf('%s_%s', $hash, $this->requests[$hash]);
    }

    public function has(string $hash): bool
    {
        return array_key_exists($hash, $this->requests);
    }

    public function count(string $hash): int
    {
        return $this->requests[$hash] ?? 0;
    }
}
