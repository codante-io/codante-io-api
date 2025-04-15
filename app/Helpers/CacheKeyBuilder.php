<?php

namespace App\Helpers;

class CacheKeyBuilder
{
    public static function buildCacheKey($key, $filters)
    {
        $filters = array_filter($filters, function ($filter) {
            return $filter !== "" && $filter !== null;
        });

        if (empty($filters)) {
            return $key;
        }

        return $key . '-' . implode('-', $filters);
    }
}
