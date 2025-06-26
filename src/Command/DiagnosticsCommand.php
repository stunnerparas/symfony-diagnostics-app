<?php

namespace App\Command;

use App\Service\SystemDiagnosticsCollectorInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\SerializerInterface;

#[AsCommand(
    name: 'app:diagnostics',
    description: 'Collects and displays system and application diagnostics.',
)]
class DiagnosticsCommand extends Command
{
    public function __construct(
        private SystemDiagnosticsCollectorInterface $collector,
        private SerializerInterface $serializer
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'include',
                'i',
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                'Specify specific diagnostic providers to include (e.g., --include=php --include=symfony)'
            )
            ->addOption(
                'level',
                'l',
                InputOption::VALUE_REQUIRED,
                'Specify a predefined diagnostic level (basic, full)'
            )
            ->addOption(
                'json',
                null,
                InputOption::VALUE_NONE,
                'Output diagnostics as JSON'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $startTime = microtime(true);

        $include = $input->getOption('include');
        $level = $input->getOption('level');
        $asJson = $input->getOption('json');

        if ($include && $level) {
            $io->error('Cannot specify both --include and --level options simultaneously.');
            return Command::INVALID;
        }

        try {
            $providersToCollect = $this->resolveProviders($include, $level);
            $diagnostics = $this->collector->collect($providersToCollect);

            if ($asJson) {
                $io->write($this->serializer->serialize($diagnostics, 'json', ['json_encode_options' => JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES]));
            } else {
                $this->displayDiagnosticData($io, $diagnostics);
            }

            $io->newLine();
            $io->info(sprintf('Diagnostics collected in %.2f seconds.', microtime(true) - $startTime));

        } catch (\InvalidArgumentException $e) {
            $io->error($e->getMessage());
            return Command::INVALID;
        } catch (\Throwable $e) {
            $io->error('An unexpected error occurred: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * @param array<string>|null $include
     * @param string|null $level
     * @return array<string>
     */
    private function resolveProviders(?array $include, ?string $level): array
    {
        if ($level) {
            return match ($level) {
                'basic' => ['php', 'symfony'],
                'full' => $this->collector->getAvailableProviders(),
                default => [],
            };
        }
        return $include ?? [];
    }

    /**
     * Displays diagnostic data to the console.
     * @param SymfonyStyle $io
     * @param array<string, array<string, mixed>> $data
     */
    private function displayDiagnosticData(SymfonyStyle $io, array $data): void
    {
        foreach ($data as $providerKey => $providerData) {
            $io->section(sprintf('%s Diagnostics', ucfirst($providerKey)));

            if (isset($providerData['error'])) {
                $io->error($providerData['error']);
                continue;
            }

            $rows = [];
            foreach ($providerData as $prop => $value) {
                $displayValue = $value;

                if (is_array($value) || is_object($value)) {
                    $displayValue = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                } elseif (is_bool($value)) {
                    $displayValue = $value ? 'True' : 'False';
                } elseif (is_null($value)) {
                    $displayValue = 'N/A';
                }

                $rows[] = [ucfirst(str_replace('_', ' ', $prop)), (string)$displayValue];
            }
            $io->table(['Property', 'Value'], $rows);
        }
    }
}
