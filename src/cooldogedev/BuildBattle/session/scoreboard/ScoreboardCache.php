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

namespace cooldogedev\BuildBattle\session\scoreboard;

class ScoreboardCache
{
    protected ?string $title;
    protected array $lines;
    protected bool $active;

    public function __construct(?string $title = null, array $lines = [], bool $active = false)
    {
        $this->title = $title;
        $this->lines = $lines;
        $this->active = $active;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getLines(): array
    {
        return $this->lines;
    }

    public function setLines(array $lines, bool $format = true): void
    {
        $this->lines = $format ? $this->formatLines($lines) : $lines;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function reset(): void
    {
        $this->setActive(false);
        $this->setTitle(null);
        $this->setLines([]);
    }

    public function formatLines(array $baseLines): array
    {
        $lines = [];

        $spaces = 0;

        $longestLine = -1;
        $longestLineIndex = -1;

        foreach ($baseLines as $index => $str) {
            if (strlen($str) > $longestLine) {
                $longestLine = strlen($str);
                $longestLineIndex = $index;
            }

            if ($str !== "") {
                $lines[$index] = $str;
                continue;
            }

            $lines[$index] = str_repeat(" ", $spaces);
            $spaces++;
        }

        $longestLineIndex !== -1 && $lines[$longestLineIndex] = trim($lines[$longestLineIndex]) . " &r";

        // Reverses the array to display the lines in descending order.
        return array_reverse($lines);
    }
}
