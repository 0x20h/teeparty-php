.. Teeparty documentation master file, created by
   sphinx-quickstart on Sat Nov  2 17:29:15 2013.

Welcome to Teeparty's documentation!
====================================

Teeparty is a special implementation of the `Pipes and Filter`_ pattern with
multiple in- and outputs (T-Pipes). In this model you pipe data to `Filter`\s
that mangle input and produce output. You can connect several `Filter`\s via
`Pipe`\s in order to allow complex processing.
 
The system is built upon 3 components:

1. A process supervisor and service bus implementation written in `node.js`_
2. (For now) a PHP library for defining and triggering `Task`\s.
3. `redis`_ as a backend database for temporary data storage and messaging.

Contents:

.. toctree::
    :maxdepth: 2
    :numbered:

    introduction
    install
    usage
    examples

    
       

Indices and tables
==================

* :ref:`genindex`
* :ref:`modindex`
* :ref:`search`

.. _Pipes and Filter: http://www.eaipatterns.com/PipesAndFilters.html
.. _node.js: http://nodejs.org/
.. _redis: http://redis.io/
