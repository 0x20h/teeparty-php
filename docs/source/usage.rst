*****
Usage
*****

Defining a Filter
=================

.. code-block:: php
   :linenos:

    <?php
    
    namespace App\Mail;
     
    use TeeParty\Filter;
    use TeeParty\Pipe\Context;
    use TeeParty\Progress\Progress;
      
    class Mailer implements Filter {
        
        public function filter($context, Progress $progress) {
            $rs = mail($context['to'], $context['subject'], $context['message']);
            // optional:
            if ($rs) {
                 return "Mail sent!";
            }
        }
    }

Starting a Worker
=================

In order to execute a filter, start a worker instance:

.. code-block:: sh
   
   vendor/bin/teeparty worker:start -times 10 default

Triggering a Filter
===================

.. code-block:: php
   :linenos:
   
    <?php

    use Teeparty\Teeparty;
    use Teeparty\Client\PhpRedis;

    $tee = new Teeparty(new PhpRedis, []);
    $context =  new Context([
        'to' => 'foo@localhost',
        'subject' => 'test',
        'message' => 'test message',
    ]);

    $token = $tee->pipe(new App\Mail\Mailer, $context, ['num_workers' => 2]);

    // ...
    // do other work or return token

    // optional: 
    // block for result
    echo $tee->join($token); // echoes "Mail sent!"

