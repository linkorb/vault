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

class DecryptCommand extends Command
{
    public function configure()
    {
        $this->setName('decrypt')
            ->setDescription('Decrypt the storage path into the work path')
        ;
    }
    
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $vault = $this->getApplication()->getVault();
        $output->writeLn('Decrypting `' . $vault->getStoragePath() . '` into `' . $vault->getWorkPath() . '`');
        
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($vault->getStoragePath()),
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($files as $name => $file) {
            if (is_file($name)) {
                $name = substr($name, strlen($vault->getStoragePath())+1);
                $vault->decryptFile($name);
            }
        }
    }
}
