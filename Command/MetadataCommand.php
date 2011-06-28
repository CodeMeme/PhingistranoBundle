<?php

namespace CodeMeme\PhingistranoBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class MetadataCommand extends ContainerAwareCommand
{
    protected $targets = array(
        'environments' => 'getEnvironments',
        'production'   => 'getProduction',
        'staging'      => 'getStaging',
        'testing'      => 'getTesting',
        'query'        => 'queryProperty',
    );

    protected function configure()
    {
        $prettyTargets = "\t<comment>" . join("</comment>,\n\t<comment>", array_keys($this->targets)) . "</comment>";
        
        $this->setName('build:metadata')
             ->setDescription('Output project specific metadata from the phing build.xml file.')
             ->setDefinition(array(
                new InputArgument('target', InputArgument::REQUIRED, 'The method to run from the metadata service'),
             ))
             ->addOption('property',          null, InputOption::VALUE_OPTIONAL, '(<comment>required for query</comment>) Property name to query for in project metadata.')
             ->addOption('e',        null, InputOption::VALUE_OPTIONAL, '(<comment>only used with query</comment>) specifies a specific environment to look for properties.')
             ->setHelp(<<<EOT
The <info>{$this->getName()}</info> command allows you to query information about the build configuration for this project.

  <info>./app/console {$this->getName()} target</info> 
 
If using the query command it is required to set a property argument.
The e argument is available for query but it is optional.
  
  <info>./app/console {$this->getName()} query --property=deploy.servers --e=staging</info> 

Targets:

{$prettyTargets}
EOT
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!array_key_exists($input->getArgument('target'), $this->targets)) {
            throw new \Exception("target argument is required");
        }
        if ($input->getArgument('target') == 'query') {
            if (!$propertyName = $input->getOption('property')) {
                throw new \Exception("--property is required for target: query");
            }
            $envNode = $input->getOption('e') ?: 'production';
            $data = $this->getContainer()->get('phingistrano.metadata')->{$this->targets[$input->getArgument('target')]}($propertyName, $envNode);
        } else {
            $data = $this->getContainer()->get('phingistrano.metadata')->{$this->targets[$input->getArgument('target')]}();
        }
        $this->recursiveDisplay($data, $output);
    }

    public function getContainer()
    {
        return parent::getContainer();
    }
    
    private function recursiveDisplay($data, $output, $level=0)
    {
        $indent = $level+1;
        if (!is_array($data)) $data = array($data);
        foreach ($data as $key => $val)
        {
            if (is_array($val)) {
                if (!is_numeric($key)) {
                    $this->displayKey($key, $output, $level);
                }
                $this->recursiveDisplay($val, $output, $indent);
            } else {
                $this->displayLine($key, $val, $output, $level);
            }
        }
    }
    
    private function displayLine($key, $val, $output, $level=0)
    {
        if (is_numeric($key)) {
             $this->displayVal($val, $output, $level);
        } else {
            $output->writeln(sprintf("%s<info>%s:</info> <comment>%s</comment>", 
                $this->indent($level), 
                $key,
                $val
            ));
        }
    }
    
    private function displayKey($key, $output, $level=0)
    {
        $output->writeln(sprintf("%s<info>%s:</info>", 
            $this->indent($level), 
            $key
        ));
    }
    
    private function displayVal($val, $output, $level=0)
    {
        $output->writeln(sprintf("%s<comment>%s</comment>", 
            $this->indent($level), 
            $val
        ));
    }
    
    private function indent($dent)
    {
        $space="";
        for ($i=0; $i<$dent; $i++) {
            $space .= "\t";
        }
        return $space;
    }

}