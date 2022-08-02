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

use pocketmine\math\Vector3;

final class Utils
{
    public static function recursiveClone(string $directory, string $target): void
    {
        $dir = opendir($directory);
        @mkdir($target);

        while ($file = readdir($dir)) {
            if (Utils::isPointer($file)) {
                continue;
            }

            if (is_dir($directory . DIRECTORY_SEPARATOR . $file)) {
                Utils::recursiveClone($directory . DIRECTORY_SEPARATOR . $file, $target . DIRECTORY_SEPARATOR . $file);
                continue;
            }

            copy($directory . DIRECTORY_SEPARATOR . $file, $target . DIRECTORY_SEPARATOR . $file);
        }

        closedir($dir);
    }

    public static function isPointer(string $file): bool
    {
        return $file === "." || $file === "..";
    }

    public static function recursiveDelete(string $directory): void
    {
        if (Utils::isPointer($directory)) {
            return;
        }

        foreach (scandir($directory) as $item) {
            if (Utils::isPointer($item)) {
                continue;
            }

            if (is_dir($directory . DIRECTORY_SEPARATOR . $item)) {
                Utils::recursiveDelete($directory . DIRECTORY_SEPARATOR . $item);
            }

            if (is_file($directory . DIRECTORY_SEPARATOR . $item)) {
                unlink($directory . DIRECTORY_SEPARATOR . $item);
            }
        }

        rmdir($directory);
    }

    public static function stringifyVec3(Vector3 $vec): string
    {
        return $vec->getX() . ":" . $vec->getY() . ":" . $vec->getZ();
    }

    public static function parseVec3(string $vec): Vector3
    {
        $vec = explode(":", $vec);
        return new Vector3((int)$vec[0], (int)$vec[1], (int)$vec[2]);
    }
}
