<?php
/**
 * User: Andreas Warnaar
 * Date: 20-3-18
 * Time: 20:38
 */

namespace App\Server;

use Predis\Client;
use React\Socket\ConnectionInterface;
use Voryx\WebSocketMiddleware\WebSocketConnection;

/**
 *
 * Class ConnectionPool
 * @package App\Server
 */
class ConnectionPool
{
    const GLOBAL_CHANNEL = 'global';

    /**
     * @var \SplObjectStorage
     */
    private $connections;


    /**
     * @var WebSocketConnection[]
     */
    private $subscriptions;
    /**
     * @var Client
     */
    private $clientPublisher;

    /**
     * ConnectionPool constructor.
     * @param Client $clientPublisher
     */
    public function __construct(Client $clientPublisher)
    {
        $this->connections = new \SplObjectStorage();
        $this->clientPublisher = $clientPublisher;
    }

    public function __destruct()
    {
        foreach ($this->connections as $connection) {
            $connection->close();
        }
    }

    public function publish($channel, $message)
    {
        $this->clientPublisher->publish($channel, $message);
    }

    public function subscribe($connection, $channel)
    {
        $this->subscriptions[$channel][$this->connections->getHash($connection)] = $connection;
    }

    /**
     * @param $token
     * @return null|ConnectionInterface
     */
    protected function getConnectionByToken($token)
    {
        /** @var ConnectionInterface $connection */
        foreach ($this->connections as $connection) {
            $data = $this->connections->offsetGet($connection);
            $connectionToken = $data['token'] ?? '';
            if ($connectionToken == $token) return $connection;
        }

        return null;
    }

    /**
     * @return \SplObjectStorage
     */
    public function getConnection()
    {
        return $this->connections;
    }

    public function handlePubSub($client)
    {
        $client->subscribe(self::GLOBAL_CHANNEL)->then(function () {

            // TODO log connection / subscription
        });

        // Loop to all subscription for the given channel
        // TODO Allow wildcard search for subscriptions. Like book:* to subscribe to book:created, book:updated and book:deleted
        $client->on('message', function ($channel, $message) {
            foreach ($this->subscriptions[$channel] ?? [] as $hash => $connection) {
                if (!$connection) {
                    unset($this->subscriptions[$channel][$hash]);
                }
                $connection->send($message);
            }

            // TODO Log this 'Message on ' . $channel . ': ' . $message . PHP_EOL;
        });

    }

}