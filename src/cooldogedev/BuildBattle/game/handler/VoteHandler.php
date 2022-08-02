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

use cooldogedev\BuildBattle\constant\ItemConstants;
use cooldogedev\BuildBattle\game\Game;
use cooldogedev\BuildBattle\session\Session;
use cooldogedev\BuildBattle\utility\message\KnownMessages;
use cooldogedev\BuildBattle\utility\message\LanguageManager;
use cooldogedev\BuildBattle\utility\message\TranslationKeys;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;

/**
 * This handler is responsible for handling the grace phase of the game after every round.
 */
final class VoteHandler extends IHandler
{
    protected int $timeLeft;
    protected int $countdown = 3;
    protected bool $started = false;
    protected ?Session $currentBuilder = null;

    public function __construct(Game $game)
    {
        parent::__construct($game);

        $this->timeLeft = $this->game->getData()->getVoteDuration();
        $this->game->broadcastTitle(LanguageManager::getMessage(KnownMessages::TOPIC_VOTE_PREPARATION, KnownMessages::VOTE_PREPARATION_TITLE), LanguageManager::getMessage(KnownMessages::TOPIC_VOTE_PREPARATION, KnownMessages::VOTE_PREPARATION_SUBTITLE), stay: 40);
        $this->game->broadcastMessage(LanguageManager::getMessage(KnownMessages::TOPIC_VOTE_PREPARATION, KnownMessages::VOTE_PREPARATION_MESSAGE));

        foreach ($game->getPlayerManager()->getSessions() as $session) {
            $session->clearAll();
        }
    }

    public function handleTicking(): void
    {
        if ($this->countdown > 0) {
            $this->countdown--;
            return;
        }

        if (!$this->started) {
            $this->startVote();
            $this->startNewSelection();

            $this->started = true;

            return;
        }

        if ($this->timeLeft > 0) {
            $this->timeLeft--;
        } else {
            $this->startNewSelection();
        }
    }

    protected function startVote(): void
    {
        foreach ($this->game->getPlayerManager()->getSessions() as $session) {
            $session->setState(Session::PLAYER_STATE_WAITING_OWN_BUILD);
        }
    }

    protected function startNewSelection(): void
    {
        $waitingPlayers = $this->game->getPlayerManager()->getSessions(Session::PLAYER_STATE_WAITING_OWN_BUILD);

        if (count($waitingPlayers) < 1) {
            $this->game->calculateResults();
            return;
        }

        $this->timeLeft = $this->game->getData()->getVoteDuration();
        $this->currentBuilder = $waitingPlayers[array_rand($waitingPlayers)];
        $this->currentBuilder->setState(Session::PLAYER_STATE_WAITING_RESULTS);

        $translations = [
            TranslationKeys::THEME => $this->game->getTheme(),
            TranslationKeys::BUILDER => $this->currentBuilder->getPlayer()->getDisplayName(),
        ];

        $this->game->broadcastTitle(LanguageManager::getMessage(KnownMessages::TOPIC_PLOT_ENTER, KnownMessages::PLOT_ENTER_TITLE), LanguageManager::getMessage(KnownMessages::TOPIC_PLOT_ENTER, KnownMessages::PLOT_ENTER_SUBTITLE), $translations, stay: 40);
        $this->game->broadcastMessage(LanguageManager::getMessage(KnownMessages::TOPIC_PLOT_ENTER, KnownMessages::PLOT_ENTER_MESSAGE), $translations);

        foreach ($this->game->getPlayerManager()->getSessions() as $session) {
            $session->clearAll();
            $session->setVote(null);
            $session->getPlayer()->teleport($this->currentBuilder->getPlot()->getCenter());

            if ($session !== $this->currentBuilder) {
                $this->sendItems($session);
            }
        }
    }

