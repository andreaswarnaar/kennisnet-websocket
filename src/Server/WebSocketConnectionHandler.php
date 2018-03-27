<?php
/**
 * User: Andreas Warnaar
 * Date: 20-3-18
 * Time: 20:32
 */

namespace App\Server;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ratchet\RFC6455\Messaging\Message;
use React\EventLoop\LoopInterface;
use React\Stream\ThroughStream;
use Voryx\WebSocketMiddleware\WebSocketConnection;

/**
 * Handles the websocket connections
 * Class WebSocketConnectionHandler
 * @package App\Server
 */
class WebSocketConnectionHandler
{
    /**
     * @var ThroughStream
     */
    private $broadcastStream;
    /**
     * @var ConnectionPool
     */
    private $connectionsPool;

    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * WebSocketConnectionHandler constructor.
     * @param ConnectionPool $connectionsPool
     * @param LoopInterface $loop
     */
    public function __construct(ConnectionPool $connectionsPool, LoopInterface $loop)
    {
        $this->connectionsPool = $connectionsPool;
        $this->loop = $loop;
    }

    /**
     * @param WebSocketConnection $connection
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     */
    public function __invoke(WebSocketConnection $connection, ServerRequestInterface $request, ResponseInterface $response)
    {
        $this->initializeConnection($connection, $request, $response);
    }

    /**
     * @param WebSocketConnection $connection
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     */
    public function initializeConnection(WebSocketConnection $connection, ServerRequestInterface $request, ResponseInterface $response)
    {
        echo print_r($request->getHeaders(),1);

        // Wait until everything is setup and ready.
        $this->loop->addTimer(0, function () use ($connection) {
            $connection->send('Welcome.');
            $this->connectionsPool->publish(ConnectionPool::GLOBAL_CHANNEL, 'new user connected');
        });
        // Sent the massage to the connectionpool for the subscribed & active connections
        $connection->on('message', function (Message $message) use ($connection) {

            // TODO map the websocket message to domain Payload.
            try {
                // payload should be like {type: 'subscribe', channel: 'main' }
                $payload = json_decode($message->getPayload());
                if ($payload->type == 'subscribe') {
                    $this->connectionsPool->subscribe($connection, $payload->channel);
                    $connection->send('Your now subscribed to '. $payload->channel);
                }else {

                    // payload should be like {type: 'message', channel: 'main', message: 'hi' }
                    $this->connectionsPool->publish($payload->channel, $payload->message);
                }
            }catch (\Exception $exception) {
                $connection->send($message->getPayload());
                $connection->send($exception->getMessage());
            }
        });

        $this->setConnectionData($connection, []);

        $connection->on('close', function () use ($connection) {
            $this->connectionsPool->getConnection()->offsetUnset($connection);
        });
    }

    /**
     * @param WebSocketConnection $connection
     * @param $data
     */
    protected function setConnectionData(WebSocketConnection $connection, $data)
    {
        $this->connectionsPool->getConnection()->offsetSet($connection, $data);
    }

    /**
     * @param WebSocketConnection $connection
     * @return mixed
     */
    protected function getConnectionData(WebSocketConnection $connection)
    {
        return $this->connectionsPool->getConnection()->offsetGet($connection);
    }

}