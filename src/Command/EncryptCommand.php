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

class EncryptCommand extends Command
{
    public function configure()
    {
        $this->setName('encrypt')
            ->setDescription('Encrypt the workpath into the storage path')
        ;
    }
    
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $vault = $this->getApplication()->getVault();
        $output->writeLn('Encrypting `' . $vault->getWorkPath() . '` into `' . $vault->getStoragePath() . '`');
        
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($vault->getWorkPath()),
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($files as $name => $file) {
            if (is_file($name)) {
                $name = substr($name, strlen($vault->getWorkPath())+1);
                $vault->encryptFile($name);
            }
        }
    }
}
