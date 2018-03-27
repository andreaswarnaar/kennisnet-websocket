<?php
/**
 * User: Andreas Warnaar
 * Date: 20-3-18
 * Time: 20:58
 */

namespace App\Server;


use React\EventLoop\LoopInterface;
use React\Socket\ConnectionInterface;

class Connection extends \React\Socket\Connection
{
    /**
     * @var ConnectionInterface
     */
    private $connection;

    public function __construct($resource, LoopInterface $loop)
    {

        parent::__construct($resource, $loop);
    }

    public function send($data)
    {
        $this->connection->write($data);
    }
}