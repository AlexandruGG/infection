<?php

namespace Infection\Command;

use Infection\Process\Builder\ProcessBuilder;
use Infection\Process\Runner\InitialTestsRunner;
use Infection\TestFramework\Adapter\Factory;
use Infection\TestFramework\Adapter\PhpUnit\PhpUnitAdapter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InfectionCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $testFrameworkFactory = new Factory();
        $adapter = $testFrameworkFactory->create($input->getOption('test-framework'));

        $processBuilder = new ProcessBuilder($adapter);
        $process = $processBuilder->getProcess();

        $initialTestsRunner = new InitialTestsRunner($process, $output);
        $result = $initialTestsRunner->run();

        if (!$result->isSuccessful()) {
            $output->writeln(sprintf('<error>Tests do not pass. Error code %d</error>', $result->getExitCode()));
        }

        // generate mutation
    }

    protected function configure()
    {
        $this
            ->setName('run')
            ->setDescription('Runs the mutation testing.')
            ->addOption(
                'test-framework',
                null,
                InputOption::VALUE_REQUIRED,
                'Name of the Test framework to use (phpunit, phpspec)',
                'phpunit'
            )
        ;
    }
}