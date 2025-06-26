<?php

namespace App\Diagnostics\Exception;

/**
 * Custom exception for when a requested diagnostic provider does not exist or is unknown.
 * This indicates an invalid input, making it appropriate to extend InvalidArgumentException.
 */
class InvalidProviderException extends \InvalidArgumentException
{
    public function __construct(string $provider)
    {
        parent::__construct(sprintf('Invalid diagnostics provider: "%s"', $provider));
    }
}
