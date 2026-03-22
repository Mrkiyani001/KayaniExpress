<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;

class RedisBloomFilter
{
    /**
     * The Redis key for the Bloom filter bit array
     */
    protected string $key;

    /**
     * The number of hash functions to run
     */
    protected int $numHashes;

    /**
     * The size of the bit array
     */
    protected int $size;

    /**
     * Initialize the Bloom Filter
     * 
     * @param string $key The Redis key to store the bits under
     * @param int $size The maximum size of the bit array (default 100M bits)
     * @param int $numHashes Number of hash functions (default 5)
     */
    public function __construct(string $key = 'system:bloom_filter', int $size = 100000000, int $numHashes = 5)
    {
        $this->key = $key;
        $this->size = $size;
        $this->numHashes = $numHashes;
    }

    /**
     * Add an item to the Bloom filter
     * 
     * @param string $item
     * @return void
     */
    public function add(string|int $item): void
    {
        $offsets = $this->getOffsets($item);
        
        // Use Redis pipeline to set multiple bits in one go for maximum performance
        Redis::pipeline(function ($pipe) use ($offsets) {
            foreach ($offsets as $offset) {
                $pipe->setbit($this->key, $offset, 1);
            }
        });
    }

    /**
     * Check if an item "probably" exists in the Bloom filter
     * (False positives are possible, false negatives are impossible)
     * 
     * @param string $item
     * @return bool
     */
    public function has(string|int $item): bool
    {
        $offsets = $this->getOffsets($item);
        
        $pipeResults = Redis::pipeline(function ($pipe) use ($offsets) {
            foreach ($offsets as $offset) {
                $pipe->getbit($this->key, $offset);
            }
        });

        // If ANY bit is 0, the item absolutely was NOT added to the filter.
        foreach ($pipeResults as $bit) {
            if ($bit === 0) {
                return false;
            }
        }

        // All bits were 1, so the item PROBABLY exists
        return true;
    }

    /**
     * Clear the entire Bloom Filter
     * 
     * @return void
     */
    public function clear(): void
    {
        Redis::del($this->key);
    }

    /**
     * Calculate hash offsets for the given item
     * 
     * @param string $item
     * @return array
     */
  protected function getOffsets(string|int $item): array
{
    $item = (string) $item;
    $offsets = [];

    for ($i = 0; $i < $this->numHashes; $i++) {
        // Full 32-bit hash directly as unsigned int
        $hash = unpack('V', substr(md5($item . $i), 0, 4))[1];
        $offsets[] = $hash % $this->size;
    }

    return $offsets;
}
}
