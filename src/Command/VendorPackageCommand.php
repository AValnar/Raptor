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
 * @date       04.06.2015
 */

namespace Bonefish\Raptor\Command;


abstract class VendorPackageCommand
{
    /**
     * @var array
     */
    protected $parameters = [];

    /**
     * @var string
     */
    protected $vendor = null;

    /**
     * @var string
     */
    protected $package = null;

    /**
     * @var string
     */
    protected $command = null;

    private function setProperty($list, $index, $setter)
    {
        if (isset($list[$index])) {
            $this->{$setter}($list[$index]);
            return true;
        }
        return false;
    }

    /**
     * @param array $parameters
     */
    public function setParameters(array $parameters = [])
    {
        $slice = 0;

        $setters = ['setVendor', 'setPackage', 'setCommand'];

        foreach($setters as $index => $setter) {
            if ($this->setProperty($parameters, $index, $setter)) $slice++;
        }

        $this->parameters = array_slice($parameters, $slice);
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }


    /**
     * @return string
     */
    public function getVendor()
    {
        return $this->vendor;
    }

    /**
     * @param string $vendor
     * @return self
     */
    public function setVendor($vendor)
    {
        $this->vendor = $vendor;

        return $this;
    }

    /**
     * @return string
     */
    public function getPackage()
    {
        return $this->package;
    }

    /**
     * @param string $package
     * @return self
     */
    public function setPackage($package)
    {
        $this->package = $package;

        return $this;
    }

    /**
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * @param string $command
     * @return self
     */
    public function setCommand($command)
    {
        $this->command = $command;

        return $this;
    }



}