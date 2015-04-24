<?php
/**
 * Copyright (C) 2014-2015  Alexander Schmidt
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
 * @date       2015-03-29
 * @package Bonefish\CLI\Raptor
 */

namespace Bonefish\Raptor;

class CLImateWrapper
{
    /**
     * @var \League\CLImate\CLImate
     * @Bonefish\Inject
     */
    public $climate;

    /**
     * Display a line break
     * @return mixed
     */
    public function br()
    {
        return $this->climate->br();
    }

    /**
     * Display a table
     * @param array $data
     * @return mixed
     */
    public function table(array $data = array())
    {
        return $this->climate->table($data);
    }

    /**
     * Print a line of text on the command line
     * @param string $text
     * @return mixed
     */
    public function out($text = '')
    {
        return $this->climate->out($text);
    }

    /**
     * Print a red line of text on the command line
     * @param string $text
     * @return mixed
     */
    public function red($text = '')
    {
        return $this->climate->red($text);
    }
} 