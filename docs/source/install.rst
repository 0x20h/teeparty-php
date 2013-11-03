************
Installation
************

Client library
==============

Teeparty can be installed via `composer`_.
In your PHP application, add teeparty to your `composer.json`::

    {
        "require": {
            "0x20h/teeparty": "*"
        }
    }


Supervisor
==========

In order to run multiple parallel workers you need to install the supervisor as
well::

    npm install teeparty-supervisor

Copy the config.js.example and adapt the settings to your needs::
    
    {
        worker: "path/to/your/php/project/vendor/bin/teeparty",
        worker_args: ["-times", "10"]
    }


.. _composer: http://getcomposer.org
