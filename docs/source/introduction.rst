************
Introduction
************

.. figure:: resources/architecture.png

    Abstract view of the *teeparty* processing model. 

    Input data is piped to a `Filter`. The output of a `Filter` is piped
    to other `Filter`\s. A Filter can also `join` several outputs from preceding
    `Filter`\s.
