<?php

namespace Teeparty\Console\Command\Task;

use Teeparty\Task\Factory;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;


class Result extends Command {
    
    private $id;
    private $container;

    protected function configure()
    {
        $this
            ->setName('teeparty:task:result')
            ->setDescription('Output task results.')
            ->addArgument(
                'TASK_ID', 
                InputArgument::REQUIRED,
                'The task id'
            )
            ->addOption(
                'config',
                'c',
                InputOption::VALUE_REQUIRED,
                'Configuration file to use [teeparty.yml].',
                'teeparty.yml'
            )
            ->addOption(
                'format',
                'f',
                InputOption::VALUE_REQUIRED,
                'Output format [json|txt]',
                'txt'
            );

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        try {
            $this->id = $_SERVER['USER'];
            $taskId = $input->getArgument('TASK_ID');
            $file = $input->getOption('config');
            $format = $input->getOption('format');
            
            $this->container = new ContainerBuilder();
            $loader = new YamlFileLoader($this->container, new FileLocator(dirname($file)));
            $loader->load(basename($file));
            $this->container->setParameter('client.id', $this->id);

            $log = $this->container->get('log');
            $client = $this->container->get('client');

            $results = $client->result($taskId);
            
            if (!$results) {
                $log->error('No results found for ' . $taskId);
                exit(1);
            }
            
            switch ($format) {
            case 'json':
                $out = array();

                foreach ($results as $result) {
                    $out[] = $result->jsonSerialize();
                }

                $output->writeln(json_encode($out));
                break;
            default:
                $output->writeln('Results:');

                foreach($results as $i => $result) {
                    $output->writeln(str_repeat('-', 80));
                    $output->writeln('<info>#'.$i.'</info>');
                    $output->writeln('<comment>Status:         </comment>' . 
                        $result->getStatus());
                    $output->writeln('<comment>Started at:     </comment>' .
                        $result->getStartDate()->format('Y-m-d H:i:s'));
                    $output->writeln('<comment>Execution time: </comment>' .
                        $result->getExecutionTime() . ' secs.');
                    $output->writeln('<comment>returnValue:    </comment>' . 
                       var_export($result->getResult(), true));
                }
            }
        } catch (\Exception $e) {
            $output->writeln('<error>'.$e->getMessage().'</error>');
            $output->writeln('<error>'.$e->getTraceAsString().'</error>');
            exit(1);
        }
    }
}
