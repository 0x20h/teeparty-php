.. _intro:

************
Introduction
************

Teeparty [ti:pɑ:rt̬i] is a (asychronous) pipeline processing framework for PHP.

:ref:`intro_pipelines` consist of a set of :ref:`intro_filters` that process
:ref:`intro_messages`. :ref:`intro_filters` are connected through 
:ref:`intro_channels`.
 
.. figure:: resources/architecture.png

    Abstract view of the *teeparty* processing model. The corresponding
    configuration in PHP:

    .. code-block:: php
        :linenos:

        <?php
        use Teeparty\Filter;
        use Teeparty\Channel;
        use Teeparty\Pipeline;
        use Teeparty\Client\Memory;
        use Teeparty\Schema\V1\NumberSchema;

        $pipeline = new Pipeline(
            [
                'A' => new FilterA,
                'C' => new FilterC,
            ], 
            new Memory
        );
        
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

Every `Message` is bound to a Schema. A schema describes the data structure that
is enveloped.

.. _intro_channels:

Channels
========

Channels transport Messages from one Filter to the next. There are different
types of channels.

*   HostChannel
    
    Only workers on a specified host receive messages.
    
* WorkerChannel
* NamedChannel
* ConditionalChannel

.. _intro_filters:

Filters
=======

.. _intro_pipelines:

Pipelines
=========

Connect Filters to parallelize computations.

Simple asyncronous execution
^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Delivering heavy work to a background process is a common task. For this special
case there is a factory method to create a pipeline that delivers data to a
single Filter in a fire and forget fashion:

.. code-block:: php
    :linenos:

    <?php
    
    use Teeparty\Pipeline;
    use Teeparty\Filter\Mailer;
    
    Pipeline::async(
        new Mailer, 
        [
            'subject' => 'foo',
            'to' => ['foo@example.org'],
            'body' => 'Test message'
        ]
    );


