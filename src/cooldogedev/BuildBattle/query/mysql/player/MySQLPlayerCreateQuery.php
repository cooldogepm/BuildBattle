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

namespace cooldogedev\BuildBattle\query\mysql\player;

use cooldogedev\libSQL\query\MySQLQuery;
use mysqli;

final class MySQLPlayerCreateQuery extends MySQLQuery
{
    protected string $data;

    public function __construct(protected string $xuid, protected string $name, array $data)
    {
        $this->data = json_encode($data);
    }

    public function onRun(mysqli $connection): void
    {
        $xuid = $this->getXuid();
        $name = $this->getName();
        $data = json_decode($this->getData(), true);
        $wins = $data["wins"];
        $winStreak = $data["win_streak"];
        $losses = $data["losses"];

        $statement = $connection->prepare($this->getQuery());
        $statement->bind_param("sssss", $xuid, $name, $wins, $winStreak, $losses);
        $statement->execute();
        $this->setResult($statement->affected_rows > 0);
        $statement->close();
    }

    public function getXuid(): string
    {
        return $this->xuid;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function getQuery(): string
    {
        return "INSERT IGNORE INTO " . $this->getTable() . " (xuid, name, wins, win_streak, losses) VALUES (?, ?, ?, ?, ?)";
    }
}
