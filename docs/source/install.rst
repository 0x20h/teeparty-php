************
Installation
************

Client library
==============

Teeparty is the PHP client library. It lets you define `Filter`\s and trigger
asynchronous `Job`\s. It can be installed via `composer`_.
In your PHP application, add teeparty to your `composer.json`::

    {
        "require": {
            "0x20h/teeparty": "*"
        }
    }


Supervisor
==========

The supervisor is in charge running and supervising worker scripts that fetch
pending `Job`\s and run the `Filter` implementations. In order to run multiple 
parallel workers you need to install the supervisor:: 

    npm install teeparty-supervisor

Copy the config.js.example and adapt the settings to your needs::
    
    {
        worker: "path/to/your/php/project/vendor/bin/teeparty",
        worker_args: ["-times", "10"]
    }


.. _composer: http://getcomposer.org
