<?php

use Orchestra\Testbench\TestCase;


class HookManagerTestCase extends TestCase
{
    /**
     * @var \Pikart\LaravelHooks\HookManager
     */
    protected $hookManager;

    public function setUp()
    {
        parent::setUp();
        $this->hookManager = $this->app['pikart.laravel-hook'];
    }

    protected function getPackageProviders($app)
    {
        return ['Pikart\LaravelHooks\HookServiceProvider'];
    }

    protected function getPackageAliases($app)
    {
        return [
            'HookManager' => 'Pikart\LaravelHooks\Facades\HookManager'
        ];
    }

    /**
     * @test
     */
    public function testCanGetHookMangerFromLaravelContainer()
    {
        $this->assertInstanceOf( \Pikart\LaravelHooks\HookManager::class, $this->app['pikart.laravel-hook'] );
    }

    /**
     * @test
     */
    public function testCanCreateNewHookManagerInstance()
    {
        $this->assertInstanceOf( \Pikart\LaravelHooks\HookManager::class, new \Pikart\LaravelHooks\HookManager( $this->app ) );
    }

    /**
     * @test
     */
    public function testCanRegisterAndGetClosure()
    {
        $this->hookManager->register('hook', function(){});
        $hook = $this->hookManager->getRaw('hook')[0];
        $this->assertInstanceOf(Closure::class, $hook['concrete'] );
    }

    /**
     * @test
     */
    public function testCanRegisterAndHookClosure()
    {
        $this->hookManager->register('hook', function(){
            return 'test';
        });
        $hook = $this->hookManager->hook('hook');
        $this->assertEquals('test', $hook );
    }

    /**
     * @test
     */
    public function testCanRegisterAndGetClassName()
    {
        $this->hookManager->register('hook', TestHookClass::class);
        $hook = $this->hookManager->getRaw('hook')[0];
        $this->assertEquals(TestHookClass::class, $hook['concrete'] );
    }

    /**
     * @test
     */
    public function testCanCreateInstaceFromClassName()
    {
        $this->hookManager->register('hook', TestHookClass::class);
        $this->hookManager->register('hook2', TestHookClass::class);
        $hook = $this->hookManager->get('hook')[0];
        $this->assertInstanceOf(TestHookClass::class, $hook );
    }

    /**
     * @test
     */
    public function testCanRegisterAndHookClassName()
    {
        $this->hookManager->register('hook', TestHookClass::class);
        $hook = $this->hookManager->hook('hook');
        $this->assertEquals('testClass', $hook );
    }

    /**
     * @test
     */
    public function testCanRegisterAndGetClassInstance()
    {
        $testHookClass = new TestHookClass();
        $this->hookManager->register('hook', $testHookClass);
        $hook = $this->hookManager->getRaw('hook')[0];
        $this->assertEquals($testHookClass, $hook['concrete'] );
    }

    /**
     * @test
     */
    public function testCanRegisterAndHookClassInstance()
    {
        $testHookClass = new TestHookClass();
        $this->hookManager->register('hook', $testHookClass);
        $hook = $this->hookManager->hook('hook');
        $this->assertEquals('testClass', $hook );
    }

    /**
     * @test
     */
    public function testCanHookMultipleHookTypes()
    {
        $this->hookManager->register('hook', function(){
            return 'testClosure';
        });

        $testHookClass = new TestHookClass();
        $this->hookManager->register('hook', $testHookClass);

        $this->hookManager->register('hook', TestHookClass::class);

        $hook = $this->hookManager->hook('hook');
        $this->assertEquals('testClosuretestClasstestClass', $hook );
    }

    /**
     * @test
     */
    public function testCanHookMultipleHookTypesWithArguments()
    {

        $this->hookManager->register('hook', function( $args ){
            return $args['testArg'];
        });

        $testHookClass = new TestHookClass();
        $this->hookManager->register('hook', $testHookClass);

        $this->hookManager->register('hook', TestHookClass::class);

        $hook = $this->hookManager->hook('hook', [
            'testArg' => 'testArg'
        ]);

        $this->assertEquals('testArgtestArgtestArg', $hook );
    }

    /**
     * @test
     */
    public function testCanSetOrderByPriority()
    {
        $this->hookManager->register('hook', TestHookClass::class);
        $this->hookManager->register('hook', TestHookClass2::class,1);

        $this->assertInstanceOf(TestHookClass2::class, $this->hookManager->get('hook')[0] );
    }