    protected function sendItems(Session $session): void
    {
        $superPoop = VanillaBlocks::STAINED_CLAY()->setColor(DyeColor::RED())->asItem();
        $poop = VanillaBlocks::STAINED_CLAY()->setColor(DyeColor::PINK())->asItem();
        $ok = VanillaBlocks::STAINED_CLAY()->setColor(DyeColor::LIME())->asItem();
        $good = VanillaBlocks::STAINED_CLAY()->setColor(DyeColor::GREEN())->asItem();
        $epic = VanillaBlocks::STAINED_CLAY()->setColor(DyeColor::PURPLE())->asItem();
        $legendary = VanillaBlocks::STAINED_CLAY()->setColor(DyeColor::YELLOW())->asItem();

        $superPoop->getNamedTag()->setInt(ItemConstants::ITEM_TYPE_IDENTIFIER, ItemConstants::ITEM_VOTE_SUPER_POOP);
        $poop->getNamedTag()->setInt(ItemConstants::ITEM_TYPE_IDENTIFIER, ItemConstants::ITEM_VOTE_POOP);
        $ok->getNamedTag()->setInt(ItemConstants::ITEM_TYPE_IDENTIFIER, ItemConstants::ITEM_VOTE_OK);
        $good->getNamedTag()->setInt(ItemConstants::ITEM_TYPE_IDENTIFIER, ItemConstants::ITEM_VOTE_GOOD);
        $epic->getNamedTag()->setInt(ItemConstants::ITEM_TYPE_IDENTIFIER, ItemConstants::ITEM_VOTE_EPIC);
        $legendary->getNamedTag()->setInt(ItemConstants::ITEM_TYPE_IDENTIFIER, ItemConstants::ITEM_VOTE_LEGENDARY);

        $superPoop->setCustomName(LanguageManager::getMessage(KnownMessages::TOPIC_VOTE_ITEMS, KnownMessages::VOTE_ITEMS_SUPER_POOP));
        $poop->setCustomName(LanguageManager::getMessage(KnownMessages::TOPIC_VOTE_ITEMS, KnownMessages::VOTE_ITEMS_POOP));
        $ok->setCustomName(LanguageManager::getMessage(KnownMessages::TOPIC_VOTE_ITEMS, KnownMessages::VOTE_ITEMS_OK));
        $good->setCustomName(LanguageManager::getMessage(KnownMessages::TOPIC_VOTE_ITEMS, KnownMessages::VOTE_ITEMS_GOOD));
        $epic->setCustomName(LanguageManager::getMessage(KnownMessages::TOPIC_VOTE_ITEMS, KnownMessages::VOTE_ITEMS_EPIC));
        $legendary->setCustomName(LanguageManager::getMessage(KnownMessages::TOPIC_VOTE_ITEMS, KnownMessages::VOTE_ITEMS_LEGENDARY));

        $session->getPlayer()->getInventory()->setItem(0, $superPoop);
        $session->getPlayer()->getInventory()->setItem(1, $poop);
        $session->getPlayer()->getInventory()->setItem(2, $ok);
        $session->getPlayer()->getInventory()->setItem(3, $good);
        $session->getPlayer()->getInventory()->setItem(4, $epic);
        $session->getPlayer()->getInventory()->setItem(5, $legendary);
    }

    /**
     * Since we use the voteType which is basically an action type we must subtract by the previous
     * action type id to get the correct score e.g. poop which has an id of 4 becomes 1 and so on.
     */
    public function onVote(Session $session, int $voteType, string $vote): void
    {
        $translations = [
            TranslationKeys::THEME => $this->game->getTheme(),
            TranslationKeys::BUILDER => $session->getPlayer()->getDisplayName(),
            TranslationKeys::VOTE => $vote,
        ];

        if ($session->getVote() === $voteType) {
            $session->getPlayer()->sendMessage(LanguageManager::translate(LanguageManager::getMessage(KnownMessages::TOPIC_VOTING, KnownMessages::VOTING_ALREADY_CONFIRMED), $translations));
            return;
        }

        // Remove the old vote if it exists
        if ($session->getVote() !== null) {
            $this->currentBuilder?->subtractScore($session->getVote());
        }

        $session->setVote($voteType);
        $this->currentBuilder?->addScore($voteType);

        $session->getPlayer()->sendMessage(LanguageManager::translate(LanguageManager::getMessage(KnownMessages::TOPIC_VOTING, KnownMessages::VOTING_CONFIRM), $translations));
    }

    public function handleScoreboardUpdates(): void
    {
        if (!$this->started || $this->countdown > 0) {
            return;
        }

        if ($this->timeLeft < 1) {
            return;
        }

        foreach ($this->game->getPlayerManager()->getSessions() as $session) {
            if (!$session->getPlayer()->isOnline()) {
                continue;
            }

            $session !== $this->currentBuilder && $session->getPlayer()->sendTip(LanguageManager::translate(LanguageManager::getMessage(KnownMessages::TOPIC_VOTING, KnownMessages::VOTING_TIP), [
                TranslationKeys::TIME_LEFT => $this->timeLeft,
            ]));

            $translations = [
                TranslationKeys::MAP => $this->game->getData()->getName(),
                TranslationKeys::PLAYERS_COUNT => count($this->game->getPlayerManager()->getSessions()),
                TranslationKeys::THEME => $this->game->getTheme(),
                TranslationKeys::BUILDER => $this->currentBuilder !== null ? $this->currentBuilder->getPlayer()->getDisplayName() : "N/A",
                TranslationKeys::VOTE => ItemConstants::voteTypeToCustomName($session->getVote()),
            ];

            $lines = array_map(fn($line) => $line !== "" ? LanguageManager::translate($line, $translations) : $line, $this->getScoreboardBody());

            $session->getScoreboard()->setLines($lines);
            $session->getScoreboard()->onUpdate();
        }
    }

    public function getScoreboardBody(): array
    {
        $scoreboardData = LanguageManager::getArray(KnownMessages::TOPIC_SCOREBOARD, KnownMessages::SCOREBOARD_BODY);

        return $scoreboardData["vote"];
    }
}
