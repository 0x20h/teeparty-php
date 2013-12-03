<?php
declare(ticks=1);
namespace Teeparty\Console\Command;

use Teeparty\Task\Result;
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
            ->setName('teeparty:worker')
            ->setDescription('Start worker')
            ->addArgument(
                'WORKER_ID', 
                InputArgument::REQUIRED,
                'Worker ID'
            )
            ->addArgument(
                'CHANNELS',
                InputArgument::REQUIRED | InputArgument::IS_ARRAY,
                'configuration file'
            )
            ->addOption(
                'loops', 
                'l', 
                InputOption::VALUE_OPTIONAL, 
                'How many loops to perform (e.g. only run N tasks)', 
                0
            )->addOption(
                'config',
                'c',
                InputOption::VALUE_REQUIRED,
                'Configuration file to use [~/.teeparty.yml',
                '~/.teeparty.yml'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        pcntl_signal(SIGTERM, array($this, 'handleSignal'));

        try {
            $this->id = $input->getArgument('WORKER_ID');
            $channels = $input->getArgument('CHANNELS');
            $loops = $input->getOption('loops');
            $file = $input->getOption('config');
            $this->container = new ContainerBuilder();
            $loader = new YamlFileLoader($this->container, new FileLocator(dirname($file)));
            $loader->load(basename($file));
            $this->container->setParameter('worker.id', $this->id);
        } catch (\Exception $e) {
            $output->writeln('<error>'.$e->getMessage().'</error>');
            exit(1);
        }
        
        $this->loop($channels, $loops);
    }

    private function loop(array $channels, $loops = 0)
    {
        $i = 0;
        $queue = $this->container->get('queue');
        $timeout = $this->container->getParameter('queue.pop.timeout');
        $log = $this->container->get('log');

        $prefix = $this->container->getParameter('redis.prefix');
        $log->debug('global namespace used for redis keys: ' . $prefix);
        $log->info('Listening on channels: ' . implode(',', $channels));

        while((!$loops || $i++ < $loops) && $this->active) {
            try {
                $task = $queue->pop($channels, $timeout);
            } catch (\Exception $e) {
                $log->error($e->getMessage(), $e->getTrace());
            }

            if (empty($task)) {
                continue;
            }

            $log->debug('starting task ' . $task->getName(), $task->getContext());
            $result = $task->execute();
            $log->info('finished task ' . $task->getName() . ' in ' . 
                $result->getExecutionTime(), $result->getResult());
            // report task results
            $queue->ack($result);
        }

        $log->info('BYE BYE');
    }

    public function setActive($bool)
    {
        $this->active = (bool) $bool;
    }

    public function handleSignal($signal)
    {
        $log = $this->container->get('log');
        $log->info('got signal '.$signal);
        
        switch($signal) {
        case 15:
            $log->info('setting worker inactive...');
            $this->setActive(false);
        }
    }
}
