<?php
function dbErrorMessage(\PDOException $e): string
{
    $driverMessage = $e->errorInfo[2] ?? null;

    // SQLite's own text for a delete/update blocked by a reference. The
    // common cases have their own message (BEFORE DELETE triggers); this
    // covers anything left.
    if ($driverMessage === 'FOREIGN KEY constraint failed')
        return 'Não é possível concluir: este registo está associado a outros dados.';

    return $driverMessage !== null && $driverMessage !== ''
        ? $driverMessage
        : 'Erro ao processar o pedido na base de dados.';
}

// Status for an error response from the exception's code. Only a real
// HTTP error status (400-599) is used as-is: a PDOException's code is an
// SQLSTATE string like 'HY000', and (int)'HY000' is 0 — passed straight to
// http_response_code() that left the response at 200, so a failed read
// looked like an empty successful one. Anything else is a 500.
function httpStatusFromException(\Throwable $e): int
{
    $code = $e->getCode();

    return is_int($code) && $code >= 400 && $code <= 599 ? $code : 500;
}

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
