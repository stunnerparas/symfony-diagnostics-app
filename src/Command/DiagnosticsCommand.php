<?php

namespace App\Command;

use App\Service\SystemDiagnosticsCollectorInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Diagnostics\Exception\InvalidProviderException;

/**
 * Symfony console command to display system and application diagnostics.
 */
#[AsCommand(
    name: 'app:diagnostics',
    description: 'Display system and application diagnostics information',
    aliases: ['diagnostics', 'app:sysinfo']
)]
class DiagnosticsCommand extends Command
{
    /**
     * @param SystemDiagnosticsCollectorInterface $collector The diagnostics collector service, autowired via interface.
     */
    public function __construct(
        private SystemDiagnosticsCollectorInterface $collector
    ) {
        parent::__construct();
    }

    /**
     * Configures the command, defining its arguments and options.
     */
    protected function configure(): void
    {
        $this
            ->addArgument(
                'providers',
                InputArgument::IS_ARRAY | InputArgument::OPTIONAL, // Allows multiple values and is optional
                'Specific providers to show (e.g., php symfony system). Leave empty to show all enabled providers.'
            );
    }

    /**
     * Executes the command logic.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $providers = $input->getArgument('providers') ?: [];

        $io->title('System Diagnostics');

        try {
            $diagnostics = $this->collector->collect($providers);

            foreach ($diagnostics as $providerKey => $data) {
                $io->section(ucfirst($providerKey) . ' Diagnostics');

                if (isset($data['error'])) {
                    $io->error('Error: ' . $data['error']);
                    continue;
                }

                $this->displayDiagnosticData($io, $data);
            }

            $io->success('Diagnostics collection completed successfully.');
            return Command::SUCCESS;

        } catch (InvalidProviderException $e) {
            $io->error($e->getMessage());
            $io->text('Available providers: ' . implode(', ', $this->collector->getAvailableProviders()));
            return Command::INVALID;
        } catch (\Throwable $e) {
            $io->error('Failed to collect diagnostics: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Helper to display diagnostic data in a SymfonyStyle table.
     */
    private function displayDiagnosticData(SymfonyStyle $io, array $data): void
    {
        $rows = [];
        foreach ($data as $key => $value) {
            $rows[] = [$key, $this->formatValue($value)];
        }

        $io->table(['Property', 'Value'], $rows);
    }

    /**
     * Helper to format different types of values for console output.
     */
    private function formatValue(mixed $value): string
    {
        // Format arrays as pretty-printed JSON
        if (is_array($value)) {
            return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }

        // Format booleans as 'true' or 'false' strings
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return (string) $value;
    }
}
