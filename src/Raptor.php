<?php

/**
 * Copyright (C) 2015  Alexander Schmidt
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * @author     Alexander Schmidt <mail@story75.com>
 * @copyright  Copyright (c) 2015, Alexander Schmidt
 * @date       2015-06-04
 */

namespace Bonefish\Raptor;

use Bonefish\Autoloader\Autoloader;
use Bonefish\CLI\CLIInterface;
use Bonefish\Injection\ContainerInterface;
use Bonefish\Traits\DirectoryCreator;
use Bonefish\Utility\Configuration\ConfigurationManagerInterface;
use Bonefish\Utility\Environment;
use Nette\Reflection\AnnotationsParser;
use Symfony\Component\Console\Application;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class Raptor extends Application implements CLIInterface
{

    use DirectoryCreator;

    /**
     * @var Environment
     * @Bonefish\Inject
     */
    public $environment;

    /**
     * @var ConfigurationManagerInterface
     * @Bonefish\Inject
     */
    public $configurationManager;

    /**
     * @var ContainerInterface
     * @Bonefish\Inject
     */
    public $container;

    /**
     * @var Finder
     * @Bonefish\Inject
     */
    public $finder;

    /**
     * @var CommandProxyGenerator
     * @Bonefish\Inject
     */
    public $commandProxyGenerator;

    const RAPTOR_CACHE_PATH = '/Raptor/';
    const COMMAND_CACHE_FILE = 'CommandCache.neon';

    public function getRaptorCachePath()
    {
        return $this->environment->getFullCachePath() . self::RAPTOR_CACHE_PATH;
    }

    public function __construct()
    {
        parent::__construct('Bonefish Raptor', 'v2');
    }

    public function __init()
    {
        $commands = $this->getBonefishCommands();

        foreach ($commands as $command) {
            $this->add($command);
        }
    }

    protected function getBonefishCommands()
    {
        $commands = [];
        $cachePath = $this->getRaptorCachePath();
        $cacheFile = $cachePath . self::COMMAND_CACHE_FILE;

        try {
            $cache = $this->configurationManager->getConfiguration($cacheFile);
        } catch (\InvalidArgumentException $e) {
            $cache = $this->generateCommandCache();
        }

        $autoloader = new Autoloader();
        $autoloader->addNamespace('Bonefish\Raptor\Proxy\\', $cachePath);
        $autoloader->register();

        foreach ($cache['commands'] as $command) {
            $commands[] = $this->container->get($command);
        }

        return $commands;
    }

    protected function generateCommandCache()
    {
        $commands = [];

        $packagesPath = $this->environment->getFullPackagePath();
        $vendorPath = $this->environment->getBasePath() . '/vendor';

        $this->finder->files()
            ->ignoreUnreadableDirs()
            ->in($packagesPath)
            ->in($vendorPath)
            ->exclude('/tests/i')
            ->path('/controller/i')
            ->name('*Command.php');

        /** @var SplFileInfo $file */
        foreach ($this->finder as $file) {
            $parsed = AnnotationsParser::parsePhp(file_get_contents($file->getPathname()));
            $class = array_keys($parsed);
            $proxies = $this->commandProxyGenerator->generateCommandProxy($class[0]);
            foreach ($proxies as $proxy) {
                $commands[] = $proxy;
            }
        }

        $cachePath = $this->getRaptorCachePath();
        $this->createDir($cachePath);
        $filePath = $cachePath . self::COMMAND_CACHE_FILE;

        $this->configurationManager->writeConfiguration(
            $filePath,
            ['commands' => $commands]
        );

        return $this->configurationManager->getConfiguration($filePath);
    }
}