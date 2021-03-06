<?php
/**
 * <!--
 * This file is part of the adventure php framework (APF) published under
 * https://adventure-php-framework.org.
 *
 * The APF is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * The APF is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with the APF. If not, see http://www.gnu.org/licenses/lgpl-3.0.txt.
 * -->
 */
namespace APF\tools\cache\provider;

use APF\tools\cache\CacheBase;
use APF\tools\cache\CacheKey;
use APF\tools\cache\CacheProvider;
use APF\tools\cache\key\AdvancedCacheKey;

/**
 * Implements the cache reader for serialized php objects stored in an APC in-memory store.
 * <p/>
 * Supports both the SimpleCacheKey as well as the AdvancedCacheKey to store information.
 *
 * @author Christian Achatz
 * @version
 * Version 0.1, 07.01.2013<br />
 */
class ApcCacheProvider extends CacheBase implements CacheProvider {

   /**
    * The delimiter between namespace, key, and sub-key.
    *
    * @var string CACHE_KEY_DELIMITER
    */
   const CACHE_KEY_DELIMITER = '#';

   public function read(CacheKey $cacheKey) {

      $cacheContent = apcu_fetch($this->getCacheIdentifier($cacheKey));

      if ($cacheContent === false) {
         return null;
      }

      $unserialized = @unserialize($cacheContent);

      if ($unserialized !== false) {
         return $unserialized;
      } else {
         return null;
      }

   }

   /**
    * Calculates the cache identifier for read and write operations.
    *
    * @param CacheKey $cacheKey The applied cache key.
    *
    * @return string The cache identifier with respect for the given cache key (simple and advanced).
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 08.01.2013<br />
    */
   protected function getCacheIdentifier(CacheKey $cacheKey) {

      $identifier = $this->getConfigAttribute('Namespace');
      $identifier .= self::CACHE_KEY_DELIMITER . $cacheKey->getKey();

      if ($cacheKey instanceof AdvancedCacheKey) {
         $identifier .= self::CACHE_KEY_DELIMITER . $cacheKey->getSubKey();
      }

      return $identifier;
   }

   public function write(CacheKey $cacheKey, $object) {

      // write to cache (try to replace all the time)
      $identifier = $this->getCacheIdentifier($cacheKey);

      $serialized = @serialize($object);

      if ($serialized === false) {
         return false;
      }

      // try to replace...
      return apcu_store($identifier, $serialized, $this->getExpireTime($cacheKey));
   }

   public function clear(CacheKey $cacheKey = null) {

      // clear cache
      $namespace = $this->getConfigAttribute('Namespace');
      if ($cacheKey === null) {

         // clear entire namespace
         foreach ($this->getCacheEntriesByNamespace($namespace) as $key) {
            apcu_delete($key);
         }

         return true;

      } else {

         if ($cacheKey instanceof AdvancedCacheKey) {
            // clear al keys matching to the current main key
            foreach ($this->getCacheEntriesByNamespaceAndKey($namespace, $cacheKey->getKey()) as $key) {
               apcu_delete($key);
            }

            return true;
         } else {
            // delete dedicated entry
            return apcu_delete($namespace . self::CACHE_KEY_DELIMITER . $cacheKey->getKey());
         }
      }

   }

   /**
    * Gathers the entries of a given namespace.
    *
    * @param string $namespace The desired namespace to filter the entries.
    *
    * @return string[] The list of current cache entries.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 12.01.2013<br />
    */
   protected function getCacheEntriesByNamespace($namespace) {
      $namespaceEntries = [];
      foreach ($this->getCacheEntries() as $entry) {
         if (strpos($entry, $namespace . self::CACHE_KEY_DELIMITER) !== false) {
            $namespaceEntries[] = $entry;
         }
      }

      return $namespaceEntries;
   }

   /**
    * Gathers the entire list of cache entries and returns it.
    *
    * @return string[] The list of current cache entries.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 12.01.2013<br />
    * Version 0.2, 16.07.2015 (Added support for WIN implementation/variant of APC cache statistics)<br />
    */
   protected function getCacheEntries() {

      // ignore IDE warning, method exists as documented under http://php.net/manual/de/function.apcu-cache-info.php
      /** @noinspection PhpUndefinedFunctionInspection */
      $cacheInfo = apcu_cache_info();

      $entries = [];
      foreach ($cacheInfo['cache_list'] as $cacheEntryInfo) {
         // distinguish between WIN vs. LINUX implementation of APC API.
         if (isset($cacheEntryInfo['info'])) {
            $entries[] = $cacheEntryInfo['info'];
         } else {
            $entries[] = $cacheEntryInfo['key'];
         }

      }

      return $entries;
   }

   /**
    * Gathers the entries of a given namespace and main cache key.
    *
    * @param string $namespace The desired namespace to filter the entries.
    * @param string $key The main cache key.
    *
    * @return string[] The list of current cache entries.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 12.01.2013<br />
    */
   protected function getCacheEntriesByNamespaceAndKey($namespace, $key) {
      $namespaceAndKeyEntries = [];
      foreach ($this->getCacheEntries() as $entry) {
         if (strpos($entry, $namespace . self::CACHE_KEY_DELIMITER . $key . self::CACHE_KEY_DELIMITER) !== false) {
            $namespaceAndKeyEntries[] = $entry;
         }
      }

      return $namespaceAndKeyEntries;
   }

}
