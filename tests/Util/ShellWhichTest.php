<?php

declare(strict_types=1);

namespace PhpCfdi\RfcLinc\Tests\Util;

use PhpCfdi\RfcLinc\Util\ShellWhich;
use PHPUnit\Framework\TestCase;

class ShellWhichTest extends TestCase
{
    /** @var ShellWhich */
    private $which;

    protected function setUp()
    {
        parent::setUp();
        $this->which = new ShellWhich();
    }

    public function testVerifyWithKnownExecutable()
    {
        $this->assertSame('/bin/sh', $this->which->__invoke('sh'), 'sh does not exists? is this linux?');
    }

    public function testVerifyWithNotFound()
    {
        $this->assertSame('', $this->which->__invoke('this-command-does-not-exists'));
    }
}
