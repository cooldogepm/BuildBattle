<?php

declare(strict_types=1);

namespace cooldogedev\BuildBattle\async;

use cooldogedev\BuildBattle\BuildBattle;
use pocketmine\scheduler\AsyncPool;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;

final class BackgroundTaskPool extends AsyncPool
{
    use SingletonTrait;

    public function __construct()
    {
        parent::__construct(BuildBattle::getInstance()->getConfig()->get("pool")["workers-limit"], BuildBattle::getInstance()->getConfig()->get("pool")["memory-limit"], Server::getInstance()->getLoader(), Server::getInstance()->getLogger(), Server::getInstance()->getTickSleeper());
    }
}
