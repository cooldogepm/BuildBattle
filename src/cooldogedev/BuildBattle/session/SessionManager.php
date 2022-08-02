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
use pocketmine\player\Player;

final class SessionManager
{
    protected array $sessions;

    public function __construct(protected BuildBattle $plugin)
    {
        $this->sessions = [];
    }

    public function createSession(Player $player): ?Session
    {
        if ($this->hasSession($player->getUniqueId()->getBytes())) {
            return null;
        }
        $session = new Session($this->getPlugin(), $player);
        $this->sessions[$player->getUniqueId()->getBytes()] = $session;
        return $session;
    }

    public function hasSession(string $uuid): bool
    {
        return isset($this->sessions[$uuid]);
    }

    public function getPlugin(): BuildBattle
    {
        return $this->plugin;
    }

    public function removeSession(string $uuid): bool
    {
        if (!$this->hasSession($uuid)) {
            return false;
        }
        unset($this->sessions[$uuid]);
        return true;
    }

    public function getSession(string $uuid): ?Session
    {
        return $this->sessions[$uuid] ?? null;
    }

    /**
     * @return Session[]
     */
    public function getSessions(): array
    {
        return $this->sessions;
    }
}
