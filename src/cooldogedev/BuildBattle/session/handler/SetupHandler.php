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

namespace cooldogedev\BuildBattle\session\handler;

use cooldogedev\BuildBattle\BuildBattle;
use cooldogedev\BuildBattle\session\Session;
use cooldogedev\BuildBattle\utility\Utils;
use pocketmine\math\Vector3;
use pocketmine\utils\TextFormat;
use pocketmine\world\World;

final class SetupHandler
{
    public const SETUP_QUIT_MESSAGE = ".quit";

    protected const STEP_MIN = 0;
    protected const STEP_MAX = 1;

    protected int $step = SetupHandler::STEP_MIN;
    protected int $current = 0;
    protected array $spawns = [];

    public function __construct(protected BuildBattle $plugin, protected ?World $world, protected string $map, protected int $maxSpawns)
    {
    }

    public function handleMessage(Session $session, string $message): bool
    {
        match ($message) {
            SetupHandler::SETUP_QUIT_MESSAGE => $this->onQuit($session),
            default => null
        };

        return $message === SetupHandler::SETUP_QUIT_MESSAGE;
    }

    protected function onQuit(Session $session): void
    {
        $this->plugin->getServer()->getWorldManager()->unloadWorld($this->world, true);

        $session->getPlugin()->getGameManager()->removeMap($this->map);
        $session->getPlayer()->sendMessage(TextFormat::RED . "Setup was cancelled.");
        $session->getPlayer()->teleport($session->getPlugin()->getServer()->getWorldManager()->getDefaultWorld()->getSafeSpawn());
        $session->getPlayer()->setGamemode($session->getPlugin()->getServer()->getGamemode());
        $session->setSetupHandler(null);

        $this->world = null;
        $this->map = "";
        $this->maxSpawns = 0;
        $this->spawns = [];
        $this->step = SetupHandler::STEP_MIN;
        $this->current = 0;
    }

    public function handleBlockBreak(Session $session, Vector3 $vec): void
    {
        $gameManager = $session->getPlugin()->getGameManager();
        $mapData = $session->getPlugin()->getGameManager()->getMap($this->map);

        if ($mapData === null) {
            $session->getPlayer()->sendMessage(TextFormat::RED . "The map you created no longer exists.");
            $session->setSetupHandler(null);
            return;
        }

        if (!isset($this->spawns[$this->current])) {
            $this->spawns[$this->current] = [];
        }

        $this->spawns[$this->current][] = $vec;

        if ($this->step === SetupHandler::STEP_MAX) {
            $session->getPlayer()->sendMessage(TextFormat::GREEN . "Successfully added spawn point (" . TextFormat::WHITE . count($this->spawns) . "/" . $this->maxSpawns . TextFormat::GREEN . ")");
            $this->step = SetupHandler::STEP_MIN;
            $this->maxSpawns - 1 > $this->current && $this->current++;
        } else {
            $session->getPlayer()->sendMessage(TextFormat::GREEN . "Successfully set minimum pos for #" . TextFormat::WHITE . $this->current + 1);
            $this->step = SetupHandler::STEP_MAX;
        }

        if ($this->current + 1 === $this->maxSpawns && isset($this->spawns[$this->current]) && count($this->spawns[$this->current]) === 2) {
            $mapData["spawns"] = array_map(fn(array $vectors) => [Utils::stringifyVec3($vectors[0]), Utils::stringifyVec3($vectors[1])], $this->spawns);

            $gameManager->addMap($this->map, $mapData, true);

            $this->onQuit($session);
        }
    }
}
