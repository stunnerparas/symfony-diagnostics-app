<?php

namespace App\Diagnostics\DTO;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * This DTO is used to capture and validate parameters from a request (e.g., query parameters from an HTTP request, or arguments from a command).
 */
class DiagnosticsRequestDTO
{
    /**
     * @var array<string> A list of specific diagnostic provider keys to include (e.g., 'php', 'symfony').
     */
    #[Assert\Type('array')]
    #[Assert\All([
        new Assert\Type('string'),
        new Assert\Choice(choices: ['php', 'symfony', 'application', 'system', 'database', 'prometheus'])
    ])]
    public array $include = [];

    #[Assert\Choice(choices: ['basic', 'full'])]
    public ?string $level = null;
}
