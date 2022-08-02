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

use cooldogedev\BuildBattle\permission\PermissionsList;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\constraint\InGameRequiredConstraint;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

final class QuitSubCommand extends BaseSubCommand
{
    public function __construct()
    {
        parent::__construct("quit", "Quit a game", ["q"]);
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $name = $args[JoinSubCommand::ARGUMENT_NAME] ?? null;

        // Set name to null if they want to queue up for a random game.
        if ($aliasUsed === "random") {
            $name = null;
        }

        $session = $this->getOwningPlugin()->getSessionManager()->getSession($sender->getUniqueId()->getBytes());
        $game = $session->getGame();

        if ($game === null) {
            $sender->sendMessage(TextFormat::RED . "You're not in a game!");
            return;
        }

        $game->getPlayerManager()->removeFromGame($session, true);
    }

    protected function prepare(): void
    {
        $this->setPermission(PermissionsList::BUILDBATTLE_SUBCOMMAND_QUIT);

        $this->addConstraint(new InGameRequiredConstraint($this));
    }
}
