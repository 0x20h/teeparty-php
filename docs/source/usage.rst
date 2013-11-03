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

In order to execute a Filter, start a worker instance:

.. code-block:: sh
   
   vendor/bin/teeparty worker:start -times 10 default

Triggering a Filter
===================

.. code-block:: php
   :linenos:
   
    <?php

    $args =  [
        'to' => 'foo@example.org',
        'subject' => 'test',
        'message' => 'test message',
    ];

    $token = Teeparty::pipe(['App\Mail\Mailer'], $args);

    // ...
    // do other work

    // optional: 
    // block for result
    echo Teeparty::join($token); // echoes "Mail sent!"

