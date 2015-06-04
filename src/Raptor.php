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

use Bonefish\CLI\CLIInterface;
use Bonefish\Raptor\Command\CommandInterface;
use Bonefish\Raptor\Command\HelpCommand;
use Bonefish\Traits\Parametrized;

class Raptor implements CLIInterface
{

    use Parametrized;

    protected $validCommandTypes = [
        self::HELP_COMMAND,
        self::LIST_COMMAND,
        self::EXPLAIN_COMMAND,
        self::EXECUTE_COMMAND
    ];

    /**
     * Main handler which is called after all arguments have been passed
     *
     * The CLI must be able to execute the following commands:
     *
     * - help
     * - list
     * - explain <vendor> <package> <command>
     * - execute <vendor> <package> <command> [argument]*
     */
    public function run()
    {
        $parameters = $this->validateParameters();

        $this->executeCommand($parameters);
    }

    /**
     * Validate passed parameters to be valid
     *
     * @return array
     */
    protected function validateParameters()
    {
        $parameters = $this->getParameters();

        $this->basicParametersValidation($parameters);
        $this->validateExplainAndExecuteCommandFormat($parameters);

        return $parameters;
    }

    /**
     * Validate arguments to be in a valid format
     *
     * @param $parameters
     */
    protected function basicParametersValidation($parameters)
    {
        if (count($parameters) < 1) {
            echo 'Invalid amount of parameters.';
            exit(self::INVALID_PARAMETER_AMOUNT);
        }

        if (!in_array($parameters[0], $this->validCommandTypes)) {
            echo 'Invalid command type (' . $parameters[0] . ').';
            exit(self::INVALID_COMMAND_TYPE);
        }
    }

    /**
     * Validate explain and execute command for basic sanity.
     *
     * @param $parameters
     */
    protected function validateExplainAndExecuteCommandFormat($parameters)
    {
        if ($parameters[0] !== self::EXPLAIN_COMMAND && $parameters[0] !== self::EXECUTE_COMMAND) {
            return;
        }

        if (count($parameters) < 4) {
            echo 'Invalid amount of parameters.';
            exit(self::INVALID_PARAMETER_AMOUNT);
        }
    }

    /**
     * @param array $parameters
     */
    protected function executeCommand(array $parameters)
    {
        /** @var CommandInterface $command */
        $command = null;

        switch ($parameters[0]) {
            case self::HELP_COMMAND :
                $command = new HelpCommand();
                break;
            case self::EXPLAIN_COMMAND :
                echo 'Explain command';
                break;
            case self::EXECUTE_COMMAND :
                echo 'Execute command';
                break;
            default :
                echo 'List command';
                breaK;
        }

        $command->setParameters(array_slice($parameters, 1));
        $returnCode = $command->execute();
        exit(intval($returnCode));
    }
}