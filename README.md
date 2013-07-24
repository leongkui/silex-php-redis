Silex PHP Redis Extension Provider
================

Installation
------------

Create (or add to your) composer.json in your projects root-directory::

    {
        "require": {
            "leongkui/silex-php-redis": "*"
        }
    }

and run::

    curl -s http://getcomposer.org/installer | php
    php composer.phar install

This is just a silex provider module for phpredis extension, you will need to setup Redis (https://github.com/antirez/redis) and phpredis extension (https://github.com/nicolasff/phpredis).


Example
----------------

Check out simple example under "example" directory.

License
-------

'silex-php-redis' is licensed under the MIT license.
