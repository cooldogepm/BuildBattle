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

namespace cooldogedev\BuildBattle\game\plot;

use cooldogedev\BuildBattle\async\BackgroundTaskPool;
use cooldogedev\BuildBattle\async\world\AsyncSetBlocks;
use pocketmine\block\Block;
use pocketmine\math\Vector3;
use pocketmine\world\format\Chunk;
use pocketmine\world\format\io\FastChunkSerializer;
use pocketmine\world\World;

final class Plot
{
    protected Vector3 $min;
    protected Vector3 $max;
    protected Vector3 $center;
    protected bool $taken = false;

    public function __construct(protected int $id, protected World $world, Vector3 $min, Vector3 $max, protected int $height)
    {
        $this->height += $min->y;

        $this->min = Vector3::minComponents($min, $max);
        $this->max = Vector3::maxComponents($min, $max);

        $center = new Vector3(($this->min->x + $this->max->x) / 2, ($this->min->y + $this->height) / 2, ($this->min->z + $this->max->z) / 2);
        $this->center = $center->floor()->add(0.5, 0, 0.5);
    }

    public function setFloor(Block $block): void
    {
        $chunks = [];

        for ($x = min($this->min->x, $this->max->x) >> Chunk::COORD_BIT_SIZE; $x <= max($this->min->x, $this->max->x) >> Chunk::COORD_BIT_SIZE; $x++) {
            for ($z = min($this->min->z, $this->max->z) >> Chunk::COORD_BIT_SIZE; $z <= max($this->min->z, $this->max->z) >> Chunk::COORD_BIT_SIZE; $z++) {
                // Get or load chunk at x, z
                $chunks[World::chunkHash($x, $z)] = $this->world->getChunk($x, $z);
            }
        }

        $task = new AsyncSetBlocks($chunks, $block->getFullId(), $this->min->x, $this->max->x, $this->min->z, $this->max->z, $this->min->y);
        $task->setClosure(function (AsyncSetBlocks $task): void {
            $chunks = array_map(fn(string $chunk) => FastChunkSerializer::deserializeTerrain($chunk), igbinary_unserialize($task->getResult()));

            foreach ($chunks as $hash => $chunk) {
                World::getXZ($hash, $x, $z);

                $this->world->setChunk($x, $z, $chunk);
            }
        });

        BackgroundTaskPool::getInstance()->submitTask($task);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function isTaken(): bool
    {
        return $this->taken;
    }

    public function setTaken(bool $taken): void
    {
        $this->taken = $taken;
    }

    public function getCenter(): Vector3
    {
        return $this->center;
    }

    public function getMax(): Vector3
    {
        return $this->max;
    }

    public function getMin(): Vector3
    {
        return $this->min;
    }

    public function isWithin(Vector3 $position): bool
    {
        return $position->x >= $this->min->x && $position->x <= $this->max->x && $position->z >= $this->min->z && $position->z <= $this->max->z && $position->y >= $this->min->y && $this->height >= $position->y;
    }
}
