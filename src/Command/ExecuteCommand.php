<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 18.03.2015
 * Time: 09:50
 */

namespace Bonefish\Raptor\Command;


class ExecuteCommand extends ExplainCommand
{

    /**
     * @var array
     */
    protected $suppliedArgs;

    public function execute()
    {
        $command = $this->arguments[3] . ICommand::COMMAND_SUFFIX;
        return call_user_func_array(array($this->controller, $command),$this->suppliedArgs);
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        if (!parent::isValid()) {
            return false;
        }

        $requiredArgs = $this->method->getNumberOfRequiredParameters();
        $this->suppliedArgs = array_slice($this->getParameters(), 4);

        return count($this->suppliedArgs) >= $requiredArgs;
    }

    /**
     * Return the first parameter that has to be set to create a valid command
     *
     * @return string
     */
    public function getFirstParameter()
    {
        return 'execute';
    }
} 