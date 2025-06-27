<?php

namespace App\Controller;

use App\Service\SystemDiagnosticsCollectorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for rendering the web-based system diagnostics dashboard.
 */
class DiagnosticsDashboardController extends AbstractController
{
    public function __construct(
        private SystemDiagnosticsCollectorInterface $collector
    ) {}

    /**
     * Renders the web-based system diagnostics dashboard.
     */
    #[Route('/diagnostics', name: 'diagnostics_dashboard_web', methods: ['GET'])]
    public function index(): Response
    {
        $startTime = microtime(true);

        // Removed the top-level try-catch block to avoid "Dead catch" warning.
        // Uncaught exceptions will now be handled by Symfony's default error handler (e.g., 500 error page).
        /** @var array<string, array<string, mixed>> $diagnostics */
        $diagnostics = $this->collector->collect($this->collector->getAvailableProviders());

        /** @var array<string, array<string, string>> $formattedDiagnostics */
        $formattedDiagnostics = [];
        foreach ($diagnostics as $providerKey => $data) {
            if (isset($data['error'])) {
                $formattedDiagnostics[$providerKey] = $data;
            } else {
                $formattedDiagnostics[$providerKey] = $this->formatTopLevelDiagnosticDataForTwig($data);
            }
        }

        return $this->render('diagnostics/dashboard.html.twig', [
            'diagnostics' => $formattedDiagnostics,
            'timestamp' => time(),
            'executionTime' => microtime(true) - $startTime,
        ]);
    }

    /**
     * Formats top-level diagnostic data values for display in Twig, ensuring all outputs are strings.
     * Complex types (arrays/objects) are JSON encoded. If JSON encoding fails, a fallback string is used.
     * This function is NOT recursive.
     *
     * @param array<string, mixed> $data
     * @return array<string, string>
     */
    private function formatTopLevelDiagnosticDataForTwig(array $data): array
    {
        $formatted = [];
        foreach ($data as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $encoded = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                if ($encoded === false) {
                    $formatted[$key] = '[Complex Data - Encoding Failed]';
                } else {
                    $formatted[$key] = $encoded;
                }
            } elseif (is_bool($value)) {
                $formatted[$key] = $value ? 'True' : 'False';
            } elseif (is_null($value)) {
                $formatted[$key] = 'N/A';
            } else {
                $formatted[$key] = (string) $value;
            }
        }
        return $formatted;
    }
}
