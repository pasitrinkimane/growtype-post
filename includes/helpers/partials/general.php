<?php

function growtype_post_merge_arrays_recursively($array1, $array2) {
    foreach ($array2 as $key => $value) {
        if (array_key_exists($key, $array1)) {
            // If both arrays have the same key
            if (is_array($array1[$key]) && is_array($value)) {
                // Recursively merge arrays
                $array1[$key] = growtype_post_merge_arrays_recursively($array1[$key], $value);
            } else {
                // Prefer value from $array1 but ensure no duplicates
                $array1[$key] = $array1[$key];
            }
        } else {
            // Add keys/values from $array2 to $array1 if not present
            $array1[$key] = $value;
        }
    }

    // Handle indexed arrays (combine without duplicates)
    if (array_keys($array1) === range(0, count($array1) - 1) || array_keys($array2) === range(0, count($array2) - 1)) {
        $array1 = array_values(array_unique(array_merge($array1, $array2)));
    }

    return $array1;
}

