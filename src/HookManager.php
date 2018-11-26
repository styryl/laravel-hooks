<?php

namespace Pikart\LaravelHooks;
use Closure;
use Illuminate\Contracts\Container\Container;
use Pikart\LaravelHooks\Contracts\Hook;
use Pikart\LaravelHooks\Exceptions\ContractException;
use Pikart\LaravelHooks\Exceptions\DuplicateExcepiton;

class HookManager
{
    /**
     * Registered hooks.
     *
     * @var array
     */
    private $hooks = [];

    /**
     * Laravel app.
     *
     * @var Container
     */
    private $container;

    /**
     * Singleton instances.
     *
     * @var array
     */
    private $instances = [];

    /**
     * HookManager constructor.
     *
     * @param Container $container
     */
    public function __construct( Container $container )
    {
        $this->container = $container;
    }

    /**
     * Registers hook to contract.
     *
     * @param string $contract
     * @param string|Object $hook
     * @param int $priority
     * @throws DuplicateExcepiton
     */
    public function register( string $contract, $hook, int $priority = 0 ) : void
    {
        if( is_string( $hook ) && !config('hook.allow_duplicates', false) && $this->exist( $hook, $contract ) )
        {
            throw new DuplicateExcepiton('Hook' . $hook. ' already exist in contract '.$contract);
        }

        $this->hooks[ $contract ][] = [
            'concrete' => $hook,
            'priority' => $priority
        ];
    }

    /**
     * Checks if hook is already registered.
     *
     * @param string $searchHook
     * @param string|null $contract
     * @return bool
     */
    public function exist( string $searchHook, string $contract = null ) : bool
    {
        if( $contract )
        {
            if( !isset( $this->hooks[ $contract ] ) )
            {
                return false;
            }

            return in_array( $searchHook, array_column( $this->hooks[ $contract ], 'concrete' ) );
        }

        foreach ( $this->hooks as $hook )
        {
            if( in_array( $searchHook, array_column( $hook, 'concrete' ) ) )
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Executes hooks for given contract.
     *
     * @param string $contract
     * @param array $args
     * @return null|string
     * @throws ContractException
     */
    public function hook( string $contract, array $args = [], ?string $method = null ) : ?string
    {
        $output = null;

        foreach ( $this->prepare( $contract, $args ) as $hook )
        {
            if( $hook instanceof Closure )
            {
                $output .= $hook( $args );
                continue;
            }

            $callMethod = ( $method ) ?: 'handle';

            $output .= $hook->$callMethod( $args );
        }

        return $output;
    }

    /**
     * Returns preapred hooks for given contract.
     *
     * @param string $contract
     * @param array $args
     * @return array
     * @throws ContractException
     */
    public function get( string $contract, array $args = [] ) : array
    {
        return $this->prepare( $contract, $args );
    }

    /**
     * Returns all raw hooks or for given contract.
     *
     * @param string $contract
     * @return array
     */
    public function getRaw( ?string $contract = null ) : array
    {
        if( !$contract )
        {
            return $this->hooks;
        }

        if( !isset( $this->hooks[ $contract ] ) )
        {
            return [];
        }

        return $this->hooks[ $contract ];
    }

    /**
     * Sorts hooks by priority.
     *
     * @param array $hooks
     * @return array
     */
    private function sort( array $hooks ) : array
    {
        usort( $hooks, function($a, $b) {
            return $a['priority'] < $b['priority'];
        });

        return $hooks;
    }

    /**
     * Creates or returns instance for given hook class.
     *
     * @param string $concrete
     * @param array $args
     * @return object
     */
    private function resolve( string $concrete, array $args = [] ) : object
    {
        if( isset( $this->instances[ $concrete ] ) )
        {
            return $this->instances[ $concrete ];
        }

        $instance = $this->container->makeWith( $concrete, $args );

        return $instance;
    }

    /**
     * Prepares hooks for given contract.
     *
     * @param string $contract
     * @param array $args
     * @return array
     * @throws ContractException
     */
    private function prepare( string $contract, array $args = [] ) : array
    {
        if( !isset( $this->hooks[ $contract ] ) )
        {
            return [];
        }

        $hooks = $this->sort( $this->hooks[ $contract ] );

        $preparedHooks = [];

        foreach ( $hooks as $hook )
        {
            if( is_string( $hook['concrete'] ) )
            {
                $instance = $this->resolve( $hook['concrete'], $args );
            }
            else
            {
                $instance = $hook['concrete'];
            }

            if( $instance instanceof Hook && $instance->isSingleton() )
            {
                $this->instances[ $contract ] = $instance;
            }

            if( !$instance instanceof Closure && !$instance instanceof Hook )
            {
                throw new ContractException('Object ' . get_class($instance) . ' must implements ' . Hook::class . '.');
            }

            if( interface_exists( $contract ) && !$instance instanceof $contract )
            {
                throw new ContractException('Object ' . get_class($instance) . ' must implements ' . $contract .' contract.');
            }

            $preparedHooks[] = $instance;
        }

        return $preparedHooks;
    }

}
