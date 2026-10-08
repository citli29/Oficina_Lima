<?php
declare(strict_types=1);

function normalize(string $str): string
{
    $str = mb_strtolower($str, 'UTF-8');

    $str = strtr($str, [
        'á'=>'a','à'=>'a','â'=>'a','ã'=>'a','ä'=>'a',
        'é'=>'e','è'=>'e','ê'=>'e','ë'=>'e',
        'í'=>'i','ì'=>'i','î'=>'i','ï'=>'i',
        'ó'=>'o','ò'=>'o','ô'=>'o','õ'=>'o','ö'=>'o',
        'ú'=>'u','ù'=>'u','û'=>'u','ü'=>'u',
        'ç'=>'c','-'=>'',
    ]);

    // Keep only letters, digits and spaces
    $str = preg_replace('/[^a-z0-9 ]+/', ' ', $str);

    // Collapse multiple spaces
    $str = preg_replace('/\s+/', ' ', $str);

    return trim($str);
}

// A plate for searching/comparing: like normalize(), and without spaces
// either — "13 13 SR", "13-13-SR" and "1313sr" are all "1313sr".
function normalizePlate(string $str): string
{
    return str_replace(' ', '', normalize($str));
}

?>
