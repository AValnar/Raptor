<?php

namespace Bonefish\Raptor\Command;


use Bonefish\CLI\CLIInterface;
use Bonefish\Traits\Parametrized;

class HelpCommand implements CommandInterface
{
    use Parametrized;

    public function execute()
    {
        echo "Command\t\t\tArguments\t\t\tDescription" . PHP_EOL;
        echo CLIInterface::HELP_COMMAND . "\t\t\t\t\t\t\tDisplay help for cli" . PHP_EOL;
        echo CLIInterface::LIST_COMMAND . "\t\t\t[<vendor>] [<package>]\t\tDisplay all commands. This list can be further filtered by vendor and then also by package." . PHP_EOL;
        echo CLIInterface::EXPLAIN_COMMAND . "\t\t\t<vendor> <package> <command>\tExplain a command. This will show the name, description and arguments." . PHP_EOL;
        echo CLIInterface::EXECUTE_COMMAND . "\t\t\t[<vendor>] [<package>]\t\tExecute a command. The number of arguments depends on the command. If you are unsure use explain first!" . PHP_EOL;

        return 0;
    }

}