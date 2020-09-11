<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient;

/**
 * Class ValueObject
 *
 * A base class for an API entity that exists only on the parent, that has no
 * children, but still requires attribute casting.
 *
 * @package    Somnambulist\Components\ApiClient
 * @subpackage Somnambulist\Components\ApiClient\ValueObject
 */
abstract class ValueObject extends AbstractModel
{

}
