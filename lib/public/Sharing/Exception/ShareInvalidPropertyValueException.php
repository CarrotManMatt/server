<?php

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace OCP\Sharing\Exception;

use OCP\Sharing\IShareProperty;

final class ShareInvalidPropertyValueException extends ShareInvalidException {
	public function __construct(
		/** @var class-string<IShareProperty> $propertyClass */
		readonly public string $propertyClass,
		string $message,
	) {
		parent::__construct($message);
	}
}
