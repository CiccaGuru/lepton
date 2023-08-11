<?php

// Set the directory to monitor

namespace Lepton;

use ScssPhp\ScssPhp\{Compiler, OutputStyle};

class LeptonServer
{
    private array $fileHashes = array();
    private array $dependancyTree = array();
    private bool $compile_sass = false;
    private $webServerProcess;

    public function __construct(public readonly string $sass_directory, public readonly string $css_directory, private readonly string $application)
    {

        echo "Running...".PHP_EOL;

        if(is_dir($this->sass_directory)) {
            // Clean output folder
            array_map('unlink', array_filter((array) glob($this->css_directory."*")));

            $this->compile_sass = true;
            echo "\e[32mCompiling assets...\e[39m".PHP_EOL;

            $this->updateMainFiles();

            // Compile the files
            foreach($this->fileHashes as $file => $hash) {

                $file = realpath($file);
                $fileDependancies = $this->compileFile($file);
                $this->fileHashes[$file] = filemtime($file);
                $this->dependancyTree[$file] = $fileDependancies;
                foreach($fileDependancies as $dep) {
                    $this->fileHashes[realpath($dep)] = filemtime($dep);
                }
            }


            echo "Dependancies tree built.".PHP_EOL.PHP_EOL;

            // Compute md5 hash for files
            foreach($this->dependancyTree as $file => $where) {
                $fileHashes[$file] = filemtime($file);
            }

        } else {
            echo "\e[32mNo _sass directory found\e[39m".PHP_EOL;
        }
    }


    public function run()
    {


        declare(ticks=1); // Enable tick processing for signal handling

        pcntl_signal(SIGINT, array($this, "terminate"));

        echo "\e[32mWatching in ".$this->sass_directory."\e[39m".PHP_EOL.PHP_EOL;

        echo "\e[33mStarting PHP WebServer (ONLY FOR DEVELOPMENT) at http://127.0.0.1:5555\e[39m".PHP_EOL.PHP_EOL;

        //Start the PHP built-in web server
        $command = sprintf('php -S localhost:5555 -t %s %s/webserver.php', $this->application, $this->application);
        $this->webServerProcess = proc_open($command, [STDIN, STDOUT, STDERR], $pipes);
        while(true) {
            if($this->compile_sass) {
                $this->cleanDeletedFiles();
                $this->updateMainFiles();
                foreach($this->fileHashes as $file => $hash) {
                    $file = realpath($file);
                    if(file_exists($file)) {
                        $newHash = filemtime($file);
                        if($newHash != $hash) {
                            echo "\e[93mFile $file modified!\e[39m".PHP_EOL;
                            $this->fileHashes[$file] = $newHash;

                            // if it's a main file
                            if(key_exists($file, $this->dependancyTree)) {
                                $this->dependancyTree[$file] = $this->compileFile($file);
                            } else { // if it's an included file
                                foreach($this->dependancyTree as $main => $dependancies) {
                                    if(in_array($file, $dependancies)) {
                                        $this->dependancyTree[$main] = $this->compileFile($main);
                                    }
                                }
                            }

                        }
                    } else {
                        unset($this->fileHashes[$file]);
                    }
                }
            }
            sleep(1);
        }

    }

    public function terminate()
    {
        echo "\n\n\e[31mTERMINATING...\e[39m\n";
        proc_terminate($this->webServerProcess);
        exit(130);
    }


    public function cleanDeletedFiles()
    {

        foreach($this->fileHashes as $file => $hash) {
            if(!file_exists($file)) {
                unset($fileHashes[$file]);
                foreach($this->dependancyTree as $main => $dependancies) {
                    if($main == $file) {
                        unset($this->dependancyTree[$main]);
                    }
                    if(in_array($file, $dependancies)) {
                        $key = array_search($file, $dependancies);
                        unset($this->dependancyTree[$main][$key]);
                    }
                }
            }
        }
    }


    public function compileScss($inputFile)
    {

        $compiler = new Compiler();
        $compiler->setImportPaths($this->sass_directory);
        $compiler->setOutputStyle(OutputStyle::COMPRESSED);

        if (is_file($inputFile)) {
            // Set the path to the output CSS file
            $outputFile = preg_replace('/(.*)\.(sass|scss)$/', '$1.css', basename($inputFile));
            $outputFile =  $this->css_directory.$outputFile;
            // Compile the SCSS code into CSS
            try {
                $result = $compiler->compileString(file_get_contents($inputFile));

                $outputdir = dirname($outputFile);

                // Create the directories recursively
                if (!is_dir($outputdir)) {
                    mkdir($outputdir, 0755, true);
                }
                file_put_contents($outputFile, $result->getCss());
                return $result->getIncludedFiles();
            } catch (\Exception $e) {
                print_r($e);
                syslog(LOG_ERR, 'scssphp: Unable to compile content');
            }

        } else {
            echo "Invalid file".PHP_EOL;
        }
    }

    public function compileFile($file)
    {
        $fileNameArray = explode("/", $file);
        $fileName = end($fileNameArray);
        echo "\e[39m\t==> Compiling $fileName... \e[39m";
        $included = $this->compileScss($file);
        echo "\e[32mDone!\e[39m".PHP_EOL;
        return $included;
    }

    public function updateMainFiles()
    {
        $files = glob($this->sass_directory . '/*');  // Get all files in the directory


        foreach ($files as $file) {
            $file = realpath($file);
            $pattern = '/^\/.+\/[^_]*[.](scss|sass)/';     // Specify the regex pattern for file names
            if(preg_match($pattern, $file)) {
                if(! array_key_exists($file, $this->fileHashes)) {
                    $this->fileHashes[$file] = 0;
                }
            }
        }

    }

}
