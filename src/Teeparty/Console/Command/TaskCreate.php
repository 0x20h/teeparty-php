<?php

namespace Teeparty\Console\Command;

use Teeparty\Task;
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
                'WORKER_CLASS', 
                InputArgument::REQUIRED,
                'Classname of the worker to invoke'
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
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'optional task context', 
                array()
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        try {
            $this->id = $_SERVER['USER'];
            $worker = $input->getArgument('WORKER_CLASS');
            $file = $input->getArgument('CONFIG_FILE');
            $context = $input->getOption('context');
            $this->container = new ContainerBuilder();
            $loader = new YamlFileLoader($this->container, new FileLocator(dirname($file)));
            $loader->load(basename($file));
            $this->container->setParameter('worker.id', $this->id);


            $queue = $this->container->get('queue');
            $log = $this->container->get('log');

            $task = new Task(new $worker, new Context($context));
            $queue->push($task, $channel);

        } catch (\Exception $e) {
            $output->writeln('<error>'.$e->getMessage().'</error>');
            exit(1);
        }
    }
}
