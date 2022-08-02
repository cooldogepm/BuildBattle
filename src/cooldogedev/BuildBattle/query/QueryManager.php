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

namespace cooldogedev\BuildBattle\query;

use cooldogedev\BuildBattle\query\mysql\MySQLTableCreationQuery;
use cooldogedev\BuildBattle\query\mysql\player\MySQLPlayerCreateQuery;
use cooldogedev\BuildBattle\query\mysql\player\MySQLPlayerRetrieveQuery;
use cooldogedev\BuildBattle\query\mysql\player\MySQLPlayerUpdateQuery;
use cooldogedev\BuildBattle\query\sqlite\player\SQLitePlayerCreateQuery;
use cooldogedev\BuildBattle\query\sqlite\player\SQLitePlayerRetrieveQuery;
use cooldogedev\BuildBattle\query\sqlite\player\SQLitePlayerUpdateQuery;
use cooldogedev\BuildBattle\query\sqlite\SQLiteTableCreationQuery;
use cooldogedev\libSQL\query\SQLQuery;

final class QueryManager
{
    protected static bool $isMySQL = false;

    public static function isMySQL(): bool
    {
        return QueryManager::$isMySQL;
    }

    public static function setIsMySQL(bool $isMySQL): void
    {
        QueryManager::$isMySQL = $isMySQL;
    }

    public static function getPlayerUpdateQuery(string $xuid, array $data): SQLQuery
    {
        $class = QueryManager::$isMySQL ? MySQLPlayerUpdateQuery::class : SQLitePlayerUpdateQuery::class;

        return new $class($xuid, $data);
    }

    public static function getPlayerRetrieveQuery(string $xuid): SQLQuery
    {
        $class = QueryManager::$isMySQL ? MySQLPlayerRetrieveQuery::class : SQLitePlayerRetrieveQuery::class;

        return new $class($xuid);
    }

    public static function getPlayerCreateQuery(string $xuid, string $name, array $data): SQLQuery
    {
        $class = QueryManager::$isMySQL ? MySQLPlayerCreateQuery::class : SQLitePlayerCreateQuery::class;

        return new $class($xuid, $name, $data);
    }

    public static function getTableCreationQuery(): SQLQuery
    {
        $class = QueryManager::$isMySQL ? MySQLTableCreationQuery::class : SQLiteTableCreationQuery::class;

        return new $class();
    }
}
