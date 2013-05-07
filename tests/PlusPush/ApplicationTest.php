<?php

namespace tests\PlusPush;

use PlusPush\Application;

class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    public function testAllCommands()
    {
        $application = new Application();
        $this->assertTrue($application->has('show'));
    }
}
