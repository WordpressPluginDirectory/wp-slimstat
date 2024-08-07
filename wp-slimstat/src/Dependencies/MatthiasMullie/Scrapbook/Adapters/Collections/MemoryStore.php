<?php

namespace SlimStat\Dependencies\MatthiasMullie\Scrapbook\Adapters\Collections;

use SlimStat\Dependencies\MatthiasMullie\Scrapbook\Adapters\Collections\Utils\PrefixKeys;
use SlimStat\Dependencies\MatthiasMullie\Scrapbook\Adapters\MemoryStore as Adapter;
use ReflectionObject;

/**
 * MemoryStore adapter for a subset of data.
 *
 * @author Matthias Mullie <scrapbook@mullie.eu>
 * @copyright Copyright (c) 2014, Matthias Mullie. All rights reserved
 * @license LICENSE MIT
 */
class MemoryStore extends PrefixKeys
{
    /**
     * @param string $name
     */
    public function __construct(Adapter $cache, $name)
    {
        parent::__construct($cache, $name.':');
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        /*
         * It's not done to use ReflectionObject, but:
         * - I *really* don't want to expose $cache->items publicly
         * - This is very specific to MemoryStore implementation, it can assume
         *   these kind of implementation details (like how it's ok for a child
         *   to use protected methods - this just can't be a subclass for
         *   practical reasons, but it mostly acts like one)
         * - Reflection is not the most optimized thing, but that doesn't matter
         *   too much for MemoryStore, which is not a *real* cache
         */
        $object = new \ReflectionObject($this->cache);
        $property = $object->getProperty('items');
        $property->setAccessible(true);
        $items = $property->getValue($this->cache);

        foreach ($items as $key => $value) {
            if (0 === strpos($key, $this->prefix)) {
                $this->cache->delete($key);
            }
        }

        return true;
    }
}
