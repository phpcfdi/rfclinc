<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\Tests\Application\Cli;

use EngineWorks\ProgressStatus\NullProgress;
use EngineWorks\ProgressStatus\Status;
use PhpCfdi\RfcLinc\Application\Cli\OutputLogger;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Output\OutputInterface;

class OutputLoggerTest extends TestCase
{
    public function testOutputProperty()
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject|OutputInterface $output */
        $output = $this->createMock(OutputInterface::class);
        $logger = new OutputLogger($output);

        $this->assertSame($output, $logger->output());
    }

    public function testStatusString()
    {
        $status = new Status(510, 500, 100, 0, 'foo');

        /** @var \PHPUnit\Framework\MockObject\MockObject|OutputLogger $logger */
        $logger = $this->getMockBuilder(OutputLogger::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['statusToString'])
            ->getMock();
        $message = $logger->statusToString($status);

        $this->assertContains('100 lines', $message);
        $this->assertContains('0:00:10', $message);
        $this->assertContains('[10 / sec]', $message);
    }

    public function testUpdateCallInfoWithValidProgressStatus()
    {
        $status = new Status(510, 500, 100, 0, 'foo');

        /** @var \PHPUnit\Framework\MockObject\MockObject|OutputLogger $logger */
        $logger = $this->getMockBuilder(OutputLogger::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['update'])
            ->getMock();
        $logger->expects($this->once())->method('info');
        $logger->expects($this->once())->method('statusToString');

        /** @var \PHPUnit\Framework\MockObject\MockObject|NullProgress $progress */
        $progress = $this->createMock(NullProgress::class);
        $progress->method('getStatus')->willReturn($status);

        $logger->update($progress);
    }

    public function testUpdateWithNotProgressInterfaceObject()
    {
        // allows any object that is not ProgressInterface
        /** @var \PHPUnit\Framework\MockObject\MockObject|\SplSubject $subjectNotProgressInterface */
        $subjectNotProgressInterface = $this->createMock(\SplSubject::class);

        /** @var \PHPUnit\Framework\MockObject\MockObject|OutputLogger $logger */
        $logger = $this->getMockBuilder(OutputLogger::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['update'])
            ->getMock();
        $logger->expects($this->never())->method('statusToString');

        $logger->update($subjectNotProgressInterface);
    }

    /**
     * @param string $message
     * @param string $type
     * @param string $expected
     * @testWith ["foo", "bar", "<bar>foo</bar>"]
     *           ["foo", "", "foo"]
     *           ["1 < 2", "", "1 \\< 2"]
     */
    public function testDecorate(string $message, string $type, string $expected)
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject|OutputLogger $logger */
        $logger = $this->getMockBuilder(OutputLogger::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['decorate'])
            ->getMock();
        $decorated = $logger->decorate($message, $type);
        $this->assertSame($expected, $decorated);
    }

    public function testStyleFromLogLevel()
    {
        $map = [
            LogLevel::WARNING => 'info',
            LogLevel::INFO => '',
            LogLevel::NOTICE => 'comment',
            LogLevel::DEBUG => '',
            LogLevel::ALERT => 'error',
            LogLevel::CRITICAL => 'error',
            LogLevel::EMERGENCY => 'error',
            LogLevel::ERROR => 'error',
        ];

        /** @var \PHPUnit\Framework\MockObject\MockObject|OutputLogger $logger */
        $logger = $this->getMockBuilder(OutputLogger::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['styleFromLogLevel'])
            ->getMock();

        foreach ($map as $loglevel => $expected) {
            $this->assertSame($expected, $logger->styleFromLogLevel($loglevel), "style does not match on $loglevel");
        }
    }
}
