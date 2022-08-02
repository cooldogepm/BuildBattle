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

namespace cooldogedev\BuildBattle\session\scoreboard;

use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

final class Scoreboard extends ScoreboardCache
{
    protected Player $player;
    protected ScoreboardCache $cache;

    public function __construct(Player $player)
    {
        parent::__construct();
        $this->player = $player;
        $this->cache = new ScoreboardCache(null, [], false);
    }

    public function reset(): void
    {
        $this->hideDisplay();
        $this->getCache()->reset();
        parent::reset();
    }

    public function hideDisplay(bool $updateCache = true): void
    {
        if ($this->getTitle() !== null) {
            $pk = new RemoveObjectivePacket();
            $pk->objectiveName = $this->getTitle();

            $this->getPlayer()->getNetworkSession()->sendDataPacket($pk);
        }

        $updateCache && $this->getCache()->setActive(false);
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function getCache(): ScoreboardCache
    {
        return $this->cache;
    }

    public function onUpdate(): void
    {
        if (!$this->player->isConnected()) {
            return;
        }

        if ($this->isActive() && !$this->getCache()->isActive()) {
            $this->showDisplay();
        }

        if (!$this->isActive() && $this->getCache()->isActive()) {
            $this->hideDisplay();
        }

        if (json_encode($this->getLines()) !== json_encode($this->getCache()->getLines())) {
            $this->showLines();
        }
    }

    public function showDisplay(bool $updateCache = true): void
    {
        $pk = new SetDisplayObjectivePacket();
        $pk->displaySlot = SetDisplayObjectivePacket::DISPLAY_SLOT_SIDEBAR;
        $pk->objectiveName = $this->getTitle();
        $pk->displayName = TextFormat::colorize($this->getTitle());
        $pk->criteriaName = "dummy";
        $pk->sortOrder = SetDisplayObjectivePacket::SORT_ORDER_DESCENDING;

        $this->getPlayer()->getNetworkSession()->sendDataPacket($pk);
        $updateCache && $this->getCache()->setActive(true);
    }

    public function showLines(): void
    {
        $this->hideDisplay(false);
        $this->showDisplay(false);

        foreach ($this->getLines() as $index => $line) {
            // Skips line zero.
            $index++;

            $entry = new ScorePacketEntry();
            $entry->objectiveName = $this->getTitle();
            $entry->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
            $entry->customName = TextFormat::colorize($line);
            $entry->score = $index;
            $entry->scoreboardId = $index;

            $pk = new SetScorePacket();
            $pk->type = SetScorePacket::TYPE_CHANGE;
            $pk->entries[] = $entry;
            $this->getPlayer()->getNetworkSession()->sendDataPacket($pk);
        }
        $this->getCache()->setLines($this->getLines());
    }

    public function setLines(array $lines, bool $format = true): void
    {
        $this->getCache()->setLines($this->lines, false);

        parent::setLines($lines);
    }
}
