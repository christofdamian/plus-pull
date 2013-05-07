<?php

namespace tests\PlusPull;

use PlusPull\Application;

class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    public function testAllCommands()
    {
        $application = new Application();
        $this->assertTrue($application->has('check'));
    }
}
