<?php declare(strict_types=1);

namespace App\Services;

// ============================================================================
//  Orçamento notifications: THE ONE PLACE TO CHANGE THEM.
//
//  Sent by the front end after an orçamento is saved (Guardar) or its note is
//  changed: POST /api/services/{s_id}/budgets/{id}/notify  {"event": "..."},
//  and by the backend when the service page deletes one (DELETE ...?notify=1).
//
//  - Who gets it ............ TYPE_NAME (a notification_types.name)
//  - Which events exist ...... TITLES (event => notification title)
//                              'to_order' = a Guardar whose only change was
//                              ticking Pedir on products (sent by the service page)
//  - The text ................ message()
//  - Where clicking goes ..... url()   (now the Orçamentos page, /budgets)
//
//  `data` also carries service_id / budget_id / event, so a future page can
//  find the orçamento without parsing the url or the message. Notifications
//  already sent keep the url they were created with.
// ============================================================================
class BudgetNotification
{
	const TYPE_NAME = 'Escritório';

	const TITLES = [
		'created' => 'Orçamento criado',
		'edited'  => 'Orçamento editado',
		'deleted' => 'Orçamento apagado',
		'to_order' => 'Orçamento para pedir',
	];

	// Events only the backend sends (the delete itself, with ?notify=1),
	// not accepted by POST .../notify.
	const SERVER_ONLY_EVENTS = ['deleted'];

	// $serviceLabel: "AA-00-BB Opel Corsa" (or '' when the service has no car).
	public static function message(string $event, array $budget, int $s_id, string $serviceLabel): string
	{
		$title = trim((string) ($budget['title'] ?? '')) ?: 'Sem título';
		$verb = ['created' => 'criado', 'edited' => 'editado', 'deleted' => 'apagado', 'to_order' => 'com produtos para pedir'][$event] ?? $event;
		$service = "folha de serviço {$s_id}" . ($serviceLabel !== '' ? " - {$serviceLabel}" : '');

		$message = "Orçamento \"{$title}\" {$verb} na {$service}.";

		// Products marked "Pedir", by name, so the escritório sees what to
		// order without opening it (created / edited / to_order).
		if ($event !== 'deleted') {
			$products = $budget['products'] ?? [];
			$toOrder = array_values(array_filter($products, static fn ($p) => (int) ($p['to_order'] ?? 0) === 1));
			if ($toOrder) {
				$names = array_map(static fn ($p) => trim((string) ($p['product_name'] ?? '')) . ' (' . (int) ($p['quantity'] ?? 1) . ')', $toOrder);
				$message .= ' Pedir ' . count($toOrder) . ' de ' . count($products) . ': ' . implode(', ', $names) . '.';
			}
		}

		return $message;
	}

	// Opens the Orçamentos page with that orçamento outlined
	// (BudgetsDashboard reads ?highlight=). To open the service page
	// instead: return "services/{$s_id}?orcamento={$budgetId}";
	// A deleted one no longer exists, so that goes to its service.
	public static function url(string $event, int $s_id, int $budgetId): string
	{
		if ($event === 'deleted') return "services/{$s_id}";
		return "budgets?highlight={$budgetId}";
	}

	public static function data(string $event, int $s_id, int $budgetId): array
	{
		return [
			'url' => self::url($event, $s_id, $budgetId),
			'service_id' => $s_id,
			'budget_id' => $budgetId,
			'event' => $event,
		];
	}
}
