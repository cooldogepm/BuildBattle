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

namespace cooldogedev\BuildBattle\session\data;

use cooldogedev\BuildBattle\game\Game;
use cooldogedev\BuildBattle\game\plot\Plot;
use cooldogedev\BuildBattle\session\Session;

trait SessionData
{
    protected ?Game $game = null;
    protected ?Plot $plot = null;
    protected bool $queued = false;
    protected int $state = Session::PLAYER_STATE_DEFAULT;
    protected int $score = 0;
    protected ?int $vote = null;

    public function getVote(): ?int
    {
        return $this->vote;
    }

    public function setVote(?int $vote): void
    {
        $this->vote = $vote;
    }

    public function getScore(): int
    {
        return $this->score;
    }

    public function setScore(int $score): void
    {
        $this->score = $score;
    }

    public function addScore(int $addition): void
    {
        $this->score += $addition;
    }

    public function subtractScore(int $subtraction): void
    {
        $this->score -= $subtraction;
    }

    public function getPlot(): ?Plot
    {
        return $this->plot;
    }

    public function setPlot(?Plot $plot): void
    {
        $plot?->setTaken(true);

        $this->plot?->setTaken(false);
        $this->plot = $plot;
    }

    public function getState(): int
    {
        return $this->state;
    }

    public function setState(int $state): void
    {
        $this->state = $state;
    }

    public function getGame(): ?Game
    {
        return $this->game;
    }

    public function setGame(?Game $game): void
    {
        $this->game = $game;
    }

    public function isQueued(): bool
    {
        return $this->queued;
    }

    public function setQueued(bool $queued): void
    {
        $this->queued = $queued;
    }

    public function inGame(): bool
    {
        return $this->game !== null;
    }
}
