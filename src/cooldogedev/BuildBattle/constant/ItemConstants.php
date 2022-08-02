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

namespace cooldogedev\BuildBattle\constant;

use cooldogedev\BuildBattle\utility\message\KnownMessages;
use cooldogedev\BuildBattle\utility\message\LanguageManager;

final class ItemConstants
{
    // Identifier of the item's namedtag
    public const ITEM_TYPE_IDENTIFIER = "buildbattle_item";

    public const ITEM_QUIT_GAME = 0;

    // Vote item types
    public const ITEM_VOTE_SUPER_POOP = 1;
    public const ITEM_VOTE_POOP = 2;
    public const ITEM_VOTE_OK = 3;
    public const ITEM_VOTE_GOOD = 4;
    public const ITEM_VOTE_EPIC = 5;
    public const ITEM_VOTE_LEGENDARY = 6;

    public const ITEM_PLOT_FLOOR = 7;

    public static function voteTypeToCustomName(?int $voteType): string
    {
        return match ($voteType) {
            ItemConstants::ITEM_VOTE_SUPER_POOP => LanguageManager::getMessage(KnownMessages::TOPIC_VOTE_ITEMS, KnownMessages::VOTE_ITEMS_SUPER_POOP),
            ItemConstants::ITEM_VOTE_POOP => LanguageManager::getMessage(KnownMessages::TOPIC_VOTE_ITEMS, KnownMessages::VOTE_ITEMS_POOP),
            ItemConstants::ITEM_VOTE_OK => LanguageManager::getMessage(KnownMessages::TOPIC_VOTE_ITEMS, KnownMessages::VOTE_ITEMS_OK),
            ItemConstants::ITEM_VOTE_GOOD => LanguageManager::getMessage(KnownMessages::TOPIC_VOTE_ITEMS, KnownMessages::VOTE_ITEMS_GOOD),
            ItemConstants::ITEM_VOTE_EPIC => LanguageManager::getMessage(KnownMessages::TOPIC_VOTE_ITEMS, KnownMessages::VOTE_ITEMS_EPIC),
            ItemConstants::ITEM_VOTE_LEGENDARY => LanguageManager::getMessage(KnownMessages::TOPIC_VOTE_ITEMS, KnownMessages::VOTE_ITEMS_LEGENDARY),
            default => LanguageManager::getMessage(KnownMessages::TOPIC_VOTING, KnownMessages::VOTING_NONE),
        };
    }
}
