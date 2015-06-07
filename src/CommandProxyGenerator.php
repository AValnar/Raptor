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

class CommandProxyGenerator
{

    use DirectoryCreator;

    /**
     * @var Environment
     * @Bonefish\Inject
     */
    public $environment;

    /**
     * @var BuilderFactory
     * @Bonefish\Inject
     */
    public $phpGenerator;

    /**
     * @var ReflectionService
     * @Bonefish\Inject
     */
    public $reflectionService;

    public function getRaptorCachePath()
    {
        return $this->environment->getFullCachePath() . Raptor::RAPTOR_CACHE_PATH;
    }

    public function generateCommandProxy($className)
    {
        $classMeta = $this->reflectionService->getClassMetaReflection($className);
        $nameSpaceParts = explode('\\', $classMeta->getNamespace());
        $namespace = 'Bonefish\Raptor\Proxy';
        $vendor = $nameSpaceParts[0];
        $package = $nameSpaceParts[1];

        $proxies = [];

        $thisNode = new Variable('this');

        foreach ($classMeta->getMethods() as $method) {

            if (!stristr($method->getName(), 'Command')) {
                continue;
            }

            $name = 'Raptor_Proxy_' . implode('_',
                    $nameSpaceParts) . '_' . $classMeta->getShortName() . '_' . ucfirst($method->getName());
            $fullName = $namespace . '\\' . $name;

            $commandName = strtolower($vendor . ':' . $package . ':' . str_replace('Command', '', $method->getName()));

            $configNodes = [];
            $executeNodes = [];

            $executeArgs = [];

            $configNodes[] = new MethodCall(
                $thisNode,
                'setName',
                [
                    new Arg(new String_($commandName))
                ]
            );

            $configNodes[] = new MethodCall(
                $thisNode,
                'setDescription',
                [
                    new Arg(new String_($method->getDescription()))
                ]
            );

            foreach ($method->getParameters() as $parameter) {
                $configNodes[] = new MethodCall(
                    $thisNode,
                    'addArgument',
                    [
                        new Arg(new String_($parameter->getName())),
                        new Arg(new ClassConstFetch(new Name('InputArgument'),
                            $parameter->isOptional() ? 'OPTIONAL' : 'REQUIRED'))
                    ]
                );
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

            $executeNodes[] = new Assign(
                $commandControllerVariable,
                new MethodCall(
                    new PropertyFetch(
                        $thisNode,
                        'container'
                    ),
                    'get',
                    [
                        new Arg(new String_($className))
                    ]
                )
            );

            $executeNodes[] = new MethodCall(
                $commandControllerVariable,
                $method->getName(),
                $executeArgs
            );


            $node = $this->phpGenerator->namespace($namespace)
                ->addStmt($this->phpGenerator->use('Symfony\Component\Console\Command\Command'))
                ->addStmt($this->phpGenerator->use('Symfony\Component\Console\Input\InputArgument'))
                ->addStmt($this->phpGenerator->use('Symfony\Component\Console\Input\InputInterface'))
                ->addStmt($this->phpGenerator->use('Symfony\Component\Console\Output\OutputInterface'))
                ->addStmt($this->phpGenerator->use('Bonefish\Injection\ContainerInterface'))
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

            $stmts = array($node);
            $prettyPrinter = new Standard();
            $code = $prettyPrinter->prettyPrintFile($stmts);

            $cachePath = $this->getRaptorCachePath();
            $this->createDir($cachePath);
            file_put_contents($cachePath . $name . '.php', $code);
            $proxies[] = $fullName;

        }

        return $proxies;
    }

}