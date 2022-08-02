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

namespace cooldogedev\BuildBattle\game\data;

use cooldogedev\BuildBattle\game\Game;

final class GameData
{
    public const GAME_DATA_NAME = "name";
    public const GAME_DATA_WORLD = "world";
    public const GAME_DATA_LOBBY = "lobby";
    public const GAME_DATA_COUNTDOWN = "countdown";
    public const GAME_DATA_MIN_PLAYERS = "min_players";
    public const GAME_DATA_MAX_PLAYERS = "max_players";
    public const GAME_DATA_BUILD_DURATION = "build_duration";
    public const GAME_DATA_VOTE_DURATION = "vote_duration";
    public const GAME_DATA_END_DURATION = "end_duration";
    public const BUILD_HEIGHT = "build_height";
    public const GAME_DATA_SPAWNS = "spawns";

    public function __construct(protected int $id, protected string $name, protected int $countdown, protected int $minPlayers, protected int $maxPlayers, protected int $buildDuration, protected int $buildHeight, protected int $voteDuration, protected int $endDuration, protected array $spawns)
    {
    }

    public function getBuildHeight(): int
    {
        return $this->buildHeight;
    }

    public function getSpawns(): array
    {
        return $this->spawns;
    }

    public function getLobby(): string
    {
        return Game::GAME_LOBBY_IDENTIFIER . $this->id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getEndDuration(): int
    {
        return $this->endDuration;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMaxPlayers(): int
    {
        return $this->maxPlayers;
    }

    public function getMinPlayers(): int
    {
        return $this->minPlayers;
    }

    public function getCountdown(): int
    {
        return $this->countdown;
    }

    public function getBuildDuration(): int
    {
        return $this->buildDuration;
    }

    public function getVoteDuration(): int
    {
        return $this->voteDuration;
    }

    public function getWorld(): string
    {
        return Game::GAME_WORLD_IDENTIFIER . $this->id;
    }
}
