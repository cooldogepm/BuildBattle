<?php

declare(strict_types=1);

namespace cooldogedev\BuildBattle\async\world;

use cooldogedev\BuildBattle\async\ClosureBackgroundTask;
use pocketmine\world\format\Chunk;
use pocketmine\world\format\io\FastChunkSerializer;
use pocketmine\world\World;

final class AsyncSetBlocks extends ClosureBackgroundTask
{
    protected string $chunks;

    public function __construct(array $chunks, protected int $block, protected int $minX, protected int $maxX, protected int $minZ, protected int $maxZ, protected int $y)
    {
        $this->chunks = igbinary_serialize(array_map(fn(Chunk $chunk) => FastChunkSerializer::serializeTerrain($chunk), $chunks));
    }

    public function onRun(): void
    {
        $chunks = [];

        // Deserialize chunks
        foreach (igbinary_unserialize($this->chunks) as $hash => $chunk) {
            $chunks[$hash] = FastChunkSerializer::deserializeTerrain($chunk);
        }

        // Set blocks
        for ($x = $this->minX; $x <= $this->maxX; $x++) {
            for ($z = $this->minZ; $z <= $this->maxZ; $z++) {
                $chunk = $chunks[World::chunkHash($x >> Chunk::COORD_BIT_SIZE, $z >> Chunk::COORD_BIT_SIZE)];

                $chunk->setFullBlock($x & Chunk::COORD_MASK, $this->y, $z & Chunk::COORD_MASK, $this->block);
            }
        }

        // Serialize chunks
        $this->setResult(igbinary_serialize(array_map(fn(Chunk $chunk) => FastChunkSerializer::serializeTerrain($chunk), $chunks)));
    }
}
