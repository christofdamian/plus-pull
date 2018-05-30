<?php

namespace tests\PlusPull;

use PHPUnit\Framework\TestCase;
use PlusPull\Application;

class ApplicationTest extends TestCase
{
    public function testAllCommands()
    {
        $application = new Application();
        $this->assertTrue($application->has('check'));
    }
}
