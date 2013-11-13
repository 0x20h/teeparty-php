<?php

namespace Teeparty\Console;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Teeparty\Client\PHPRedis;


class Worker extends Command {

    private $container;

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
            $file = $input->getArgument('CONFIG_FILE');
            $this->container = new ContainerBuilder();
            $loader = new YamlFileLoader($this->container, new FileLocator(dirname($file)));
            $loader->load(basename($file));
        } catch (\Exception $e) {
            $output->writeln('<error>'.$e->getMessage().'</error>');
            exit(1);
        }

        $this->loop();
    }

    private function loop()
    {
        $queue = $this->container->get('queue');
        $channels = ['foo', 'bar'];

        while($item = $queue->pop($channels)) {
            var_dump($item);
        }
    }
}
