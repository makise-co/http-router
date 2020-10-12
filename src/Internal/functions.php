<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Http\Router\Internal;

/**
 * @param bool $preserveIntegerKeys
 * @param mixed[] ...$arrays
 * @return mixed[]
 */
function mergeRecursive(bool $preserveIntegerKeys, array ...$arrays): array
{
    $result = [];

    foreach ($arrays as $array) {
        foreach ($array as $key => $value) {
            // Renumber integer keys as array_merge_recursive() does unless
            // $preserveIntegerKeys is set to TRUE. Note that PHP automatically
            // converts array keys that are integer strings (e.g., '1') to integers.
            if (\is_int($key) && !$preserveIntegerKeys) {
                $result[] = $value;
            } elseif (isset($result[$key]) && \is_array($result[$key]) && \is_array($value)) {
                $result[$key] = mergeRecursive($preserveIntegerKeys, $result[$key], $value);
            } else {
                $result[$key] = $value;
            }
        }
    }

    return $result;
}
