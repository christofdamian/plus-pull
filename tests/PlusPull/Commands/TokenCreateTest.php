<?php

namespace tests\PlusPull\Commands;

use Symfony\Component\Console\Helper\HelperSet;

use PlusPull\Commands\TokenCreate;
use Symfony\Component\Console\Tester\CommandTester;

class TokenCreateTest extends \PHPUnit_Framework_TestCase
{
    public function testConfigure()
    {
        $tokenCreate = new TokenCreate();
        $this->assertEquals('token:create', $tokenCreate->getName());
    }

    public function testExecute()
    {
        $note = 'some note';
        $username = 'user';
        $password = 'pass';
        $token = 'token123';

        $yaml = $this->getMockBuilder('Symfony\Component\Yaml\Yaml')
            ->disableOriginalConstructor()
            ->getMock();
        $yaml->staticExpects($this->once())
            ->method('dump')
            ->with(
                $this->equalTo(
                    array('authorization' => array('token' => $token))
                )
            );

        $github = $this->getMockBuilder('PlusPull\GitHub')
            ->disableOriginalConstructor()
            ->getMock();
        $github->expects($this->once())
            ->method('authenticate')
            ->with($this->equalTo($username), $this->equalTo($password));
        $github->expects($this->once())
            ->method('createToken')
            ->with($this->equalTo($note))
            ->will($this->returnValue($token));

        $tokenCreate = $this->getMockBuilder('PlusPull\Commands\TokenCreate')
            ->setMethods(array('getGitHub', 'getYaml'))
            ->getMock();
        $tokenCreate->expects($this->once())
            ->method('getGitHub')
            ->will($this->returnValue($github));
        $tokenCreate->expects($this->once())
            ->method('getYaml')
            ->will($this->returnValue($yaml));

        $tester = new CommandTester($tokenCreate);

        $dialog = $this->getMock(
            'Symfony\Component\Console\Helper\DialogHelper'
        );
        $dialog->expects($this->once())
            ->method('ask')
            ->will($this->returnValue($username));
        $dialog->expects($this->once())
            ->method('askHiddenResponse')
            ->will($this->returnValue($password));

        $tokenCreate->setHelperSet(new HelperSet());

        $tokenCreate->getHelperSet()->set($dialog, 'dialog');

        $tester->execute(array('--note' => $note));
    }
}
