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

namespace cooldogedev\BuildBattle\utility\message;

use cooldogedev\BuildBattle\BuildBattle;
use pocketmine\utils\TextFormat;
use function array_keys;
use function array_values;
use function mkdir;
use function str_replace;

final class LanguageManager
{
    protected const DEFAULT_LANGUAGE = "en-US";
    protected const SUPPORTED_LANGUAGES = [
        "en-US",
        "vi-VN"
    ];

    protected static string $language;
    protected static array $translations;

    public static function init(BuildBattle $plugin, ?string $language)
    {
        $languagesFolder = $plugin->getDataFolder() . "languages";
        @mkdir($languagesFolder);

        foreach (LanguageManager::SUPPORTED_LANGUAGES as $languageCode) {
            $plugin->saveResource("languages" . DIRECTORY_SEPARATOR . $languageCode . ".yml");
        }

        if (!$language || !in_array($language, LanguageManager::SUPPORTED_LANGUAGES)) {
            $language = LanguageManager::DEFAULT_LANGUAGE;
        }

        LanguageManager::$language = $language;
        LanguageManager::$translations = yaml_parse_file(
            $languagesFolder . DIRECTORY_SEPARATOR . $language . ".yml"
        );
    }

    public static function getMessage(string $topic, string $message): string
    {
        return TextFormat::colorize(LanguageManager::$translations[$topic][$message] ?? "Translation not found");
    }

    public static function getArray(string $topic, string $message): array
    {
        return LanguageManager::$translations[$topic][$message] ?? [];
    }

    public static function translate(string $message, array $variables = []): string
    {
        return str_replace(array_keys($variables), array_values($variables), $message);
    }

    public static function getLanguage(): string
    {
        return LanguageManager::$language;
    }
}
