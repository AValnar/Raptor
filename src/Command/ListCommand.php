<?php

namespace Bonefish\Raptor\Command;


use Bonefish\AbstractTraits\Parameters;
use Bonefish\Raptor\CLImateWrapper;

class ListCommand extends CLImateWrapper implements ICommand
{
    use Parameters;

    /**
     * @var \Bonefish\Raptor\Cache\ListCacheGenerator
     * @Bonefish\Inject
     */
    public $cacheGenerator;

    /**
     * @var \Bonefish\Raptor\Validator\CLIPackageArguments
     * @Bonefish\Inject
     */
    public $packageArgumentValidator;

    const CACHE_KEY = 'raptor.cache.list';

    /**
     * @var array
     */
    protected $cachedList = array();

    public function execute()
    {
        $count = count($this->getParameters());

        if ($count === 3) {
            $this->displayPackage();
            return;
        }

        if ($count === 2) {
            $this->displayVendor();
            return;
        }

        $this->displayAll();
        return;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        if (!$this->isValidFirstArg()) {
            return false;
        }

        $this->packageArgumentValidator->setParameters($this->arguments);
        $this->packageArgumentValidator->setVendorRequired(FALSE)
            ->setPackageRequired(FALSE);

        return $this->packageArgumentValidator->isValid();
    }

    /**
     * @return bool
     */
    protected function isValidFirstArg()
    {
        return strtolower($this->arguments[0]) === $this->getFirstParameter();
    }

    protected function prepareCache()
    {
        return $this->cachedList = $this->cacheGenerator->generate(self::CACHE_KEY);
    }

    /**
     * Return the first parameter that has to be set to create a valid command
     *
     * @return string
     */
    public function getFirstParameter()
    {
        return 'list';
    }

    protected function displayAll()
    {
        $this->prepareCache();

        foreach ($this->cachedList as $vendor => $packages) {
            foreach ($packages as $package => $commands) {
                $this->out('<light_blue>' . $vendor . '</light_blue> <light_blue>' . $package . '</light_blue>');
                $this->br();
                foreach ($commands as $command) {
                    $this->out($vendor . ' ' . $package . ' ' . $command);
                }
                $this->br();
            }
        }
    }

    protected function displayVendor()
    {
        $this->prepareCache();

        $vendor = $this->arguments[1];

        if (!isset($this->cachedList[$vendor])) {
            throw new \InvalidArgumentException('Invalid vendor specified!');
        }

        $packages = $this->cachedList[$vendor];

        foreach ($packages as $package => $commands) {
            $this->out('<light_blue>' . $vendor . '</light_blue> <light_blue>' . $package . '</light_blue>');
            $this->br();
            foreach ($commands as $command) {
                $this->out($vendor . ' ' . $package . ' ' . $command);
            }
            $this->br();
        }

    }

    protected function displayPackage()
    {
        $this->prepareCache();

        $vendor = $this->arguments[1];
        $package = $this->arguments[2];

        if (!isset($this->cachedList[$vendor])) {
            throw new \InvalidArgumentException('Invalid vendor specified!');
        }

        if (!isset($this->cachedList[$vendor][$package])) {
            throw new \InvalidArgumentException('Invalid package specified!');
        }

        $commands = $this->cachedList[$vendor][$package];

        $this->out('<light_blue>' . $vendor . '</light_blue> <light_blue>' . $package . '</light_blue>');
        $this->br();
        foreach ($commands as $command) {
            $this->out($vendor . ' ' . $package . ' ' . $command);
        }
    }

}