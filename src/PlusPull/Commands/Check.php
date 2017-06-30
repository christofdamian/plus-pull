<?php
namespace PlusPull\Commands;

use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;

use PlusPull\GitHub\Label;

class Check extends AbstractCommand
{

    protected function configure()
    {
        $this->setName('check');
        $this->setDescription('Check pull requests');
        $this->addArgument(
            'config-file',
            InputArgument::OPTIONAL,
            'Path of the yaml configuration file',
            'config.yml'
        );
        $this->addOption(
            'pull',
            'p',
            InputOption::VALUE_NONE,
            'Pull the request if all conditions are met'
        );
        $this->addOption(
            'limit',
            'l',
            InputOption::VALUE_REQUIRED,
            'Maximum numbers of pull',
            1
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $this->getYaml()->parse($input->getArgument('config-file'));

        if (!is_array($config) || empty($config)) {
            throw new \InvalidArgumentException('Empty or missing config file');
        }

        $github = $this->getGitHub();

        if (!empty($config['authorization']['token'])) {
            $github->authenticateWithToken($config['authorization']['token']);
        } else {
            $github->authenticate(
                $config['authorization']['username'],
                $config['authorization']['password']
            );
        }

        $maxPulls = $input->getOption('limit');

        if (!empty($config['repository'])) {
            $repositories = array($config['repository']);
        } else {
            $repositories = $config['repositories'];
        }

        foreach ($repositories as $repositoryConfig) {

            $username = $repositoryConfig['username'];
            $repository = $repositoryConfig['name'];
            $checkStatus = !empty($repositoryConfig['status']);

            $output->writeln("repository: $username/$repository");

            $plusRequired = 3;
            if (isset($repositoryConfig['required'])) {
                $plusRequired = $repositoryConfig['required'];
            }

            $whitelist = null;
            if (!empty($repositoryConfig['whitelist'])) {
                $whitelist = $repositoryConfig['whitelist'];
            }

            $mergeMethod = 'merge';
            if (!empty($repositoryConfig['mergemethod'])) {
                $mergeMethod= $repositoryConfig['mergemethod'];
            }

            $github->setRepository($username, $repository);

            $labels = array();
            if (!empty($repositoryConfig['labels'])) {
                $labels = $repositoryConfig['labels'];
                foreach ($labels as $key => $labelConfig) {
                    $labelConfig['label'] = new Label(
                        $labelConfig['name'],
                        $labelConfig['color']
                    );
                    if (!$github
                        ->checkRepositoryLabelExists($labelConfig['label'])) {
                        $github->addRepositoryLabel($labelConfig['label']);
                    }
                    $labels[$key] = $labelConfig;
                }
            }

            $pullRequests = array_reverse($github->getPullRequests());

            foreach ($pullRequests as $pullRequest) {
                $pull = $input->getOption('pull');

                $output->write(
                    $pullRequest->number.' ('.$pullRequest->title.')'
                );

                $pullRequest->collectCommentLabels($labels);

                if ($pullRequest->checkComments($plusRequired, $whitelist)) {
                    $output->write(' +1');
                } else {
                    $output->write(' -1');
                    $pull = false;
                }

                if ($checkStatus) {
                    if ($pullRequest->checkStatuses()) {
                        $output->write(' success');
                    } else {
                        $output->write(' fail');
                        $pull = false;
                    }
                }

                if ($pullRequest->isMergeable()) {
                    $output->write(' mergeable');
                } else {
                    $output->write(' conflicts');
                    $pull = false;
                }

                $github->updateLabels($pullRequest, $labels);

                if ($pull) {
                    $github->merge(
                        $pullRequest->number,
                        $pullRequest->sha,
                        $mergeMethod
                    );
                    $output->write(' pulled');
                    $maxPulls--;
                }

                $output->writeln('');

                if ($maxPulls<=0) {
                    break;
                }
            }
        }
    }
}
