<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 18.03.2015
 * Time: 09:50
 */

namespace Bonefish\Raptor\Command;

use Bonefish\AbstractTraits\Parameters;
use Bonefish\CLI\CLImateWrapper;
use Bonefish\Controller\Command;
use Bonefish\Core\Package;

class ExplainCommand extends CLImateWrapper implements ICommand
{
    use Parameters;

    /**
     * @var \Bonefish\Raptor\Validator\CLIPackageArguments
     * @Bonefish\Inject
     */
    public $packageArgumentValidator;

    /**
     * @var \Bonefish\Core\PackageManager
     * @Bonefish\Inject
     */
    public $packageManager;

    /**
     * @var \Bonefish\Core\Package
     */
    protected $package;

    /**
     * @var Command
     */
    protected $controller;

    /**
     * @var \ReflectionClass
     */
    protected $reflection;

    /**
     * @var \ReflectionMethod
     */
    protected $method;

    public function execute()
    {
        $this->out('Supplied command: '.implode(' ', $this->getParameters()));
        $this->br();
        $this->out($this->method->getDocComment());

        $parameters = $this->method->getParameters();
        $table = [];

        foreach($parameters as $i => $parameter)
        {
            $table[] = [
                '#' => $i,
                'Name' => $parameter->getName(),
                'Optional?' => $parameter->isOptional() ? 'yes' : 'no',
                'Default value' => $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : '~none~'
            ];
        }

        if (!empty($table)) {
            $this->br();
            $this->out('Parameters: ');
            $this->table($table);
        }
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

        if (!$this->packageArgumentValidator->isValid()) {
            return false;
        }

        $this->package = $this->packageManager->createPackage($this->arguments[1], $this->arguments[2]);
        $this->controller = $this->package->getController(Package::TYPE_COMMAND);

        $this->reflection = new \ReflectionClass($this->controller);
        try {
            $this->method = $this->reflection->getMethod($this->arguments[3] . ICommand::COMMAND_SUFFIX);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @return bool
     */
    protected function isValidFirstArg()
    {
        return strtolower($this->arguments[0]) === $this->getFirstParameter();
    }

    /**
     * Return the first parameter that has to be set to create a valid command
     *
     * @return string
     */
    public function getFirstParameter()
    {
        return 'explain';
    }
} 