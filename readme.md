
 * There is no authentication in the system as of now. The service is not user facing. Since only our own clients will be using the service we can configure it to respond only to a certain set of ips. In the code as well we could setup a list of trusted ips. This is a basic setup.
 * To add further authentication we can have a HMAC setup to validate each request.
 *  For error logging we are using Monolog. It's been configured to use Kibana and syslog for error handling. Depending on the level of error/warning the error can be logged to appropriate handler
 * Response Handler and Error Handler has been customized as per requirement
 * Whenever an order placed request is processed an event for notifying the user via sms and email is pushed to the queue - beanstalk. This queue is processed as per load and supervidord(queue-management-system) setup depending on the load
 * When the queue is processed the sms and mail service is called and the response is logged.
 * Ideally there should be a proper client which can be packaged as a library and be reused across any project. The client can be hosted on any private service like sentry and be easliy loaded via composer. This allows for reuseability of the client.
