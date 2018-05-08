<?php
/**
 * Created by PhpStorm.
 * User: Dizy
 * Date: 2018/5/8
 * Time: 18:49
 */

namespace Hhxsv5\LaravelS\Swoole;


class Socket
{
    protected $swoolePort;
    public function setSwoolePort($port){
        $this->swoolePort = $port;
    }
    public function onConnect($server, $fd, $reactorId){}
    public function onClose($server, $fd, $reactorId){}
    public function onReceive($server, $fd, $reactorId, $data){}
    public function onPacket($server, $data,  $clientInfo){}
}