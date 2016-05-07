<?php

namespace Vault;

use Symfony\Component\Console\Output\OutputInterface;
use RuntimeException;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class Vault
{
    protected $output;
    protected $password;
    protected $method = 'aes-256-cbc';
    protected $key;
    protected $iv;
    
    public function __construct($password, OutputInterface $output)
    {
        $this->output = $output;
        $this->password = $password;
        $hash = hash('sha256', $this->password);
        $key = substr($hash, 0, 32);
        $iv = substr($hash, -32);
        
        /*
        echo "HASH: $hash\n";
        echo "KEY: $key\n";
        echo "IV: $iv\n";
        */
        $this->key = $this->hextostr($key);
        $this->iv = $this->hextostr($iv);
    }
    
    protected $storagePath;
    
    public function getStoragePath()
    {
        return $this->storagePath;
    }
    
    public function setStoragePath($storagePath)
    {
        $this->storagePath = $storagePath;
        return $this;
    }
    
    protected $workPath;
    
    public function getWorkPath()
    {
        return $this->workPath;
    }
    
    public function setWorkPath($workPath)
    {
        $this->workPath = $workPath;
        return $this;
    }
    
    public function encryptFilename($filename)
    {
        return trim(base64_encode($this->encrypt($filename)), '=');
    }
    
    public function decryptFilename($filename)
    {
        return $this->decrypt(base64_decode($filename));
    }

    public function encrypt($data)
    {
        $data = openssl_encrypt($data, $this->method, $this->key, OPENSSL_RAW_DATA, $this->iv);
        return $data;
    }
    
    public function decrypt($data)
    {
        $data = openssl_decrypt($data, $this->method, $this->key, OPENSSL_RAW_DATA, $this->iv);
        return $data;
    }
    
    public function encryptFile($filename)
    {
        $this->output->writeLn("* Encrypting: <info>$filename</info>");
        $data = file_get_contents($this->getWorkPath() . '/' . $filename);
        
        $filename = $this->encryptFilename($filename);
        $data = $this->encrypt($data);
        file_put_contents($this->getStoragePath() . '/' . $filename, $data);
    }
    
    public function decryptFile($filename)
    {
        $this->output->writeLn("* Decrypting: <info>$filename</info>");
        $data = file_get_contents($this->getStoragePath() . '/' . $filename);
        
        $filename = $this->decryptFilename($filename);
        $data = $this->decrypt($data);
        file_put_contents($this->getWorkPath() . '/' . $filename, $data);
    }
    
    private function strtohex($x)
    {
        $s='';
        foreach (str_split($x) as $c) {
            $s.=sprintf("%02X", ord($c));
        }
        return($s);
    }
    
    private function hextostr($hex)
    {
        $string='';
        for ($i=0; $i < strlen($hex)-1; $i+=2) {
            $string .= chr(hexdec($hex[$i].$hex[$i+1]));
        }
        return $string;
    }
    
    public function verify()
    {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->getStoragePath()),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($files as $name => $file) {
            if (is_file($name)) {
                $name = substr($name, strlen($this->getWorkPath()));
                $filename = $this->decryptFilename($name);
                if (!$filename) {
                    throw new RuntimeException("Incorrect password. Can't decrypt filename: " . $name);
                }
            }
        }
    }
}
