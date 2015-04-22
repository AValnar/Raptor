<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 17.03.2015
 * Time: 07:06
 */

namespace Bonefish\Raptor\Cache;


use Bonefish\Cache\ICache;
use Bonefish\Raptor\Command\ICommand;
use Bonefish\Core\Package;

class ListCacheGenerator
{
    /**
     * @var \Bonefish\Core\PackageManager
     * @Bonefish\Inject
     */
    public $packageManager;

    /**
     * @var ICache
     * @Bonefish\Inject(key=Raptor)
     */
    public $cache;

    /**
     * @var \Bonefish\Reflection\Helper
     * @Bonefish\Inject
     */
    public $reflectorHelper;

    /**
     * @param string $key
     * @return array
     */
    public function generate($key)
    {
        $list = $this->cache->fetch($key);

        return ($list !== NULL) ? $list : $this->generateCache($key);
    }

    /**
     * @param string $key
     * @return array
     */
    protected function generateCache($key)
    {
        $packages = $this->packageManager->getAllPackages();

        $list = array();

        foreach ($packages as $package) {
            $list[$package->getVendor()][$package->getName()] = $this->getPackageCommands($package);
        }

        $this->cache->save($key, $list);

        return $list;
    }

    /**
     * @param Package $package
     * @return array
     */
    protected function getPackageCommands($package)
    {
        $controllerCommands = array();

        $commandController = $package->getController(Package::TYPE_COMMAND);
        $reflection = new \ReflectionClass($commandController);
        $commands = $this->reflectorHelper->getSuffixMethods(ICommand::COMMAND_SUFFIX, $reflection);

        if (!empty($commands)) {
            foreach ($commands as $command) {
                $controllerCommands[] = str_replace(ICommand::COMMAND_SUFFIX, '', $command->getName());
            }
        }

        return $controllerCommands;
    }
} 