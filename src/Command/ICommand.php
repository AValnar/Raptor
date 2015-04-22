<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 15.03.2015
 * Time: 10:48
 */

namespace Bonefish\Raptor\Command;


interface ICommand
{
    const COMMAND_SUFFIX = 'Command';

    /**
     * @return NULL|string|array
     */
    public function execute();

    /**
     * @return bool
     */
    public function isValid();

    /**
     * @param array $arguments
     */
    public function setParameters(array $arguments);

    /**
     * Return the first parameter that has to be set to create a valid command
     *
     * @return string
     */
    public function getFirstParameter();
} 