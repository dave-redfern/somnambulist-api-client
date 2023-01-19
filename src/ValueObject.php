<?php declare(strict_types=1);

namespace Somnambulist\Components\ApiClient;

/**
 * A base class for an API entity that exists only on the parent, that has no children, but still requires
 * attribute casting.
 */
abstract class ValueObject extends AbstractModel
{

}
