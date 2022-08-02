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

namespace cooldogedev\BuildBattle\command\subcommand;

use cooldogedev\BuildBattle\async\BackgroundTaskPool;
use cooldogedev\BuildBattle\async\directory\AsyncDirectoryClone;
use cooldogedev\BuildBattle\game\data\GameData;
use cooldogedev\BuildBattle\permission\PermissionsList;
use cooldogedev\BuildBattle\session\handler\SetupHandler;
use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\constraint\InGameRequiredConstraint;
use pocketmine\command\CommandSender;
use pocketmine\player\GameMode;
use pocketmine\utils\TextFormat;
use pocketmine\world\World;

final class CreateSubCommand extends BaseSubCommand
{
    public function __construct()
    {
        parent::__construct("create", "Create a new arena", ["c"]);
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $name = $args[GameData::GAME_DATA_NAME];
        $world = $args[GameData::GAME_DATA_WORLD];
        $lobby = $args[GameData::GAME_DATA_LOBBY];
        $countdown = $args[GameData::GAME_DATA_COUNTDOWN];
        $maxPlayers = $args[GameData::GAME_DATA_MAX_PLAYERS];
        $minPlayers = $args[GameData::GAME_DATA_MIN_PLAYERS];
        $buildDuration = $args[GameData::GAME_DATA_BUILD_DURATION];
        $buildHeight = $args[GameData::BUILD_HEIGHT];
        $voteDuration = $args[GameData::GAME_DATA_VOTE_DURATION];
        $endDuration = $args[GameData::GAME_DATA_END_DURATION];

        if ($this->getOwningPlugin()->getGameManager()->isMapExists($name)) {
            $sender->sendMessage(TextFormat::RED . "There's already existing map with the name " . TextFormat::WHITE . $name);
            return;
        }

        if (!$this->getOwningPlugin()->getServer()->getWorldManager()->isWorldGenerated($world) || $lobby !== null && !$this->getOwningPlugin()->getServer()->getWorldManager()->isWorldGenerated($lobby)) {
            $sender->sendMessage(TextFormat::RED . "There's no existing worlds with the name " . TextFormat::WHITE . $world);
            return;
        }

        $data = [
            GameData::GAME_DATA_NAME => $name,
            GameData::GAME_DATA_COUNTDOWN => (int)$countdown,
            GameData::GAME_DATA_MIN_PLAYERS => (int)$minPlayers,
            GameData::GAME_DATA_MAX_PLAYERS => (int)$maxPlayers,
            GameData::GAME_DATA_BUILD_DURATION => (int)$buildDuration,
            GameData::BUILD_HEIGHT => (int)$buildHeight,
            GameData::GAME_DATA_VOTE_DURATION => (int)$voteDuration,
            GameData::GAME_DATA_END_DURATION => (int)$endDuration,
            GameData::GAME_DATA_SPAWNS => [],
        ];

        $worldManager = $this->getOwningPlugin()->getServer()->getWorldManager();

        if (strtolower($world) === strtolower($lobby)) {
            $sender->sendMessage(TextFormat::RED . "Lobby world can't be the same as the build world.");
            return;
        }

        if ($worldManager->getDefaultWorld()->getFolderName() === $world || $worldManager->getDefaultWorld()->getFolderName() === $lobby) {
            $sender->sendMessage(TextFormat::RED . "Default world can't be used as build world or lobby world.");
            return;
        }

        $dataPath = $this->getOwningPlugin()->getDataFolder() . "maps" . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR;
        @mkdir($dataPath);

        file_put_contents($dataPath . "data.json", json_encode($data));

        $this->getOwningPlugin()->getGameManager()->addMap($name, $data);

        if ($worldManager->isWorldLoaded($world)) {
            $worldManager->getWorldByName($world)->save(true);
            $worldManager->unloadWorld($worldManager->getWorldByName($world), true);
        }

        if ($worldManager->isWorldLoaded($lobby)) {
            $worldManager->unloadWorld($worldManager->getWorldByName($lobby), true);
        }

        $directories = [];

        $directories[$this->getOwningPlugin()->getServer()->getDataPath() . "worlds" . DIRECTORY_SEPARATOR . $world] = $dataPath . DIRECTORY_SEPARATOR . GameData::GAME_DATA_WORLD;
        $directories[$this->getOwningPlugin()->getServer()->getDataPath() . "worlds" . DIRECTORY_SEPARATOR . $lobby] = $dataPath . DIRECTORY_SEPARATOR . GameData::GAME_DATA_LOBBY;

        $task = new AsyncDirectoryClone($directories);
        $task->setClosure(function () use ($sender, $name, $world, $maxPlayers, $worldManager): void {
            if (!$worldManager->isWorldLoaded($world) && !$worldManager->loadWorld($world)) {
                $sender->sendMessage(TextFormat::RED . "Failed to load world " . TextFormat::WHITE . $world);
                return;
            }
            $world = $this->getOwningPlugin()->getServer()->getWorldManager()->getWorldByName($world);
            $world->setAutoSave(false);
            $world->setTime(World::TIME_DAY);
            $world->stopTime();

            $session = $this->getOwningPlugin()->getSessionManager()->getSession($sender->getUniqueId()->getBytes());
            $session->setSetupHandler(new SetupHandler($this->getOwningPlugin(), $world, $name, $maxPlayers));

            $sender->sendMessage(TextFormat::GREEN . "Successfully created a map with the name " . TextFormat::WHITE . $name);
            $sender->sendMessage(TextFormat::GREEN . "Now you've entered the setup mode for the map");
            $sender->sendMessage(TextFormat::GREEN . "Now break the floor edges of each plot to set its area. Type " . TextFormat::WHITE . SetupHandler::SETUP_QUIT_MESSAGE . TextFormat::GREEN . " in chat to quit the setup mode.");
            $sender->setGamemode(GameMode::CREATIVE());

            $sender->teleport($world->getSpawnLocation());
        });

        BackgroundTaskPool::getInstance()->submitTask($task);
    }

    protected function prepare(): void
    {
        $this->setPermission(PermissionsList::BUILDBATTLE_SUBCOMMAND_CREATE);

        $this->registerArgument(0, new RawStringArgument(GameData::GAME_DATA_NAME));
        $this->registerArgument(1, new RawStringArgument(GameData::GAME_DATA_LOBBY));
        $this->registerArgument(2, new RawStringArgument(GameData::GAME_DATA_WORLD));
        $this->registerArgument(3, new IntegerArgument(GameData::GAME_DATA_COUNTDOWN));
        $this->registerArgument(4, new IntegerArgument(GameData::GAME_DATA_MIN_PLAYERS));
        $this->registerArgument(5, new IntegerArgument(GameData::GAME_DATA_MAX_PLAYERS));
        $this->registerArgument(6, new IntegerArgument(GameData::GAME_DATA_BUILD_DURATION));
        $this->registerArgument(7, new IntegerArgument(GameData::BUILD_HEIGHT));
        $this->registerArgument(8, new IntegerArgument(GameData::GAME_DATA_VOTE_DURATION));
        $this->registerArgument(9, new IntegerArgument(GameData::GAME_DATA_END_DURATION));

        $this->addConstraint(new InGameRequiredConstraint($this));
    }
}
