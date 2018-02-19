<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\Application\Cli;

use EngineWorks\ProgressStatus\ProgressInterface;
use EngineWorks\ProgressStatus\Status;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;
use SplObserver;
use SplSubject;
use Symfony\Component\Console\Output\OutputInterface;

class OutputLogger extends AbstractLogger implements SplObserver
{
    /** @var OutputInterface */
    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function output(): OutputInterface
    {
        return $this->output;
    }

    public function debug($message, array $context = [])
    {
        if ($this->output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
            parent::debug($message, $context);
        }
    }

    public function log($level, $message, array $context = [])
    {
        $style = $this->styleFromLogLevel((string) $level);
        $message = $this->decorate($message, $style);
        $this->output->writeln($message);
    }

    public function styleFromLogLevel(string $level): string
    {
        if (LogLevel::WARNING === $level) {
            return 'info';
        }
        if (LogLevel::NOTICE === $level) {
            return 'comment';
        }
        if (LogLevel::INFO === $level || LogLevel::DEBUG === $level) {
            return '';
        }
        return 'error';
    }

    public function decorate(string $message, string $type = ''): string
    {
        $decorated = str_replace('<', '\<', $message);
        if ('' !== $type) {
            $decorated = '<' . $type . '>' . $decorated . '</' . $type . '>';
        }
        return $decorated;
    }

    /**
     * @param SplSubject|ProgressInterface $subject
     */
    public function update(SplSubject $subject)
    {
        if (! $subject instanceof ProgressInterface) {
            return;
        }

        $this->info($this->statusToString($subject->getStatus()));
    }

    public function statusToString(Status $status)
    {
        return vsprintf('Processed %s lines in %s [%d / sec]', [
            number_format($status->getValue()),
            $status->getIntervalElapsed()->format('%h:%I:%S'),
            number_format($status->getSpeed()),
        ]);
    }
}
