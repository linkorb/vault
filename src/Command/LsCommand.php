<?php

namespace Vault\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use RuntimeException;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use splitbrain\PHPArchive\Archive;
use splitbrain\PHPArchive\Tar;

class LsCommand extends Command
{
    public function configure()
    {
        $this->setName('ls')
            ->setDescription('List files in the vault')
        ;
    }
    
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $vault = $this->getApplication()->getVault();
        $output->writeLn('Encrypting `' . $vault->getWorkPath() . '` into `' . $vault->getStoragePath() . '`');
        
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($vault->getStoragePath()),
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($files as $name => $file) {
            if (is_file($name)) {
                $name = substr($name, strlen($vault->getWorkPath()));
                $filename = $vault->decryptFilename($name);
                $output->writeLn(' * <info>' . $filename . '</info> [' . $name . ']');
            }
        }
    }
}
