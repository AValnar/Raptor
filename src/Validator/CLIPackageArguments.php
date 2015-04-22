<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 16.03.2015
 * Time: 22:09
 */

namespace Bonefish\Raptor\Validator;


use Bonefish\AbstractTraits\Parameters;
use Bonefish\Interfaces\Validator;

class CLIPackageArguments implements Validator
{
    use Parameters;

    /**
     * @var \Bonefish\Core\PackageManager
     * @Bonefish\Inject
     */
    public $packageManager;

    /**
     * @var bool
     */
    protected $vendorRequired = TRUE;

    /**
     * @var bool
     */
    protected $packageRequired = TRUE;

    /**
     * @return boolean
     */
    public function isPackageRequired()
    {
        return $this->packageRequired;
    }

    /**
     * @param boolean $packageRequired
     * @return self
     */
    public function setPackageRequired($packageRequired)
    {
        $this->packageRequired = $packageRequired;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isVendorRequired()
    {
        return $this->vendorRequired;
    }

    /**
     * @param boolean $vendorRequired
     * @return self
     */
    public function setVendorRequired($vendorRequired)
    {
        $this->vendorRequired = $vendorRequired;
        return $this;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        if (!$this->isValidVendor()) {
            return false;
        }

        return $this->isValidPackage();
    }

    /**
     * @return bool
     */
    protected function isValidPackage()
    {
        return $this->isValidArgument(2, 'isPackageRequired', 'isPackageInstalledByVendorAndPackageName');
    }

    /**
     * @return bool
     */
    protected function isValidVendor()
    {
        return $this->isValidArgument(1, 'isVendorRequired', 'isPackageInstalledByVendor');
    }

    /**
     * @param int $index
     * @param string $internalMethod
     * @param string $packageManagerValidatorMethod
     * @return bool
     */
    private function isValidArgument($index, $internalMethod, $packageManagerValidatorMethod)
    {
        if (!isset($this->arguments[$index])) {
            return !$this->{$internalMethod}();
        }

        $parameter = array();

        for($i = 1; $i <= $index; $i++) {
            $parameter[] = $this->arguments[$i];
        }

        return call_user_func_array(array($this->packageManager, $packageManagerValidatorMethod), $parameter);
    }
} 