    /**
     * @test
     */
    public function testCanRegisterDuplicatesIfDuplicatesAreAllowed()
    {
        $this->app['config']->set('hook.allow_duplicates', true);
        $this->hookManager->register('hook', TestHookClass::class);
        $this->hookManager->register('hook', TestHookClass::class);

        $this->assertInstanceOf(TestHookClass::class, $this->hookManager->get('hook')[0]);
        $this->assertInstanceOf(TestHookClass::class, $this->hookManager->get('hook')[1]);
    }

    /**
     * @test
     */
    public function testCanCheckIfClassNameAlreadyExists()
    {
        $this->hookManager->register('hook', TestHookClass::class);

        $this->assertEquals(true, $this->hookManager->exist(TestHookClass::class));
    }

    /**
     * @test
     */
    public function testCanGetRawHooksByContract()
    {
        $this->hookManager->register('hook', TestHookClass::class);
        $this->assertEquals(TestHookClass::class, $this->hookManager->getRaw('hook')[0]['concrete']);
    }

    /**
     * @test
     */
    public function testCanGetAllRawHooks()
    {
        $this->hookManager->register('hook', TestHookClass::class);
        $this->hookManager->register('hook2', TestHookClass2::class);

        $all = $this->hookManager->getRaw();

        $this->assertEquals(TestHookClass::class, $all['hook'][0]['concrete']);
        $this->assertEquals(TestHookClass2::class, $all['hook2'][0]['concrete']);
    }

    /**
     * @test
     */
    public function testHookCanCallCustomClassMethod()
    {
        $this->hookManager->register(TestHookInterface::class, new Class extends \Pikart\LaravelHooks\Hook implements TestHookInterface {
            public function customMethod( array $args ) : string
            {
                return 'test';
            }

            public function handle(array $args): ?string {
                return null;
            }
        });

        $this->assertEquals('test', $this->hookManager->hook( TestHookInterface::class, [], 'customMethod' ));
    }

    /**
     * @test
     */
    public function testHookCanCallCustomClassMethodWithArguments()
    {
        $this->hookManager->register(TestHookInterface::class, new Class extends \Pikart\LaravelHooks\Hook implements TestHookInterface {
            public function customMethod( array $args ) : string
            {
                return $args['arg'];
            }

            public function handle(array $args): ?string {
                return null;
            }
        });

        $this->assertEquals('test', $this->hookManager->hook( TestHookInterface::class, [
            'arg' => 'test'
        ], 'customMethod' ));
    }

    /**
     * @expectedException \Pikart\LaravelHooks\Exceptions\DuplicateExcepiton
     */
    public function testShouldThrowDuplicateExceptionIfClassNameAlreadyExistsIfDuplicatesAreNotAllowed()
    {
        $this->hookManager->register('hook', TestHookClass::class);
        $this->hookManager->register('hook', TestHookClass::class);
    }

    /**
     * @expectedException \Pikart\LaravelHooks\Exceptions\ContractException
     */
    public function testShouldThrowContractExceptionIfClassNotImplementHookInterface()
    {
        $this->hookManager->register('hook', new Class{});
        $this->hookManager->hook('hook');
    }

    /**
     * @expectedException ReflectionException
     */
    public function testShouldThrowReflectionExceptionIfHookClassStringDoesNotExist()
    {
        $this->hookManager->register('hook', 'string');
        $this->hookManager->hook('hook');
    }

    /**
     * @expectedException \Pikart\LaravelHooks\Exceptions\ContractException
     */
    public function testShouldThrowContractExceptionIfClassNotImplementContractInterface()
    {
        $this->hookManager->register(TestHookInterface::class, new Class{});
        $this->hookManager->hook(TestHookInterface::class);
    }

    /**
     * @expectedException \Pikart\LaravelHooks\Exceptions\ContractException
     */
    public function testShouldThrowContractExceptionIfCotractImplementationIsClosure()
    {
        $this->hookManager->register(TestHookInterface::class, function(){});
        $this->hookManager->hook(TestHookInterface::class);
    }

}

class TestHookClass extends \Pikart\LaravelHooks\Hook {
    public function handle(array $args): ?string{

        if( isset( $args['testArg'] ) )
        {
            return $args['testArg'];
        }

        return 'testClass';
    }
}

class TestHookClass2 extends \Pikart\LaravelHooks\Hook {
    public function handle(array $args): ?string{

        if( isset( $args['testArg'] ) )
        {
            return $args['testArg'];
        }

        return 'testClass2';
    }
}

interface TestHookInterface extends \Pikart\LaravelHooks\Contracts\Hook {
    public function customMethod( array $args ) : string;
};


