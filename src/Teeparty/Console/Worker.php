<?php

namespace Teeparty\Console;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;


class Worker extends Command {
    
    private $id;
    private $container;
    private $active = true;

    protected function configure()
    {
        $this
            ->setName('worker')
            ->setDescription('Start worker')
            ->addArgument(
                'CONFIG_FILE',
                InputArgument::REQUIRED,
                'configuration file'
            )
            ->addArgument(
                'WORKER_ID', 
                InputArgument::REQUIRED,
                'Worker ID'
            )
            ->addOption(
                'loops', 
                'l', 
                InputOption::VALUE_OPTIONAL, 
                'How many loops to perform (e.g. only run N tasks)', 
                0
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        try {
            $this->id = $input->getArgument('WORKER_ID');
            $file = $input->getArgument('CONFIG_FILE');
            $this->container = new ContainerBuilder();
            $loader = new YamlFileLoader($this->container, new FileLocator(dirname($file)));
            $loader->load(basename($file));
            $this->container->setParameter('worker.id', $this->id);
        } catch (\Exception $e) {
            $output->writeln('<error>'.$e->getMessage().'</error>');
            exit(1);
        }

        $this->loop();
    }

    private function loop()
    {
        $queue = $this->container->get('queue');
        $channels = $this->container->getParameter('channels');
        $timeout = $this->container->getParameter('queue.pop.timeout');
        $log = $this->container->get('log');

        $log->debug('Listening on channels: ' . implode(',', $channels));

        while($this->active) {
            $task = $queue->pop($channels, $timeout);

            if (empty($task)) {
                $log->debug('timeout, idling...');
                continue;
            }

            $log->debug('Task: ', $task);

            try {
                $queue->ack($task, $task->run($context));
            } catch (\Exception $e) {
                $log->error($e);
                exit(1);
            }

        }
    }


    public function setActive($bool)
    {
        $this->active = (bool) $bool;
    }
}
