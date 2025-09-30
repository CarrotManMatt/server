<?php

/*
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace OCP\Sharing;

abstract readonly class ASharePropertyBoolean extends ASharePropertyEnum {
	/**
	 * @return non-empty-list<string>
	 */
	#[\Override]
	public function getValidValues(): array {
		return ['true', 'false'];
	}
}
