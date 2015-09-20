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
 * @date       07.06.2015
 */

namespace Bonefish\Raptor;


use Bonefish\Reflection\Meta\ClassMeta;
use Bonefish\Reflection\Meta\MethodMeta;
use Bonefish\Reflection\ReflectionService;
use Bonefish\Traits\DirectoryCreator;
use Bonefish\Utility\Environment;
use PhpParser\BuilderFactory;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\PrettyPrinter\Standard;
use Bonefish\Injection\Annotations as Bonefish;

final class CommandProxyGenerator
{

    use DirectoryCreator;

    /**
     * @var BuilderFactory
     */
    public $phpGenerator;

    /**
     * @var ReflectionService
     */
    protected $reflectionService;

    /**
     * @var Standard
     */
    protected $prettyPrinter;

    const PROXY_NAMESPACE = 'Bonefish\Raptor\Proxy';
    const COMMAND_SUFFIX = 'Command';
    const COMMAND_PROXY_NAME_SEPARATOR = '_';


    public function __construct(
        BuilderFactory $phpGenerator,
        ReflectionService $reflectionService,
        Standard $prettyPrinter
    )
    {
        $this->phpGenerator = $phpGenerator;
        $this->reflectionService = $reflectionService;
        $this->prettyPrinter = $prettyPrinter;
    }


    /**
     * Get path where raptor should store proxy classes
     *
     * @return string
     */
    public function getRaptorCachePath()
    {
        return Raptor::RAPTOR_CACHE_PATH;
    }

    /**
     * Entry point to create proxies of a bonefish command class
     *
     * @param string $className
     * @return array
     */
    public function generateCommandProxy($className)
    {
        $classMeta = $this->reflectionService->getClassMetaReflection($className);
        $proxies = [];

        $cachePath = $this->getRaptorCachePath();
        $this->createDirectory($cachePath);
        $thisNode = new Variable('this');

        foreach ($classMeta->getMethods() as $methodMeta) {

            // skip if method is not a command or an inherited command
            if (!stristr($methodMeta->getName(), self::COMMAND_SUFFIX) ||
                $methodMeta->getDeclaringClass() !== $classMeta) {
                continue;
            }

            $proxies[] = $this->generateProxyForMethod($cachePath, $thisNode, $methodMeta, $classMeta);
        }

        return $proxies;
    }

    /**
     * Create a unique command name
     *
     * @param MethodMeta $methodMeta
     * @param ClassMeta $classMeta
     * @return string
     */
    protected function getCommandName($methodMeta, $classMeta)
    {
        $nameSpaceParts = explode('\\', $classMeta->getNamespace());
        $vendor = $nameSpaceParts[0];
        $package = $nameSpaceParts[1];

        return strtolower($vendor . ':' . $package . ':' . str_replace(self::COMMAND_SUFFIX, '', $methodMeta->getName()));
    }

    /**
     * @param MethodMeta $methodMeta
     * @param ClassMeta $classMeta
     * @return string
     */
    protected function getProxyClassName($methodMeta, $classMeta)
    {
        $nameSpaceParts = explode('\\', $classMeta->getNamespace());

        // Raptor_Proxy_Command_Name_Space_ShortName_MethodName

        return 'Raptor_Proxy_' .
                implode(self::COMMAND_PROXY_NAME_SEPARATOR, $nameSpaceParts) .
                self::COMMAND_PROXY_NAME_SEPARATOR . $classMeta->getShortName() .
                '_' . ucfirst($methodMeta->getName());
    }

