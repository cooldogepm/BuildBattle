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

namespace cooldogedev\BuildBattle;

use cooldogedev\BuildBattle\constant\ItemConstants;
use cooldogedev\BuildBattle\game\Game;
use cooldogedev\BuildBattle\game\handler\EndHandler;
use cooldogedev\BuildBattle\game\handler\MatchHandler;
use cooldogedev\BuildBattle\game\handler\VoteHandler;
use cooldogedev\BuildBattle\session\Session;
use cooldogedev\BuildBattle\utility\message\KnownMessages;
use cooldogedev\BuildBattle\utility\message\LanguageManager;
use cooldogedev\BuildBattle\utility\message\TranslationKeys;
use cooldogedev\BuildBattle\utility\Utils;
use Exception;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use muqsit\invmenu\type\InvMenuTypeIds;
use pocketmine\block\TNT;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockBurnEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockSpreadEvent;
use pocketmine\event\block\LeavesDecayEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\FlintSteel;
use pocketmine\item\ItemBlock;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;

final class EventListener implements Listener
{
    protected BuildBattle $plugin;

    public function __construct(BuildBattle $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * @ignoreCanceled true
     */
    public function onPlayerItemUse(PlayerItemUseEvent $event): void
    {
        $player = $event->getPlayer();
        $item = $event->getItem();
        $session = $this->getPlugin()->getSessionManager()->getSession($player->getUniqueId()->getBytes());
        $game = $session->getGame();

        if ($game === null) {
            return;
        }

        try {
            $actionType = $item->getNamedTag()->getInt(ItemConstants::ITEM_TYPE_IDENTIFIER);
        } catch (Exception) {
            return;
        }

        switch ($actionType) {
            case ItemConstants::ITEM_QUIT_GAME:
                $game->getPlayerManager()->removeFromGame($session);
                break;
            case ItemConstants::ITEM_PLOT_FLOOR:
                $menu = InvMenu::create(InvMenuTypeIds::TYPE_CHEST);
                $menu->setName(LanguageManager::getMessage(KnownMessages::TOPIC_ITEMS, KnownMessages::ITEMS_PLOT_FLOOR_INVENTORY));
                $menu->setListener(InvMenu::readonly(function (DeterministicInvMenuTransaction $transaction) use ($menu, $session): void {
                    $item = $transaction->getItemClickedWith();
                    if (!$item->isNull() && $item instanceof ItemBlock) {
                        $menu->onClose($transaction->getPlayer());
                        $this->getPlugin()->getScheduler()->scheduleDelayedTask(new ClosureTask(fn() => $session->getPlot()->setFloor($item->getBlock())), 1);
                    }
                }));
                $menu->send($player);
                break;
            case ItemConstants::ITEM_VOTE_SUPER_POOP:
            case ItemConstants::ITEM_VOTE_POOP:
            case ItemConstants::ITEM_VOTE_OK:
            case ItemConstants::ITEM_VOTE_GOOD:
            case ItemConstants::ITEM_VOTE_EPIC:
            case ItemConstants::ITEM_VOTE_LEGENDARY:
                $handler = $game->getHandler();

                if (!$handler instanceof VoteHandler) {
                    return;
                }

                $handler->onVote($session, $actionType, $item->getCustomName());
        }
    }

    public function getPlugin(): BuildBattle
    {
        return $this->plugin;
    }

    /**
     * @ignoreCanceled true
     */
    public function onPlayerDropItem(PlayerDropItemEvent $event): void
    {
        $player = $event->getPlayer();
        $session = $this->getPlugin()->getSessionManager()->getSession($player->getUniqueId()->getBytes());
        if ($session->inGame()) {
            $event->cancel();
        }
    }

    /**
     * @ignoreCanceled true
     */
    public function onPlayerInteract(PlayerInteractEvent $event): void
    {
        $player = $event->getPlayer();
        $session = $this->getPlugin()->getSessionManager()->getSession($player->getUniqueId()->getBytes());

        if ($session->inGame() && $event->getBlock() instanceof TNT && $event->getItem() instanceof FlintSteel) {
            $event->cancel();
        }
    }

    /**
     * @ignoreCanceled true
     * @priority MONITOR
     */
    public function onPlayerChat(PlayerChatEvent $event): void
    {
        $player = $event->getPlayer();
        $session = $this->getPlugin()->getSessionManager()->getSession($player->getUniqueId()->getBytes());
        $game = $session->getGame();

        if ($session->getSetupHandler() !== null) {
            $session->getSetupHandler()->handleMessage($session, strtolower($event->getMessage())) && $event->cancel();
            return;
        }

        if ($game === null) {
            return;
        }

        $translations = [
            TranslationKeys::PLAYER => $player->getDisplayName(),
            TranslationKeys::MESSAGE => $event->getMessage(),
            TranslationKeys::SCORE => (string)$session->getScore()
        ];

        $event->setFormat(
            $game->getHandler() instanceof EndHandler ?
                LanguageManager::translate(LanguageManager::getMessage(KnownMessages::TOPIC_CHAT, KnownMessages::CHAT_MATCH), $translations)
                :
                LanguageManager::translate(LanguageManager::getMessage(KnownMessages::TOPIC_CHAT, KnownMessages::CHAT_END), $translations)
        );
    }

    /**
     * @ignoreCanceled true
     * @priority MONITOR
     */
    public function onInventoryTransaction(InventoryTransactionEvent $event): void
    {
        $player = $event->getTransaction()->getSource();
        $session = $this->getPlugin()->getSessionManager()->getSession($player->getUniqueId()->getBytes());
        $game = $session->getGame();

        if ($game !== null && !$game->getHandler() instanceof MatchHandler) {
            $event->cancel();
        }
    }

    /**
     * @ignoreCanceled true
     */
    public function onPlayerLogin(PlayerLoginEvent $event): void
    {
        $player = $event->getPlayer();
        $this->getPlugin()->getSessionManager()->createSession($player);
    }

    public function onPlayerJoin(PlayerJoinEvent $event): void
    {
        $player = $event->getPlayer();
        $session = $this->getPlugin()->getSessionManager()->getSession($player->getUniqueId()->getBytes());

        if ($this->getPlugin()->getConfig()->get("behaviour")["queue-on-login"] && $session) {
            $this->getPlugin()->getGameManager()->queueToGame([$session], null, true, true);
        }
    }

    public function onPlayerQuit(PlayerQuitEvent $event): void
    {
        $player = $event->getPlayer();
        $session = $this->getPlugin()->getSessionManager()->getSession($player->getUniqueId()->getBytes());
        $session->save();

        $game = $session->getGame();
        $game?->getPlayerManager()->removeFromGame($session, true);

        $this->getPlugin()->getSessionManager()->removeSession($player->getUniqueId()->getBytes());
    }

    /**
     * @priority MONITOR
     */
    public function onPlayerBreak(BlockBreakEvent $event): void
    {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        $session = $this->getPlugin()->getSessionManager()->getSession($player->getUniqueId()->getBytes());
        $game = $session->getGame();

        if ($session->getSetupHandler() !== null) {
            $session->getSetupHandler()->handleBlockBreak($session, $event->getBlock()->getPosition());
            $event->cancel();

            return;
        }

        if ($game === null) {
            return;
        }

        $handler = $game->getHandler();

        if ($handler instanceof MatchHandler && $handler->isBreakableBlock(Utils::stringifyVec3($block->getPosition()), $player->getName())) {
            $handler->removeBreakableBlock(Utils::stringifyVec3($block->getPosition()));
            return;
        }

        $event->cancel();
    }

    public function onLeavesDecay(LeavesDecayEvent $event): void
    {
        if (str_starts_with($event->getBlock()->getPosition()->getWorld()->getFolderName(), Game::GAME_WORLD_IDENTIFIER)) {
            $event->cancel();
        }
    }

    public function onBlockBurn(BlockBurnEvent $event): void
    {
        if (str_starts_with($event->getBlock()->getPosition()->getWorld()->getFolderName(), Game::GAME_WORLD_IDENTIFIER)) {
            $event->cancel();
        }
    }

    public function onBlockSpread(BlockSpreadEvent $event): void
    {
        if (str_starts_with($event->getBlock()->getPosition()->getWorld()->getFolderName(), Game::GAME_WORLD_IDENTIFIER)) {
            $event->cancel();
        }
    }

    /**
     * @priority MONITOR
     */
    public function onPlayerPlace(BlockPlaceEvent $event): void
    {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        $session = $this->getPlugin()->getSessionManager()->getSession($player->getUniqueId()->getBytes());
        $game = $session->getGame();

        if ($game === null) {
            return;
        }

        $handler = $game->getHandler();

        if ($handler instanceof MatchHandler && $session->getPlot()->isWithin($event->getBlock()->getPosition())) {
            $handler->addBreakableBlock(Utils::stringifyVec3($block->getPosition()), $player->getName());
            return;
        }

        $event->cancel();
    }

    /**
     * @ignoreCanceled
     * @priority MONITOR
     */
    public function onEntityDamage(EntityDamageEvent $event): void
    {
        $entity = $event->getEntity();

        if (!$entity instanceof Player) {
            return;
        }

        $session = $this->getPlugin()->getSessionManager()->getSession($entity->getUniqueId()->getBytes());

        if ($session->inGame()) {
            $event->cancel();
        }
    }

    /**
     * @priority MONITOR
     */
    public function onPlayerMove(PlayerMoveEvent $event): void
    {
        $player = $event->getPlayer();
        $session = $this->getPlugin()->getSessionManager()->getSession($player->getUniqueId()->getBytes());
        $game = $session->getGame();

        if ($game === null || $session->getState() !== Session::PLAYER_STATE_BUILDING || !$game->getHandler() instanceof MatchHandler) {
            return;
        }

        if (!$session->getPlot()->isWithin($event->getTo()->floor())) {
            $event->cancel();
        }
    }
}
