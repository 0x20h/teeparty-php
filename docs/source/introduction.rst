.. _intro:

************
Introduction
************

Teeparty [ti:pɑ:rt̬i] is a (asychronous) pipeline processing framework for PHP.

:ref:`intro_pipelines` consist of a set of :ref:`intro_filters` that process
:ref:`intro_messages` that are passed around through :ref:`intro_channels`.
 
.. figure:: resources/architecture.png

    Abstract view of the *teeparty* processing model. 

    :ref:`intro_messages` are passed through :ref:`intro_channels` to
    different :ref:`intro_filters`. 

   
.. code-block:: php

    <?php
    use Teeparty\Filter;
    use Teeparty\Channel;
    use Teeparty\Pipeline;
    use Teeparty\Client\Redis;
    use Teeparty\Schema\V1\NumberSchema;

    $pipeline = new Pipeline([
        'A' => new FilterA,
        'C' => new FilterC,
    ], new Redis);
    
    $pipeline
        ->connect(Pipeline::STDIN, ['A'])
        ->connect('A', ['C'], new Channel('foo', ['workers' => 1]))
        ->connect('C', Pipeline::STDOUT);
    
    foreach($work as $item) {
        // 'A' accepts NumberSchema
        $message = new Message(new NumberSchema(), $item);
        $pipeline->write($message);
    }

    while ($result = $pipeline->read(Pipeline::STDOUT) !== null) {
        echo $result;
    }

.. _intro_messages:

Messages
========

Messages wrap data items in a way that it can be transported from one Filter to
another. Messages may be typed, so that receiving Filters can understand the
meaning of a certain message.

Schema
^^^^^^


.. _intro_channels:

Channels
========

Channels transport Messages from one Filter to the next. There are different
types of channels.

.. _intro_filters:

Filters
=======

.. _intro_pipelines:

Pipelines
=========

Connect Filters to parallelize computations.
