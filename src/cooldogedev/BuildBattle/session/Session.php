<?php

/**
 * Copyright (c) 2022 cooldogedev
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * @auto-license
 */

declare(strict_types=1);

namespace cooldogedev\BuildBattle\session;

use cooldogedev\BuildBattle\BuildBattle;
use cooldogedev\BuildBattle\constant\DatabaseConstants;
use cooldogedev\BuildBattle\game\Game;
use cooldogedev\BuildBattle\query\QueryManager;
use cooldogedev\BuildBattle\session\data\SessionCache;
use cooldogedev\BuildBattle\session\data\SessionData;
use cooldogedev\BuildBattle\session\handler\SetupHandler;
use cooldogedev\BuildBattle\session\scoreboard\Scoreboard;
use cooldogedev\libSQL\context\ClosureContext;
use pocketmine\player\Player;

final class Session
{
    public const PLAYER_STATE_DEFAULT = -1;
    public const PLAYER_STATE_BUILDING = 0;
    public const PLAYER_STATE_WAITING_OWN_BUILD = 1;
    public const PLAYER_STATE_WAITING_RESULTS = 2;

    use SessionCache, SessionData;

    protected BuildBattle $plugin;
    protected Player $player;
    protected Scoreboard $scoreboard;

    protected bool $loading = true;
    protected ?SetupHandler $setupHandler = null;

    public function __construct(BuildBattle $plugin, Player $player)
    {
        $this->plugin = $plugin;
        $this->player = $player;
        $this->scoreboard = new Scoreboard($this->player);

        $this->plugin->getConnectionPool()->submit(QueryManager::getPlayerRetrieveQuery($player->getXuid()), DatabaseConstants::TABLE_BUILDBATTLE_PLAYERS, ClosureContext::create(
            function (?array $result): void {
                if ($result !== null) {
                    $this->wins = $result["wins"];
                    $this->winStreak = $result["win_streak"];
                    $this->losses = $result["losses"];
                }

                $this->unregistered = $result === null;
                $this->loading = false;
            }
        ));
    }

    public function clearAll(): void
    {
        $this->player->getInventory()->clearAll();
        $this->player->getArmorInventory()->clearAll();
        $this->player->getCursorInventory()->clearAll();
        $this->player->getOffHandInventory()->clearAll();
        $this->player->getEffects()->clear();
    }

    public function getSetupHandler(): ?SetupHandler
    {
        return $this->setupHandler;
    }

    public function setSetupHandler(?SetupHandler $setupHandler): void
    {
        $this->setupHandler = $setupHandler;
    }

    public function getPlugin(): BuildBattle
    {
        return $this->plugin;
    }

    public function isLoading(): bool
    {
        return $this->loading;
    }

    public function getGame(): ?Game
    {
        return $this->game;
    }

    public function save(): void
    {
        if (!$this->unregistered && $this->updated) {
            $this->plugin->getConnectionPool()->submit(QueryManager::getPlayerUpdateQuery($this->player->getXuid(), $this->toArray()), DatabaseConstants::TABLE_BUILDBATTLE_PLAYERS);
        }

        if ($this->isUnregistered()) {
            $this->plugin->getConnectionPool()->submit(QueryManager::getPlayerCreateQuery($this->player->getXuid(), $this->player->getName(), $this->toArray()), DatabaseConstants::TABLE_BUILDBATTLE_PLAYERS);
        }
    }

    public function getScoreboard(): Scoreboard
    {
        return $this->scoreboard;
    }

    public function isWithinPlot(): bool
    {
        if ($this->plot === null) {
            return false;
        }

        return $this->plot->isWithin($this->getPlayer()->getPosition());
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }
}
