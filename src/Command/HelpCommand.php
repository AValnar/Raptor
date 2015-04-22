<?php

namespace Bonefish\Raptor\Command;


use Bonefish\AbstractTraits\Parameters;

class HelpCommand implements ICommand
{
    use Parameters;

    public function execute()
    {
        return [
            [
                'Command type' => 'help',
                'Arguments' => '~none~',
                'Description' => 'Display this help'
            ],
            [
                'Command type' => 'list',
                'Arguments' => '[VendorName] [PackageName]',
                'Description' => 'Display all commands. This list can be further filtered by vendor and then also by package.'
            ],
            [
                'Command type' => 'execute',
                'Arguments' => 'VendorName PackageName CommandName [CommandArguments]',
                'Description' => 'Execute a specific command. The number of arguments depends on the command. If you are unsure use explain first!'
            ],
            [
                'Command type' => 'explain',
                'Arguments' => 'VendorName PackageName CommandName',
                'Description' => 'Explain a specific command. This will show the name, description and arguments.'
            ]
        ];
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return strtolower($this->arguments[0]) == $this->getFirstParameter();
    }

    /**
     * Return the first parameter that has to be set to create a valid command
     *
     * @return string
     */
    public function getFirstParameter()
    {
       return 'help';
    }

}