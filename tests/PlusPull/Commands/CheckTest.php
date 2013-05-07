<?php

namespace tests\PlusPull\Commands;

use PlusPull\Commands\Check;
use PlusPull\GitHub\PullRequest;
use Symfony\Component\Console\Tester\CommandTester;

class CheckTest extends \PHPUnit_Framework_TestCase
{
    public function testConfigure()
    {
        $check = new Check();
        $this->assertEquals('check', $check->getName());
    }

    public function testExecute()
    {
        $pullRequests = array(
            new PullRequest()
        );

        $config = array(
            'authorization' => array(
                'username' => 'testuser',
                'password' => 'secret',
            ),
            'repository' => array(
                'name' => 'test-repo',
                'username' => 'test-owner',
                'status' => false,
            ),
        );

        $yaml = $this->getMockBuilder('Symfony\Component\Yaml\Yaml')
            ->disableOriginalConstructor()
            ->getMock();
        $yaml->staticExpects($this->any())
            ->method('parse')
            ->will($this->returnValue($config));

        $github = $this->getMockBuilder('PlusPull\GitHub')
            ->disableOriginalConstructor()
            ->getMock();
        $github->expects($this->once())
            ->method('getPullRequests')
            ->will($this->returnValue($pullRequests));

        $check = $this->getMockBuilder('PlusPull\Commands\Check')
            ->setMethods(array('getGitHub', 'getYaml'))
            ->getMock();
        $check->expects($this->once())
            ->method('getGitHub')
            ->will($this->returnValue($github));
        $check->expects($this->once())
            ->method('getYaml')
            ->will($this->returnValue($yaml));

        $tester = new CommandTester($check);
        $tester->execute(array());
    }
}
