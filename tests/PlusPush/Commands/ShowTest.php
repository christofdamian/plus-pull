<?php

namespace tests\PlusPush\Commands;

use PlusPush\Commands\Show;
use PlusPush\GitHub\PullRequest;
use Symfony\Component\Console\Tester\CommandTester;

class ShowTest extends \PHPUnit_Framework_TestCase
{
    public function testConfigure()
    {
        $show = new Show();
        $this->assertEquals('show', $show->getName());
    }

    public function testExecute()
    {
        $pullRequests = array(
            new PullRequest()
        );

        $yaml = $this->getMock('Symfony\Component\Yaml\Yaml');
        $github = $this->getMockBuilder('PlusPush\GitHub')
            ->disableOriginalConstructor()
            ->getMock();
        $github->expects($this->once())
            ->method('getPullRequests')
            ->will($this->returnValue($pullRequests));

        $show = $this->getMockBuilder('PlusPush\Commands\Show')
            ->setMethods(array('getGitHub', 'getYaml'))
            ->getMock();
        $show->expects($this->once())
            ->method('getGitHub')
            ->will($this->returnValue($github));
        $show->expects($this->once())
            ->method('getYaml')
            ->will($this->returnValue($yaml));

        $tester = new CommandTester($show);
        $tester->execute(array());
    }
}