    /**
     * @param string $cachePath
     * @param Variable $thisNode
     * @param MethodMeta $methodMeta
     * @param ClassMeta $classMeta
     * @return string
     */
    protected function generateProxyForMethod($cachePath, $thisNode, $methodMeta, $classMeta)
    {
        $name = $this->getProxyClassName($methodMeta, $classMeta);
        $fullName = self::PROXY_NAMESPACE . '\\' . $name;
        $commandName = $this->getCommandName($methodMeta, $classMeta);

        // Nodes to create the configuration method block
        $configNodes = [];
        // Nodes to create the execute method block
        $executeNodes = [];
        // Nodes of the arguments to execute the command
        $executeArgs = [];

        // $this->setName($commandName)
        $configNodes[] = $this->simpleMethodCallNode($thisNode, 'setName', $commandName);
        // $this->setDescription($methodMeta->getDescription())
        $configNodes[] = $this->simpleMethodCallNode($thisNode, 'setDescription', $methodMeta->getDescription());

        foreach ($methodMeta->getParameters() as $parameter) {
            // $this->addArgument($parameter->getName(), $parameter->isOptional() ? InputArgument::OPTIONAL : InputArgument::REQUIRED)
            $configNodes[] = new MethodCall(
                $thisNode,
                'addArgument',
                [
                    new Arg(new String_($parameter->getName())),
                    new Arg(new ClassConstFetch(new Name('InputArgument'),
                        $parameter->isOptional() ? 'OPTIONAL' : 'REQUIRED'))
                ]
            );
            // $input->getArgument($parameter->getName())
            $executeArgs[] = new Arg(
                new MethodCall(
                    new Variable('input'),
                    'getArgument',
                    [
                        new Arg(new String_($parameter->getName()))
                    ]
                )
            );
        }

        $commandControllerVariable = new Variable('commandController');

        // $this->container->get($classMeta->getName())
        $executeNodes[] = new Assign(
            $commandControllerVariable,
            new MethodCall(
                new PropertyFetch(
                    $thisNode,
                    'container'
                ),
                'get',
                [
                    new Arg(new String_($classMeta->getName()))
                ]
            )
        );

        // $commandController->{$methodMeta->getName()}(...$executeArgs)
        $executeNodes[] = new MethodCall(
            $commandControllerVariable,
            $methodMeta->getName(),
            $executeArgs
        );

        // See the generated proxy for this one...
        $node = $this->phpGenerator->namespace(self::PROXY_NAMESPACE)
            ->addStmt($this->phpGenerator->use('Symfony\Component\Console\Command\Command'))
            ->addStmt($this->phpGenerator->use('Symfony\Component\Console\Input\InputArgument'))
            ->addStmt($this->phpGenerator->use('Symfony\Component\Console\Input\InputInterface'))
            ->addStmt($this->phpGenerator->use('Symfony\Component\Console\Output\OutputInterface'))
            ->addStmt($this->phpGenerator->use('Bonefish\Injection\ContainerInterface'))
            ->addStmt($this->phpGenerator->use('Bonefish\Injection\Annotations')->as('Bonefish'))
            ->addStmt($this->phpGenerator->class($name)
                ->extend('Command')
                ->addStmt($this->phpGenerator->property('container')
                    ->makePublic()
                    ->setDocComment('/**
                        * @var ContainerInterface
                        * @Bonefish\Inject
                        */')
                )
                ->addStmt($this->phpGenerator->method('configure')
                    ->makeProtected()
                    ->addStmts($configNodes)
                )
                ->addStmt($this->phpGenerator->method('execute')
                    ->makeProtected()
                    ->addParam($this->phpGenerator->param('input')->setTypeHint('InputInterface'))
                    ->addParam($this->phpGenerator->param('output')->setTypeHint('OutputInterface'))
                    ->addStmts($executeNodes)
                )
            )
            ->getNode();

        // Generate Code finally
        $code = $this->prettyPrinter->prettyPrintFile([$node]);
        // Write file
        file_put_contents($cachePath . $name . '.php', $code);

        // Return proxy class name
        return $fullName;
    }

    /**
     * @param Variable $thisNode
     * @param $methodName
     * @param $stringParameter
     * @return MethodCall
     */
    protected function simpleMethodCallNode($thisNode, $methodName, $stringParameter)
    {
        return new MethodCall(
            $thisNode,
            $methodName,
            [
                new Arg(new String_($stringParameter))
            ]
        );
    }
}