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

        try {
            /** @var array<string, array<string, mixed>> $diagnostics */
            $diagnostics = $this->collector->collect($this->collector->getAvailableProviders());

            /** @var array<string, array<string, string>> $formattedDiagnostics */
            $formattedDiagnostics = [];
            foreach ($diagnostics as $providerKey => $data) {
                if (isset($data['error'])) {
                    $formattedDiagnostics[$providerKey] = $data;
                } else {
                    // This call to formatTopLevelDiagnosticDataForTwig can also throw \Throwable
                    $formattedDiagnostics[$providerKey] = $this->formatTopLevelDiagnosticDataForTwig($data);
                }
            }

            return $this->render('diagnostics/dashboard.html.twig', [
                'diagnostics' => $formattedDiagnostics,
                'timestamp' => time(),
                'executionTime' => microtime(true) - $startTime,
            ]);

        }
            /** @phpstan-ignore-line */ // FIX: Tells PHPStan to ignore "Dead catch" for this specific catch block.
        catch (\Throwable $e) {
            // Log the exception (e.g., via Monolog) and show a generic error message to the user.
            error_log('Error loading diagnostics dashboard: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->render('error/error.html.twig', [
                'message' => 'An error occurred while loading the diagnostics dashboard: ' . $e->getMessage(),
                'exception_message' => $this->getParameter('kernel.debug') ? $e->getMessage() : null,
                'exception_trace' => $this->getParameter('kernel.debug') ? $e->getTraceAsString() : null,
            ], new Response(null, Response::HTTP_INTERNAL_SERVER_ERROR));
        }
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
                try {
                    $encoded = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $formatted[$key] = $encoded;
                    } else {
                        $formatted[$key] = '[Complex Data - JSON Encoding Failed: ' . json_last_error_msg() . ']';
                    }
                } catch (\Throwable $e) { // Keep \Throwable here as json_encode can throw
                    $formatted[$key] = '[Complex Data - PHP Exception during JSON encoding: ' . $e->getMessage() . ']';
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
