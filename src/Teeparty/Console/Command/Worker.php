<?php
declare(ticks=1);
namespace Teeparty\Console\Command;

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
    private $exceptionBackoff = 0;

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
                'CHANNEL',
                InputArgument::REQUIRED,
                'Channel to fetch tasks from.'
            )
            ->addOption(
                'loops', 
                'l', 
                InputOption::VALUE_REQUIRED, 
                'How many loops to perform (e.g. only run N tasks)', 
                0
            )->addOption(
                'config',
                'c',
                InputOption::VALUE_REQUIRED,
                'Configuration file to use [teeparty.yml]',
                'teeparty.yml'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        pcntl_signal(SIGTERM, array($this, 'handleSignal'));
        pcntl_signal(SIGINT, array($this, 'handleSignal'));

        try {
            $this->id = $input->getArgument('WORKER_ID');
            $channels = $input->getArgument('CHANNELS');
            $loops = $input->getOption('loops');
            $file = $input->getOption('config');
            $this->container = new ContainerBuilder();
            $loader = new YamlFileLoader($this->container, new FileLocator(dirname($file)));
            $loader->load(basename($file));
            $this->container->setParameter('worker.id', $this->id);
            $log = $this->container->get('log');

            if (!$log) {
                throw new Exception('unable to setup logger');
            }

            try {
                $client = $this->container->get('client');
            } catch (\Exception $e) {
                $log->emergency($e->getMessage(), $e->getTrace());
                throw $e;
            }

            $this->loop($channels, $loops);
        } catch (\Exception $e) {
            $output->writeln('<error>'.$e->getMessage().'</error>');
            exit(2);
        }
    }


    private function loop(array $channels, $loops = 0)
    {
        $i = 0;
        $timeout = $this->container->getParameter('client.get.timeout');
        $log = $this->container->get('log');
        $client = $this->container->get('client');
        $prefix = $this->container->getParameter('redis.prefix');
        $log->debug('global namespace used for redis keys: ' . $prefix);
        $log->info('Listening on channels: ' . implode(',', $channels));

        while((!$loops || $i++ < $loops) && $this->active) {
            $task = null;

            try {
                $task = $client->get($channels, $timeout);
                $this->exceptionBackoff = 0;
            } catch (\Exception $e) {
                $log->error($e->getMessage(), $e->getTrace());

                if ($this->exceptionBackoff > 10) {
                    $log->emergency('exiting due to 10 consecutive exceptions');
                    exit(1);
                }
                
                usleep(min(pow(2, $this->exceptionBackoff++ + 15), 3 * 1E6));
            }

            if (empty($task)) {
                continue;
            }

            $log->info('executing task ' . $task->getId(), $task->getContext());
            $result = $task->execute();
            $log->debug('[' . strtoupper($result->getStatus()) . '] finished ' .
                'task ' . $task->getId() . ' in ' .
                $result->getExecutionTime(), (array) $result->getResult());
            // report task results
            $client->ack($result);
        }
    }


    public function setActive($bool)
    {
        $this->active = (bool) $bool;
    }


    public function handleSignal($signal)
    {
        $log = $this->container->get('log');
        $log->debug('received signal ' . $signal);

        switch($signal) {
            case SIGINT:
            case SIGTERM:
                $log->info('shutting down...');
                $this->setActive(false);
                break;
            default:
                $log->info('unhandled signal: ' . $signal);
        }
    }
}
