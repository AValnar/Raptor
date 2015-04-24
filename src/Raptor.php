<?php


namespace Bonefish\Raptor;

use Bonefish\AbstractTraits\Parameters;
use Bonefish\CLI\ICLI;

/**
 * Copyright (C) 2014  Alexander Schmidt
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
 * @copyright  Copyright (c) 2014, Alexander Schmidt
 * @version    1.0
 * @date       2015-03-14
 * @package Bonefish\Raptor
 */
class Raptor extends CLImateWrapper implements ICLI
{
    use Parameters;

    /**
     * @var \Bonefish\Raptor\Command\Generator
     * @Bonefish\Inject
     */
    public $generator;

    /**
     * Main handler
     */
    public function run()
    {
        $args = array_slice($this->getParameters(), 1);
        $this->getCommandAndPrintOutput($args);
    }

    /**
     * Execute an action
     *
     * @param \Bonefish\Core\Package $package
     * @param string $action
     * @param array $parameters
     */
    public function execute($package, $action, $parameters = array())
    {
        $args = array_merge(array('execute', $package->getVendor(), $package->getName(), $action), $parameters);
        $this->getCommandAndPrintOutput($args);
    }

    /**
     * Explain an action
     *
     * @param \Bonefish\Core\Package $package
     * @param string $action
     */
    public function explain($package, $action)
    {
        $args = array('execute', $package->getVendor(), $package->getName(), $action);
        $this->getCommandAndPrintOutput($args);
    }

    /**
     * Print output if there was any
     *
     * @param null $output
     */
    protected function printOutput($output = NULL)
    {
        if ($output == NULL) {
            return;
        }

        if (is_array($output)) {
            $this->table($output);
            return;
        }

        $this->out($output);
        return;
    }

    /**
     * @param array $args
     */
    protected function getCommandAndPrintOutput($args)
    {
        $this->generator->setParameters($args);

        try {
            $command = $this->generator->getCommand();
            $output = $command->execute();
            $this->printOutput($output);
        } catch (\Exception $e) {
            $this->red($e->getMessage());
        }
    }
} 