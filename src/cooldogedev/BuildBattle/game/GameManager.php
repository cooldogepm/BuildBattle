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

namespace cooldogedev\BuildBattle\game;

use cooldogedev\BuildBattle\BuildBattle;
use cooldogedev\BuildBattle\game\data\GameData;
use cooldogedev\BuildBattle\session\Session;
use cooldogedev\BuildBattle\utility\message\KnownMessages;
use cooldogedev\BuildBattle\utility\message\LanguageManager;

final class GameManager
{
    protected BuildBattle $plugin;
    protected int $generatedGames;
    /**
     * @var Game[]
     */
    protected array $games;
    protected array $maps;

    public function __construct(BuildBattle $plugin)
    {
        $this->plugin = $plugin;
        $this->generatedGames = 0;
        $this->games = [];
        $this->maps = [];
        $this->loadMaps();
    }

    protected function loadMaps(): void
    {
        foreach (scandir($this->plugin->getDataFolder() . "maps" . DIRECTORY_SEPARATOR) as $map) {
            if ($map === "." || $map === ".." || is_file($map)) {
                continue;
            }
            $data = json_decode(file_get_contents($this->plugin->getDataFolder() . "maps" . DIRECTORY_SEPARATOR . $map . DIRECTORY_SEPARATOR . "data.json"), true);
            $this->addMap($data["name"], $data);
        }
    }

    public function addMap(string $map, array $data, bool $overwrite = false): bool
    {
        if (!$overwrite && $this->isMapExists($map)) {
            return false;
        }

        if ($overwrite) {
            file_put_contents($this->getPlugin()->getDataFolder() . "maps" . DIRECTORY_SEPARATOR . $map . DIRECTORY_SEPARATOR . "data.json", json_encode($data));
        }

        $this->maps[$map] = $data;
        return true;
    }

    public function isMapExists(string $map): bool
    {
        return isset($this->maps[$map]);
    }

    public function getPlugin(): BuildBattle
    {
        return $this->plugin;
    }

    public function reloadMap(string $map): bool
    {
        if (!isset($this->maps[$map])) {
            return false;
        }
        $this->removeMap($map);
        $data = json_decode(file_get_contents($this->plugin->getDataFolder() . "maps" . DIRECTORY_SEPARATOR . $map . DIRECTORY_SEPARATOR . "data.json"), true);
        $this->addMap($map, $data);
        return true;
    }

    public function removeMap(string $map): bool
    {
        if (!$this->isMapExists($map)) {
            return false;
        }
        unset($this->maps[$map]);
        return true;
    }

    /**
     * @param Session[] $queues
     */
    public function queueToGame(array $queues, ?string $map = null, bool $create = false, bool $kick = false): bool
    {
        $availableGames = [];

        foreach ($this->getGames() as $index => $game) {
            if (
                $map !== null && $game->getData()->getName() !== $map ||
                !$game->isFree()
            ) {
                continue;
            }
            $availableGames[$index] = $game;
        }

        if (count($availableGames) >= 1 && count($queues) >= 1) {
            $game = $this->getGames()[array_rand($availableGames)];
            foreach ($queues as $queue) {
                $game->getPlayerManager()->addToGame($queue);
            }
            return true;
        }

        if (!$create || count($this->getMaps()) <= 0 || $map && !$this->isMapExists($map)) {
            foreach ($queues as $queue) {
                if ($kick) {
                    $queue->getPlayer()->kick(LanguageManager::getMessage(KnownMessages::TOPIC_QUEUE_ERROR, KnownMessages::QUEUE_ERROR_KICK));
                } else {
                    $queue->getPlayer()->sendMessage(LanguageManager::getMessage(KnownMessages::TOPIC_QUEUE_ERROR, KnownMessages::QUEUE_ERROR_MESSAGE));
                }
            }
            return false;
        }

        $map ??= array_rand($this->getMaps());
        $mapData = $this->getMap($map);

        $id = $this->getGeneratedGames();
        $name = $mapData[GameData::GAME_DATA_NAME];
        $countdown = (int)$mapData[GameData::GAME_DATA_COUNTDOWN];
        $minPlayers = (int)$mapData[GameData::GAME_DATA_MIN_PLAYERS];
        $maxPlayers = (int)$mapData[GameData::GAME_DATA_MAX_PLAYERS];
        $buildDuration = (int)$mapData[GameData::GAME_DATA_BUILD_DURATION];
        $buildHeight = (int)$mapData[GameData::BUILD_HEIGHT];
        $voteDuration = (int)$mapData[GameData::GAME_DATA_VOTE_DURATION];
        $endDuration = (int)$mapData[GameData::GAME_DATA_END_DURATION];
        $spawns = (array)$mapData[GameData::GAME_DATA_SPAWNS];

        $game = new Game($this->plugin, new GameData($id, $name, $countdown, $minPlayers, $maxPlayers, $buildDuration, $buildHeight, $voteDuration, $endDuration, $spawns));

        $this->games[$id] = $game;
        $this->generatedGames++;

        foreach ($queues as $queue) {
            $game->getPlayerManager()->addToGame($queue);
        }

        return true;
    }

    /**
     * @return Game[]
     */
    public function getGames(): array
    {
        return $this->games;
    }

    public function getMaps(): array
    {
        return $this->maps;
    }

    public function getMap(string $name): ?array
    {
        return $this->maps[$name] ?? null;
    }

    public function getGeneratedGames(): int
    {
        return $this->generatedGames;
    }

    public function removeGame(int $id): bool
    {
        if (!$this->isGameExists($id)) {
            return false;
        }
        unset($this->games[$id]);
        return true;
    }

    public function isGameExists(int $id): bool
    {
        return isset($this->games[$id]);
    }

    public function getGame(int $id): ?Game
    {
        return $this->games[$id] ?? null;
    }

    public function handleTick(): void
    {
        foreach ($this->getGames() as $game) {
            $game->tickGame();
        }
    }
}
