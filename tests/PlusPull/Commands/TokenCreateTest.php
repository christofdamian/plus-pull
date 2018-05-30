<?php

namespace tests\PlusPull\Commands;

use PHPUnit\Framework\TestCase;
use PlusPull\Commands\TokenCreate;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Tester\CommandTester;

class TokenCreateTest extends TestCase
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

        $github = $this->getMockBuilder('PlusPull\GitHub')
            ->disableOriginalConstructor()
            ->createMock();
        $github->expects($this->once())
            ->method('authenticate')
            ->with($this->equalTo($username), $this->equalTo($password));
        $github->expects($this->once())
            ->method('createToken')
            ->with($this->equalTo($note))
            ->will($this->returnValue($token));

        $tokenCreate = $this->getMockBuilder('PlusPull\Commands\TokenCreate')
            ->setMethods(array('getGitHub', 'dumpYaml'))
            ->createMock();
        $tokenCreate->expects($this->once())
            ->method('getGitHub')
            ->will($this->returnValue($github));
        $tokenCreate->expects($this->once())
            ->method('dumpYaml')
            ->with(
                $this->equalTo(
                    array('authorization' => array('token' => $token))
                )
            );

        $tester = new CommandTester($tokenCreate);

        $helper = $this->createMock(
            'Symfony\Component\Console\Helper\QuestionHelper'
        );
        $helper->expects($this->exactly(2))
            ->method('ask')
            ->will($this->onConsecutiveCalls($username, $password));

        $tokenCreate->setHelperSet(new HelperSet());

        $tokenCreate->getHelperSet()->set($helper, 'question');

        $tester->execute(array('--note' => $note));
    }
}
