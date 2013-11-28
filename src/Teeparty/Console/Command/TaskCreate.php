<?php

namespace Teeparty\Console\Command;

use Teeparty\Task\Factory;
use Teeparty\Task\Result;
use Teeparty\Task\Context;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;


class TaskCreate extends Command {
    
    private $id;
    private $container;

    protected function configure()
    {
        $this
            ->setName('teeparty:task:create')
            ->setDescription('Create a task [and fetch results].')
            ->addArgument(
                'CONFIG_FILE',
                InputArgument::REQUIRED,
                'configuration file'
            )
            ->addArgument(
                'JOB', 
                InputArgument::REQUIRED,
                'Classname of the job to invoke'
            )
            ->addOption(
                'channel', 
                'c',
                InputOption::VALUE_REQUIRED,
                'push task to channel'
            )
            ->addOption(
                'context', 
                null, 
                InputOption::VALUE_OPTIONAL,
                'optional JSON encoded task context', 
                ''
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        try {
            $this->id = $_SERVER['USER'];
            $job = $input->getArgument('JOB');
            $file = $input->getArgument('CONFIG_FILE');
            $channel = $input->getOption('channel');
            $context = json_decode($input->getOption('context'), true);
            
            if (!$context) {
                $context = array();
            }

            $this->container = new ContainerBuilder();
            $loader = new YamlFileLoader($this->container, new FileLocator(dirname($file)));
            $loader->load(basename($file));
            $this->container->setParameter('worker.id', $this->id);


            $log = $this->container->get('log');

            $task = Factory::create($job, $context);
            $log->info('pushing ' . $job . ' to ' . $channel, $context);
            
            $queue = $this->container->get('queue');
            $id = $queue->push($task, $channel);

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
