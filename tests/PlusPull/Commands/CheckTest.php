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

    public function executeProvider()
    {
        return array(
            'all ok' => array(
                'input' => array('--pull' => true, '--limit' => 10),
                'checkComments' => true,
                'isMergeable' => true,
                'checkStatuses' => true,
                'mergeCount' => 2,
                'token' => null,
                'checkRepositoryLabelExists' => true,
                'addRepositoryLabelCount' => 0,
             ),
            'all ok + token' => array(
                'input' => array('--pull' => true, '--limit' => 10),
                'checkComments' => true,
                'isMergeable' => true,
                'checkStatuses' => true,
                'mergeCount' => 2,
                'token' => 'token123',
                'checkRepositoryLabelExists' => true,
                'addRepositoryLabelCount' => 0,
             ),
             'limit' => array(
                'input' => array('--pull' => true, '--limit' => 1),
                'checkComments' => true,
                'isMergeable' => true,
                'checkStatuses' => true,
                'mergeCount' => 1,
                'token' => null,
                'checkRepositoryLabelExists' => true,
                'addRepositoryLabelCount' => 0,
            ),
            '-1' => array(
                'input' => array('--pull' => true),
                'checkComments' => false,
                'isMergeable' => true,
                'checkStatuses' => true,
                'mergeCount' => 0,
                'token' => null,
                'checkRepositoryLabelExists' => true,
                'addRepositoryLabelCount' => 0,
            ),
            'unmergeable' => array(
                'input' => array('--pull' => true),
                'checkComments' => true,
                'isMergeable' => false,
                'checkStatuses' => true,
                'mergeCount' => 0,
                'token' => null,
                'checkRepositoryLabelExists' => true,
                'addRepositoryLabelCount' => 0,
            ),
            'fail' => array(
                'input' => array('--pull' => true),
                'checkComments' => true,
                'isMergeable' => true,
                'checkStatuses' => false,
                'mergeCount' => 0,
                'token' => null,
                'checkRepositoryLabelExists' => true,
                'addRepositoryLabelCount' => 0,
            ),
            'all ok labelling' => array(
                'input' => array('--pull' => true, '--limit' => 10),
                'checkComments' => true,
                'isMergeable' => true,
                'checkStatuses' => true,
                'mergeCount' => 2,
                'token' => null,
                'checkRepositoryLabelExists' => false,
                'addRepositoryLabelCount' => 1,
            ),
        );
    }

    /**
     * @dataProvider executeProvider
     *
     * @param boolean $checkComments
     * @param boolean $isMergeable
     * @param boolean $checkStatuses
     * @param integer $mergeCount
     * @param string $token
     * @param boolean $checkRepositoryLabelExists
     * @param integer $addRepositoryLabelCount
     */
    public function testExecute(
        $input,
        $checkComments,
        $isMergeable,
        $checkStatuses,
        $mergeCount,
        $token,
        $checkRepositoryLabelExists,
        $addRepositoryLabelCount
    ) {
        $required = 3;
        $whitelist = array('usera');
        $username = 'user';
        $password = 'pass';

        $pullRequest = $this->getMock('PlusPull\GitHub\PullRequest');
        $pullRequest->expects($this->atLeastOnce())
            ->method('collectCommentLabels');
        $pullRequest->expects($this->atLeastOnce())
            ->method('checkComments')
            ->with($this->equalTo($required), $this->equalTo($whitelist))
            ->will($this->returnValue($checkComments));
        $pullRequest->expects($this->atLeastOnce())
            ->method('isMergeable')
            ->will($this->returnValue($isMergeable));
        $pullRequest->expects($this->atLeastOnce())
            ->method('checkStatuses')
            ->will($this->returnValue($checkStatuses));

        $configFile = 'test-config.yml';

        $pullRequests = array(
            $pullRequest,
            $pullRequest,
        );

        $config = array(
            'authorization' => array(
                'username' => $username,
                'password' => $password,
                'token'    => $token,
            ),
            'repository' => array(
                'name' => 'test-repo',
                'username' => 'test-owner',
                'status' => true,
                'required' => $required,
                'whitelist' => $whitelist,
            ),
            'labels' => array(
                array(
                    'name' => 'blocked',
                    'color' => 'eb6420',
                    'hook' => '[B]',
                ),
            ),
        );

        $yaml = $this->getMockBuilder('Symfony\Component\Yaml\Yaml')
            ->disableOriginalConstructor()
            ->getMock();
        $yaml->staticExpects($this->any())
            ->method('parse')
            ->with($this->equalTo($configFile))
            ->will($this->returnValue($config));

        $github = $this->getMockBuilder('PlusPull\GitHub')
            ->disableOriginalConstructor()
            ->getMock();

        if ($token) {
            $github->expects($this->once())
                ->method('authenticateWithToken')
                ->with($this->equalTo($token));
        } else {
            $github->expects($this->once())
                ->method('authenticate')
                ->with($this->equalTo($username), $this->equalTo($password));
        }

        $github->expects($this->once())
            ->method('getPullRequests')
            ->will($this->returnValue($pullRequests));
        $github->expects($this->once())
            ->method('checkRepositoryLabelExists')
            ->will($this->returnValue($checkRepositoryLabelExists));
        $github->expects($this->exactly($addRepositoryLabelCount))->method('addRepositoryLabel');
        $github->expects($this->exactly($mergeCount))->method('merge');

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

        $input['config-file'] = $configFile;
        $tester->execute($input);
    }

    public function testExecuteMissingConfig()
    {
        $yaml = $this->getMockBuilder('Symfony\Component\Yaml\Yaml')
            ->disableOriginalConstructor()
            ->setMethods(array('parse'))
            ->getMock();

        $check = $this->getMockBuilder('PlusPull\Commands\Check')
            ->setMethods(array('getYaml'))
            ->getMock();
        $check->expects($this->once())
            ->method('getYaml')
            ->will($this->returnValue($yaml));

        $tester = new CommandTester($check);

        try {
            $tester->execute(array());
            $this->fail('Expected InvalidArgumentException');
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals(
                'Empty or missing config file',
                $e->getMessage()
            );
        }
    }
}
