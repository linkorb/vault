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

class CatCommand extends Command
{
    public function configure()
    {
        $this->setName('cat')
            ->setDescription('Outputs decrypted file to stdout')
            ->addArgument(
                'filename',
                InputArgument::REQUIRED,
                'Filename to cat'
            )
        ;
    }
    
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $vault = $this->getApplication()->getVault();
        $filename = $input->getArgument('filename');
        $encFilename = $vault->getStoragePath() . '/' . $vault->encryptFilename($filename);
        if (!file_exists($encFilename)) {
            throw new RuntimeException("File not found in vault: " . $filename);
        }
        $data = file_get_contents($encFilename);
        $data = $vault->decrypt($data);
        echo $data;
    }
}
