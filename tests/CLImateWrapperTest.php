<?php
/**
 * Created by PhpStorm.
 * User: E8400
 * Date: 29.03.2015
 * Time: 12:58
 */

namespace Bonefish\Tests\Raptor;


use Bonefish\Raptor\CLImateWrapper;

class CLImateWrapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CLImateWrapper
     */
    protected $sut;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $climate;

    public function setUp()
    {
        $this->sut = new CLImateWrapper();
        $this->climate = $this->getMock('\League\CLImate\CLImate');
        $this->sut->climate = $this->climate;
    }

    /**
     * @dataProvider callsOnClimateDataProvider
     * @param string $method
     * @param mixed $input
     */
    public function testCallsOnClimate($method, $input = NULL)
    {
        $expectedResult = 'Foo';

        $expectedCallArg = array();

        if ($input != NULL)
        {
            $expectedCallArg = array($input);
        }

        $this->climate->expects($this->once())
            ->method('__call')
            ->with($method, $expectedCallArg)
            ->will($this->returnValue($expectedResult));

        $this->assertThat(
            $this->sut->{$method}($input),
            $this->equalTo($expectedResult)
        );
    }

    public function callsOnClimateDataProvider()
    {
        return array(
            array('br'),
            array('table', array('foo')),
            array('out', 'foo'),
            array('red', 'foo'),
        );
    }
}
