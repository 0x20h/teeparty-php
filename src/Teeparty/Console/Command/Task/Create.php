<?php

namespace Teeparty\Console\Command\Task;

use Teeparty\Task;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;


class Create extends Command {
    
    private $id;
    private $container;

    protected function configure()
    {
        $this
            ->setName('teeparty:task:create')
            ->setDescription('Create a task')
            ->addArgument(
                'CHANNEL', 
                InputArgument::REQUIRED,
                'Channel to push the job to.'
            )
            ->addArgument(
                'JOB', 
                InputArgument::REQUIRED,
                'Classname of the job to invoke.'
            )
            ->addOption(
                'config',
                'c',
                InputOption::VALUE_REQUIRED,
                'Configuration file to use [teeparty.yml].',
                'teeparty.yml'
            )
            ->addOption(
                'context', 
                null, 
                InputOption::VALUE_OPTIONAL,
                'optional JSON encoded task context.', 
                ''
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        try {
            $this->id = $_SERVER['USER'];
            $channel = $input->getArgument('CHANNEL');
            $job = $input->getArgument('JOB');
            $file = $input->getOption('config');
            $context = json_decode($input->getOption('context'), true);
            
            if (!$context) {
                $context = array();
            }

            $this->container = new ContainerBuilder();
            $loader = new YamlFileLoader($this->container, new FileLocator(dirname($file)));
            $loader->load(basename($file));
            $this->container->setParameter('client.id', $this->id);
        } catch (\Exception $e) {
            $output->writeln('<error>'.$e->getMessage().'</error>');
            exit(1);
        }

        try {
            $log = $this->container->get('log');

            if (!class_exists($job)) {
                throw new \Teeparty\Exception('class ' . $job. ' not found');
            }

            $instance = new $job;

            if (!($instance instanceof \Teeparty\Job)) {
                throw new \Teeparty\Exception('class ' . $job.
                    ' is not a Teeparty\Job');
            }

            $task = new Task($instance, $context);
            $log->info('pushing ' . $job . ' to ' . $channel, $context);
            
            $client = $this->container->get('client');
            $id = $client->put($task, $channel);

            if (!$id) {
                $log->error('failed pushing task', (array) $task);
                exit(1);
            } else {
                $log->info('Pushed task: '.$task->getId());
            }
        } catch (\Exception $e) {
            $output->writeln('<error>'.$e->getMessage().'</error>');
            exit(1);
        }
    }
}
