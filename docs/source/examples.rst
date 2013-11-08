*************
Code Examples
*************

Parallel Sorting
================

.. code-block:: php
    :linenos:

    <?php
    namespace App\Filter;
     
    class SortFilter implements Filter, Reducer {

        public function filter(Teeparty $tee, Context $context) {
            return [
                'data' => sort($context['data'], $context['direction']),
                'direction' => $context['direction']
            ];
        }

        public function reduce(Teeparty $tee, $context) {
            // merge sorted lists $contextA & $contextB
            $i = 0, $j = 0, $context = [];

            for ($i = 0; $i < count($contextA['data']); $i++) {
            }
        }
    }


.. code-block:: php
    :linenos:

    <?php

    $tee = new Teeparty(new Teeparty\Client\PhpRedis, []);
    $x = shuffle(range(1,10000));
    $token = $tee->pipe('App\Filter\SortFilter', $x, ['chunks' => 10]);

    echo $tee->join($token, 'App\Filter\SortFilter');

