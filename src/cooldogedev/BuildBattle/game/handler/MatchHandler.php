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

namespace cooldogedev\BuildBattle\game\handler;

use cooldogedev\BuildBattle\game\Game;
use cooldogedev\BuildBattle\utility\message\KnownMessages;
use cooldogedev\BuildBattle\utility\message\LanguageManager;
use cooldogedev\BuildBattle\utility\message\TranslationKeys;

final class MatchHandler extends IHandler
{
    protected array $breakableBlocks = [];
    protected int $timeLeft;

    public function __construct(Game $game)
    {
        parent::__construct($game);

        $this->timeLeft = $this->getGame()->getData()->getBuildDuration();
        $this->startMatch();
    }

    public function startMatch(): void
    {
        $themes = $this->game->getPlugin()->getConfig()->get("themes");
        $this->getGame()->setTheme($themes[array_rand($themes)]);

        $translations = [
            TranslationKeys::THEME => $this->game->getTheme(),
        ];

        $this->game->broadcastTitle(LanguageManager::getMessage(KnownMessages::TOPIC_START, KnownMessages::START_TITLE), LanguageManager::getMessage(KnownMessages::TOPIC_START, KnownMessages::START_SUBTITLE), $translations, stay: 40);
        $this->game->broadcastMessage(LanguageManager::getMessage(KnownMessages::TOPIC_START, KnownMessages::START_MESSAGE), $translations);
    }

    public function getBreakableBlocks(): array
    {
        return $this->breakableBlocks;
    }

    public function addBreakableBlock(string $position, string $player): void
    {
        $this->breakableBlocks[$position] = $player;
    }

    public function removeBreakableBlock(string $position): void
    {
        unset($this->breakableBlocks[$position]);
    }

    public function isBreakableBlock(string $position, string $player): bool
    {
        return isset($this->breakableBlocks[$position]) && $this->breakableBlocks[$position] === $player;
    }

    public function handleTicking(): void
    {
        if ($this->timeLeft > 0) {
            $this->timeLeft--;
        } else {
            $this->handleMatchEnd();
        }
    }

    protected function handleMatchEnd(): void
    {
        $this->game->setHandler(new VoteHandler($this->game));
    }

    public function handleScoreboardUpdates(): void
    {
        if ($this->timeLeft < 1) {
            return;
        }

        foreach ($this->game->getPlayerManager()->getSessions() as $session) {
            if (!$session->getPlayer()->isOnline()) {
                continue;
            }

            $translations = [
                TranslationKeys::MAP => $this->game->getData()->getName(),
                TranslationKeys::PLAYERS_COUNT => count($this->game->getPlayerManager()->getSessions()),
                TranslationKeys::THEME => $this->game->getTheme(),
                TranslationKeys::TIME_LEFT => date("i:s", $this->timeLeft),
            ];

            $lines = array_map(fn($line) => $line !== "" ? LanguageManager::translate($line, $translations) : $line, $this->getScoreboardBody());

            $session->getScoreboard()->setLines($lines);
            $session->getScoreboard()->onUpdate();
        }
    }

    protected function getScoreboardBody(): array
    {
        $scoreboardData = LanguageManager::getArray(KnownMessages::TOPIC_SCOREBOARD, KnownMessages::SCOREBOARD_BODY);

        return $scoreboardData["match"];
    }
}
