# PhpMultithread

This minimalistic package allows asynchronous multithreading using coroutines and the Symfony/Process component.
Coroutine structure based heavily on https://nikic.github.io/2012/12/22/Cooperative-multitasking-using-coroutines-in-PHP.html
Basically, for use when you don't have access to reactPhp or other async frameworks, or message queues like Beanstalk or Redis
and still want to run non-blocking processes on multiple threads. Supports running php closure functions by passing
a closure obj to AsyncTask->create();

See TaskRunnerExample.php for some simple examples.
