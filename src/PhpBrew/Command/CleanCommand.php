<?php

namespace PhpBrew\Command;

use CLIFramework\Command;
use PhpBrew\Build;
use PhpBrew\BuildFinder;
use PhpBrew\Config;
use PhpBrew\Tasks\MakeTask;
use PhpBrew\Utils;

class CleanCommand extends Command
{
    public function brief()
    {
        return 'Clean up the source directory of a PHP distribution';
    }

    public function usage()
    {
        return 'phpbrew clean [-a|--all] [php-version]';
    }

    public function options($opts)
    {
        $opts->add('a|all', 'Remove all the files in the source directory of the PHP distribution.');
    }

    public function arguments($args)
    {
        $args->add('PHP build')
            ->validValues(function () {
                return BuildFinder::findInstalledBuilds();
            })
            ;
    }

    public function execute($version)
    {
        $buildDir = Config::getBuildDir() . DIRECTORY_SEPARATOR . $version;
        if ($this->options->all) {
            if (!file_exists($buildDir)) {
                $this->logger->info('Source directory ' . $buildDir . ' does not exist.');
            } else {
                $this->logger->info('Source directory ' . $buildDir . ' found, deleting...');
                Utils::recursive_unlink($buildDir, $this->logger);
            }
        } else {
            $make = new MakeTask($this->logger);
            $make->setQuiet();
            $build = new Build($version);
            $build->setSourceDirectory($buildDir);
            if ($make->clean($build)) {
                $this->logger->info('Distribution is cleaned up. Woof! ');
            }
        }
    }
}
