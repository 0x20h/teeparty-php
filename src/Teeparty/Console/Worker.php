<?php

namespace Teeparty\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use Teeparty\Client\PHPRedis;


class Worker extends Command {

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
            $config = Yaml::parse(file_get_contents($file));
            $redis = new $config['queue']['class']($config['queue']['options']);
        } catch (\Exception $e) {
            $output->writeln('<error>'.$e->getMessage().'</error>');
            exit(1);
        }

        $this->loop($redis);
    }

    private function loop(Client $client)
    {
        $active = true;

        while($active) {
            
        }
    }
}
