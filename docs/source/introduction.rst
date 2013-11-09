.. _intro:

************
Introduction
************

Teeparty is a framework for asychronous processing of :ref:`intro_stream`\s. 
`Stream`\s consist of (typed) `Message`\s that are processed by `Filter`\s.
`Filter` can emit or receive multiple `Stream`\s.

.. figure:: resources/architecture.png

    Abstract view of the *teeparty* processing model. 

    Input data is piped to a `Filter`. The output of a `Filter` is piped
    to other `Filter`\s. A Filter can also `join` several outputs from preceding
    `Filter`\s.


.. _intro_stream:

Stream
======

InputStream
^^^^^^^^^^^

OutputStream
^^^^^^^^^^^^

.. _intro_message:

Message
========


.. _intro_filter:

Filter
======



