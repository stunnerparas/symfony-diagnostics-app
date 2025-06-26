<?php

namespace App\Diagnostics\Exception;

/**
 * Custom exception for errors occurring specifically within diagnostic providers.
 * This helps in clearly identifying errors that originate from the diagnostics collection process.
 */
class DiagnosticsException extends \RuntimeException
{
    public function __construct(string $provider, string $message, ?\Throwable $previous = null)
    {
        parent::__construct(
            sprintf('Diagnostics error in provider "%s": %s', $provider, $message),
            0,
            $previous
        );
    }
}
