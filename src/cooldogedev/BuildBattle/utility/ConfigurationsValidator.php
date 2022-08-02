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

namespace cooldogedev\BuildBattle\utility;

use cooldogedev\BuildBattle\BuildBattle;
use cooldogedev\BuildBattle\utility\message\LanguageManager;

final class ConfigurationsValidator
{
    protected const TOKEN_CONFIG_VERSION = "config-version";

    // TODO: Clean this up
    public static function validate(BuildBattle $plugin): void
    {
        $config = $plugin->getConfig();

        if ($config->get("config-version") !== $plugin->getDescription()->getVersion()) {
            copy($plugin->getDataFolder() . "config.yml", $plugin->getDataFolder() . "config.yml.old");

            $plugin->saveConfig();
            $plugin->saveResource("config.yml", true);
            $plugin->reloadConfig();

            $plugin->getLogger()->warning("Your config file is outdated. It has been backed up to config.old.yml");
        }

        $languageFile = "languages" . DIRECTORY_SEPARATOR . $config->get("language") . ".yml";

        $tokens = ConfigurationsValidator::getTokens(file_get_contents($plugin->getDataFolder() . $languageFile));

        if (ConfigurationsValidator::getToken($tokens, ConfigurationsValidator::TOKEN_CONFIG_VERSION) !== $plugin->getDescription()->getVersion()) {
            copy($plugin->getDataFolder() . $languageFile, $plugin->getDataFolder() . $languageFile . ".old");
            $plugin->saveResource($languageFile, true);
            $plugin->getLogger()->warning("Your language file is either outdated or corrupted (has no tokens). It has been backed up to " . $languageFile . ".old");

            LanguageManager::init($plugin, $plugin->getConfig()->get("language"));
        }
    }

    public static function getTokens(string $content): array
    {
        preg_match_all("/###(.*?)###/", $content, $tokens);

        // Skip the matches and just get the groups.
        $tokens = count($tokens[1]) > 0 ? $tokens[1] : [];

        // Trim the tokens.
        $tokens = array_map(fn($token) => trim($token), $tokens);

        // return the tokens as key value pairs.

        return array_combine(array_map(fn(string $token) => trim(explode(":", $token)[0]), $tokens), array_map(fn(string $token) => trim(explode(":", $token)[1]), $tokens));
    }

    public static function getToken(array $tokens, string $token): ?string
    {
        return $tokens[$token] ?? null;
    }
}
