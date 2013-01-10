### Welcome to the *Shaw 0.3.0* !

Shaw library is a set of usefull modules I developed while working on Zend projects.

### Features

- **Logging**. Shaw_Log implements static calling, additionnal writers for email and color shell, sprintf-like polymorphic calling, autotraces exceptions.
- **Doctrine 1.2 support**. Additionnal templates, shell commands for generating database models, profiler.
- **Cron Tasking**. Shaw_Task helps implementing batch and cron jobs inside the business structure of the app.
- **Twitter Bootstrap view helpers**
- **Email template system**
- **Bottom Debug Trace**
- **Misc**. Various DateTime and usefull func besides.

### SYSTEM REQUIREMENTS

Shaw library requires PHP 5.2.0 or later.

### INSTALLATION

Make sure Shaw is inside your include path. Once done, include the library by referencing it in the Zend Autoloader like this :

    // Inside your Bootstrap.php
    $autoloader = Zend_Loader_Autoloader::getInstance();
    $autoloader->registerNamespace('Shaw_');

Or inside the config.ini :

    autoloadernamespaces[] = "Shaw_"

Like the reste of the Zend Framework, you can however just include the pieces you need by require_once, it should work just fine.

Some application resources are overriden inside Shaw, if you want to benefit from them add also :

    pluginPaths.Shaw_Application_Resource = "Shaw/Application/Resource"

It will automagically add the following ressources :
   
- Doctrine
- Log


### LICENSE

The files in this archive/project are released under the MIT License.
You can find a copy of this license inside.

### DOCUMENTATION

### ACKNOWLEDGEMENTS

Please visit me sometime soon at http://h.alfti.me