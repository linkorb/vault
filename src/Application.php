<?php

namespace Vault;

use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Question\Question;
use Vault\Vault;
use RuntimeException;

class Application extends ConsoleApplication
{
    protected $vaultConfig;
    protected $vault;
    
    public function __construct($autoLoader)
    {
        $this->autoLoader = $autoLoader;
        parent::__construct();

        $this->setName('Vault');
        $this->setVersion('1.0.0');
        
        // extract --vault-config argument, before interpreting other arguments
        foreach ($_SERVER['argv'] as $i => $argument) {
            if (substr($argument, 0, 15)=='--vault-config=') {
                $this->vaultConfig = substr($argument, 15);
                unset($_SERVER['argv'][$i]);
            }
        }

    }
    
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $password = getenv('VAULT_PASSWORD');
        $tries = 0;
        while (!$password) {
             $question = new Question('Vault password: ', null);
             $question->setHidden(true);
             $question->setHiddenFallback(false);
             $helperSet = $this->getHelperSet();

             $helper = $helperSet->get('question');
             $password = $helper->ask($input, $output, $question);
        }
        $this->vault = new Vault($password, $output);
        // Defaults
        $this->vault->setStoragePath(getcwd() . '/vault');
        $this->vault->setWorkPath(getcwd() . '/secure');
        $this->vault->verify();

        // Load droid.yml
        
        $filename = $this->getVaultFilename();
        
        if ($filename && file_exists($filename)) {
            $loader = new JsonLoader();
            $loader->load($vault, $filename);
        } else {
        }

        return parent::doRun($input, $output);
    }
    
    public function getVault()
    {
        return $this->vault;
    }
    
    private function getVaultFilename()
    {
        if ($this->vaultConfig) {
            $filename = $this->vaultConfig;
        } else {
            // no parameters, assume 'vault.json' in current working directory
            $filename = getcwd() . '/vault.json';
        }
        return $filename;
    }
}
