<?php 
function nullableInt(?int $value): ?int
{
    return $value === null ? null : (int) $value;
}

function parsePagination(array $get, int $defaultPerPage = 20, int $maxPerPage = 100): ?array
{
    if (!isset($get['p']) && !isset($get['u'])) {
        return null;
    }

    $page = max(1, (int) ($get['p'] ?? 1));

    $perPage = (int) ($get['u'] ?? $defaultPerPage);
    $perPage = max(1, min($maxPerPage, $perPage ?: $defaultPerPage));

    return ['page' => $page, 'per_page' => $perPage];
}

function parseSort(array $get): array
{
    $column = isset($get['sort']) && $get['sort'] !== '' ? $get['sort'] : null;
    $direction = (isset($get['dir']) && strtolower($get['dir']) === 'desc') ? 'DESC' : 'ASC';

    return ['column' => $column, 'direction' => $direction];
}
?>
