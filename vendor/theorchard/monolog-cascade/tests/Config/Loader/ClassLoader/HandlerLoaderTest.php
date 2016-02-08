<?php
/**
 * This file is part of the Monolog Cascade package.
 *
 * (c) Raphael Antonmattei <rantonmattei@theorchard.com>
 * (c) The Orchard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Cascade\Tests\Config\Loader\ClassLoader;

use Monolog\Formatter\LineFormatter;

use Cascade\Config\Loader\ClassLoader\HandlerLoader;

/**
 * Class HandlerLoaderTest
 *
 * @author Raphael Antonmattei <rantonmattei@theorchard.com>
 */
class HandlerLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testHandlerLoader()
    {
        $dummyClosure = function () {
            // Empty function
        };
        $original = $options = array(
            'class' => '\Monolog\Handler\TestHandler',
            'level' => 'DEBUG',
            'formatter' => 'test_formatter',
            'processors' => array('test_processor_1', 'test_processor_2')
        );
        $formatters = array('test_formatter' => new LineFormatter());
        $processors = array(
            'test_processor_1' => $dummyClosure,
            'test_processor_2' => $dummyClosure
        );
        $loader = new HandlerLoader($options, $formatters, $processors);

        $this->assertNotEquals($original, $options);
        $this->assertEquals(new LineFormatter(), $options['formatter']);
        $this->assertContains($dummyClosure, $options['processors']);
        $this->assertContains($dummyClosure, $options['processors']);
    }

    public function testHandlerLoaderWithNoOptions()
    {
        $original = $options = array();
        $loader = new HandlerLoader($options);

        $this->assertEquals($original, $options);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testHandlerLoaderWithInvalidFormatter()
    {
        $options = array(
            'formatter' => 'test_formatter'
        );

        $formatters = array('test_formatterXYZ' => new LineFormatter());
        $loader = new HandlerLoader($options, $formatters);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testHandlerLoaderWithInvalidProcessor()
    {
        $dummyClosure = function () {
            // Empty function
        };
        $options = array(
            'processors' => array('test_processor_1')
        );

        $formatters = array();
        $processors = array('test_processorXYZ' => $dummyClosure);
        $loader = new HandlerLoader($options, $formatters, $processors);
    }

    /**
     * Check if the handler exists for a given class and option
     * Also checks that it a callable and return it
     *
     * @param  string $class Class name the handler applies to
     * @param  string $optionName Option name
     * @return \Closure Closure
     */
    private function getHandler($class, $optionName)
    {
        if (isset(HandlerLoader::$extraOptionHandlers[$class][$optionName])) {
            // Get the closure
            $closure = HandlerLoader::$extraOptionHandlers[$class][$optionName];

            $this->assertTrue(is_callable($closure));

            return $closure;
        } else {
            throw new \Exception(
                sprintf(
                    'Custom handler %s is not defined for class %s',
                    $optionName,
                    $class
                )
            );
        }
    }

    /**
     * Tests that calling the given Closure will trigger a method call with the given param
     * in the given class
     *
     * @param  string   $class      Class name
     * @param  string   $methodName Method name
     * @param  mixed    $methodArg  Parameter passed to the closure
     * @param  \Closure $closure    Closure to call
     */
    private function doTestMethodCalledInHandler($class, $methodName, $methodArg, \Closure $closure)
    {
        // Setup mock and expectations
        $mock = $this->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->setMethods(array($methodName))
            ->getMock();

        $mock->expects($this->once())
            ->method($methodName)
            ->with($methodArg);

        // Calling the handler
        $closure($mock, $methodArg);
    }


    /**
     * Test that handlers exist
     */
    public function testHandlersExist()
    {
        $options = array();
        new HandlerLoader($options);
        $this->assertTrue(count(HandlerLoader::$extraOptionHandlers) > 0);
    }

    /**
     * Data provider for testHandlers
     * /!\ Important note:
     * Just add values to this array if you need to test a newly added handler
     *
     * If one of your handlers calls more than one method you can add more than one entries
     *
     * @return array of array of args for testHandlers
     */
    public function handlerParamsProvider()
    {
        return array(
            array(
                '*',                    // Class name
                'formatter',            // Option name
                new LineFormatter(),    // Option test value
                'setFormatter'          // Name of the method called by your handler
            ),
            array(
                'Monolog\Handler\LogglyHandler',    // Class name
                'tags',                             // Option name
                array('some_tag'),                  // Option test value
                'setTag'                            // Name of the method called by your handler
            )
        );
    }

    /**
     * Test the extra option handlers
     *
     * @dataProvider handlerParamsProvider
     */
    public function testHandlers($class, $optionName, $optionValue, $calledMethodName)
    {
        $options = array();
        new HandlerLoader($options);
        // Test if handler exists and return it
        $closure = $this->getHandler($class, $optionName);

        if ($class == '*') {
            $class = 'Monolog\Handler\TestHandler';
        }

        $this->doTestMethodCalledInHandler($class, $calledMethodName, $optionValue, $closure);
    }

    /**
     * Test extra option processor handler
     */
    public function testHandlerForProcessor()
    {
        $options = array();

        $mockProcessor1 = '123';
        $mockProcessor2 = '456';
        $processorsArray = array($mockProcessor1, $mockProcessor2);

        // Setup mock and expectations
        $mockHandler = $this->getMockBuilder('Monolog\Handler\TestHandler')
            ->disableOriginalConstructor()
            ->setMethods(array('pushProcessor'))
            ->getMock();

        $mockHandler->expects($this->exactly(sizeof($processorsArray)))
            ->method('pushProcessor')
            ->withConsecutive(array($mockProcessor2), array($mockProcessor1));

        new HandlerLoader($options);
        $closure = $this->getHandler('*', 'processors');
        $closure($mockHandler, $processorsArray);
    }
}
