<?php 
function nullableInt(?int $value): ?int
{
    return $value === null ? null : (int) $value;
}
?>
