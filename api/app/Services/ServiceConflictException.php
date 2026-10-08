<?php
declare(strict_types = 1);

namespace App\Services;

use InvalidArgumentException;

// 409 from patchService: someone else changed one of the fields this save
// changes. Carries which fields and the service as it is now, so the page
// can keep the user's other edits instead of reloading everything.
class ServiceConflictException extends InvalidArgumentException
{
	public function __construct(public readonly array $fields, public readonly array $service)
	{
		parent::__construct(
			"Este campo foi alterado por outro utilizador entretanto. Foi mantida a alteração mais recente.",
			409
		);
	}
}
