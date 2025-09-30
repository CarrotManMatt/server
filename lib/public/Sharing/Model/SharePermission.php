<?php

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace OCP\Sharing\Model;

use OCA\Sharing\ResponseDefinitions;
use OCP\Server;
use OCP\Sharing\Exception\ShareInvalidException;
use OCP\Sharing\IRegistry;
use OCP\Sharing\ISharePermission;
use RuntimeException;

/**
 * @psalm-import-type SharingPermission from ResponseDefinitions
 */
final readonly class SharePermission {
	public function __construct(
		/** @var class-string<ISharePermission> $type */
		public string $type,
		public bool $enabled,
	) {
		/** @psalm-suppress DocblockTypeContradiction */
		if ($type === '') {
			throw new RuntimeException('The type is empty.');
		}
	}

	/**
	 * @return SharingPermission
	 */
	public function format(): array {
		if (($permissionType = (Server::get(IRegistry::class)->getPermissions()[$this->type] ?? null)) === null) {
			throw new ShareInvalidException('The permission type is not registered: ' . $this->type);
		}

		return [
			'type' => $this->type,
			'display_name' => $permissionType->getDisplayName(),
			'category' => $permissionType->getCategory(),
			'enabled' => $this->enabled,
		];
	}
}
