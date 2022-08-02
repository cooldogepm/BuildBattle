<?php

declare(strict_types=1);

namespace cooldogedev\BuildBattle\utility\message;

final class KnownMessages
{
    public const TOPIC_PLAYER = "player";
    public const PLAYER_JOIN = "join";
    public const PLAYER_QUIT = "quit";

    public const TOPIC_END = "end";
    public const END_MESSAGE = "message";
    public const END_FIRST = "first";
    public const END_SECOND = "second";
    public const END_THIRD = "third";
    public const END_YOU = "you";

    public const TOPIC_COUNTDOWN = "countdown";
    public const COUNTDOWN_START = "start";
    public const COUNTDOWN_STOP = "stop";
    public const COUNTDOWN_DECREMENT = "decrement";

    public const TOPIC_START = "start";
    public const START_TITLE = "title";
    public const START_SUBTITLE = "subtitle";
    public const START_MESSAGE = "message";

    public const TOPIC_QUEUE_ERROR = "queue-error";
    public const QUEUE_ERROR_MESSAGE = "message";
    public const QUEUE_ERROR_KICK = "kick";

    public const TOPIC_COMMAND = "command";
    public const COMMAND_NAME = "name";
    public const COMMAND_ALIASES = "aliases";
    public const COMMAND_DESCRIPTION = "description";

    public const TOPIC_ITEMS = "items";
    public const ITEMS_QUIT_GAME = "quit-game";
    public const ITEMS_PLOT_FLOOR = "plot-floor";
    public const ITEMS_PLOT_FLOOR_INVENTORY = "plot-floor-inventory";

    public const TOPIC_VOTE_PREPARATION = "vote-preparation";
    public const VOTE_PREPARATION_TITLE = "title";
    public const VOTE_PREPARATION_SUBTITLE = "subtitle";
    public const VOTE_PREPARATION_MESSAGE = "message";

    public const TOPIC_PLOT_ENTER = "plot-enter";
    public const PLOT_ENTER_TITLE = "title";
    public const PLOT_ENTER_SUBTITLE = "subtitle";
    public const PLOT_ENTER_MESSAGE = "message";

    public const TOPIC_VOTING = "voting";
    public const VOTING_NONE = "none";
    public const VOTING_CONFIRM = "confirm";
    public const VOTING_ALREADY_CONFIRMED = "already-confirmed";
    public const VOTING_TIP = "tip";

    public const TOPIC_VOTE_ITEMS = "vote-items";
    public const VOTE_ITEMS_SUPER_POOP = "super-poop";
    public const VOTE_ITEMS_POOP = "poop";
    public const VOTE_ITEMS_OK = "ok";
    public const VOTE_ITEMS_GOOD = "good";
    public const VOTE_ITEMS_EPIC = "epic";
    public const VOTE_ITEMS_LEGENDARY = "legendary";

    public const TOPIC_CHAT = "chat";
    public const CHAT_MATCH = "match";
    public const CHAT_END = "end";

    public const TOPIC_SCOREBOARD = "scoreboard";
    public const SCOREBOARD_TITLE = "title";
    public const SCOREBOARD_BODY = "body";
}
