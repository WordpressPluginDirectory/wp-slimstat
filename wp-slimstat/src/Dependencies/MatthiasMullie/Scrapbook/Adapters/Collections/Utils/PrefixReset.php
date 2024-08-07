<?php

namespace SlimStat\Dependencies\MatthiasMullie\Scrapbook\Adapters\Collections\Utils;

use SlimStat\Dependencies\MatthiasMullie\Scrapbook\KeyValueStore;

/**
 * @author Matthias Mullie <scrapbook@mullie.eu>
 * @copyright Copyright (c) 2014, Matthias Mullie. All rights reserved
 * @license LICENSE MIT
 */
class PrefixReset extends PrefixKeys
{
    /**
     * @var string
     */
    protected $collection;

    /**
     * @param string $name
     */
    public function __construct(KeyValueStore $cache, $name)
    {
        $this->cache = $cache;
        $this->collection = $name;
        parent::__construct($cache, $this->getPrefix());
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        $index = $this->cache->increment($this->collection);
        $this->setPrefix($this->collection.':'.$index.':');

        return false !== $index;
    }

    /**
     * @return string
     */
    protected function getPrefix()
    {
        /*
         * It's easy enough to just set a prefix to be used, but we can not
         * flush only a prefix!
         * Instead, we'll generate a unique prefix key, based on some name.
         * If we want to flush, we just create a new prefix and use that one.
         */
        $index = $this->cache->get($this->collection);

        if (false === $index) {
            $index = $this->cache->set($this->collection, 1);
        }

        return $this->collection.':'.$index.':';
    }
}
