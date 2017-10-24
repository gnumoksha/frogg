<?php
/**
 * Created by PhpStorm.
 * User: magroski
 * Date: 09/10/17
 * Time: 16:23
 */

namespace Frogg\Services;

use Aws\Sqs\SqsClient as AmazonSqsClient;

class SqsClient
{
    protected $sqsClient;
    protected $queueUrl;

    /**
     * SqsClient constructor.
     *
     * @param array $config Config array expect the following keys:
     *                      AWS_ACCESS_KEY
     *                      AWS_SECRET_KEY
     *                      AWS_SQS_REGION
     *                      AWS_SQS_QUEUE_URL
     */
    public function __construct(array $config)
    {
        $this->sqsClient = new AmazonSqsClient([
            'credentials' => [
                'key'    => $config['AWS_ACCESS_KEY'],
                'secret' => $config['AWS_SECRET_KEY'],
            ],
            'region'      => $config['AWS_SQS_REGION'],
            'version'     => 'latest',
        ]);
        $this->queueUrl  = $config['AWS_SQS_QUEUE_URL'];
    }

    /**
     * Sends a message to AWS SQS service
     *
     * @param string $route   The route to which the message will be forwarded by the daemon. Ex: /worker/process-url
     * @param string $message The content of the message, must be text or a json_encoded array
     */
    public function sendMessage(string $route, string $message)
    {
        $this->sqsClient->sendMessage([
            'MessageBody'       => $message,
            'QueueUrl'          => $this->queueUrl,
            'MessageAttributes' => [
                'beanstalk.sqsd.path'           => [
                    'DataType'    => 'String',
                    'StringValue' => $route,
                ],
                'beanstalk.sqsd.task_name'      => [
                    'DataType'    => 'String',
                    'StringValue' => str_replace('/', '', $route),
                ],
                'beanstalk.sqsd.scheduled_time' => [
                    'DataType'    => 'String',
                    'StringValue' => date('Y-m-d H:i:s T'),
                ],
            ],
        ]);
    }

}