
# Installation  
  
Requirements:  
  
php >= 7.2  
laravel >= 5.7  
  
Require this package with composer.  
```bash  
composer require pikart/laravel-hooks  
```  
  
This package will automatically register service provider and alias using laravel auto discovery functionality.  
  
Copy the package config to your local config with the publish command:

```bash  
php artisan vendor:publish --provider="Pikart\LaravelHooks\HookServiceProvider"
```  

# Usage  
  
**Registering hooks**  
  
Register method takes three parameters:  
  
1. contract (**string**) required  
	custom string or existing interface name
	 
2. hook (**string**|**Closure**|**Pikart\LaravelHook\Contracts\Hook**) required  
      
    If contract is an existing interface, hook must be existing class name or class instance.  
    The registered class must implement two interfaces. The interface to which it relates and   
    the hooks interface (Pikart\LaravelHook\Contracts\Hook).  
  
    If hook is an existing class name, class will be resolved using laravel service container,   
    so its may use auto instance injection in constructor.  
  
3. priority (**int**) default 0  
    Ordering execution of registered hooks, bigger will be execute first.  
  
Register closure   
  
```php  
HookManager::register('hook_name', function( array $args ) {   
    return 'Hello world';  
}, 10);  
```  
  
Register class   
  
```php  
HookManager::register('hook_name', SomeHookClass::class, 10);  
```  
  
Register instance  
  
```php  
HookManager::register('hook_name', new SomeHookClass::class, 10);  
```  
  
Register interface to implementation  
  
```php  
HookManager::register(SomeHookInterface::class, SomeHookClass::class, 10);  
```  
  
**Executing**  
  
Hooks are executing by hook method. Hook method takes two parameters:  
  
1. Hook name (**string**) required   
   custom string or existing interface name  
2. Arguments (**array**)   
3. Method (**string**)  

Arguments are passed for execution and in the case of an existing class name to   
create its instance using the laravel service container  
  
Execute hook by custom name  
  
```php  
$output = HookManager::hook('hook_name');  
```  
  
Execute hook by custom name with arguments  
  
```php  
$user = User::find(1);  
  
$output = HookManager::hook('hook_name', [  
 'user' => $user]);  
```  
  
Execute hook by interface  
  
```php  
$output = HookManager::hook(SomeHookInterface::class);  
```  
  
Execute hook by interface name with arguments  
  
```php  
$user = User::find(1);  
  
$output = HookManager::hook(SomeHookInterface::class, [  
 'user' => $user]);  
```  

Execute hook by interface name with arguments and custom method 
  
```php  
$user = User::find(1);  
  
$output = HookManager::hook(SomeHookInterface::class, [  
 'user' => $user], 'someMethod');  
``` 
  
**Gets hooks to be executed**  
  
Get method takes three parameters:  
  
1. Hook name (**string**)  required 
	custom string or existing interface name 
2. Argumesnts (**array**)   
  
The get method works the same as the hook method, however, the hooks are not executed, array is returned.

Get prepared hooks  
  
```php  
$hooks = HookManager::get(SomeHookInterface::class);  
```  

Get prepared hooks with arguments
  
```php  
$hooks = HookManager::get(SomeHookInterface::class, [
    'user' => $user
]);  
```  
  
**Gets raw hooks**  
  
```php  
$hooks = HookManager::getRaw(SomeHookInterface::class);  
```

**Tests** 

```bash  
 vendor/bin/phpunit --testdox
```  