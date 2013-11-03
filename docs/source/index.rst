.. Teeparty documentation master file, created by
   sphinx-quickstart on Sat Nov  2 17:29:15 2013.
   You can adapt this file completely to your liking, but it should at least
   contain the root `toctree` directive.

Welcome to Teeparty's documentation!
==================================

Teeparty is a special implementation of the `Pipes and Filter`_ pattern with
multiple in- and outputs. 

It consists with 3 components:

1. A process supervisor and service bus implementation written in `node.js`_ and 
2. A PHP library for defining and triggering computations.
3. `redis`_ for communication.

Contents:

.. toctree::
    :maxdepth: 2
    :numbered:

    install
    usage

    
       

Indices and tables
==================

* :ref:`genindex`
* :ref:`modindex`
* :ref:`search`

.. _Pipes and Filter: http://www.eaipatterns.com/PipesAndFilters.html
.. _node.js: http://nodejs.org/
.. _redis: http://redis.io/
