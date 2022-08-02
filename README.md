# BuildBattle

<p align="center">
	<a href="https://github.com/cooldogedev/BuildBattle"><img
            src="https://github.com/cooldogedev/BuildBattle/blob/main/assets/icon.png?raw=true"/></a><br>
	An extremely customizable BuildBattle mini-game designed for scalability and simplicity.
</p>

## Features

- Customisable messages and scoreboard
- Multi arena support
- Waiting lobby support
- Auto-queue support
- Game statistics
- SQLite support
- MySQL support

## Commands

|      Command       |          Description          |           Permission           |
|:------------------:|:-----------------------------:|:------------------------------:|
| buildbattle create |      Create a new arena.      | `buildbattle.subcommand.create` |
| buildbattle delete | Delete an existing arena. | `buildbattle.subcommand.delete` |
|  buildbattle list  | List all available arenas. |  `buildbattle.subcommand.list`  |
|  buildbattle join  |    Join a game.    |  `buildbattle.subcommand.join`  |
|  buildbattle quit  |    Quit a game.    |  `buildbattle.subcommand.quit`  |

### Arena creation

#### Format

`buildbattle create <name: string> <lobby: string> <world: string> <countdown: int> <min_players: int> <max_players: int> <build_duration: int> <build_height: int> <vote_duration: int> <end_duration: int>`

#### Example

`buildbattle create name game-lobby game-world 30 6 24 300 30 10 10 10`
