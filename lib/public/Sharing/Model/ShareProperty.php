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
use OCP\Sharing\IShareProperty;
use RuntimeException;

/**
 * @psalm-import-type SharingPropertyDate from ResponseDefinitions
 * @psalm-import-type SharingPropertyEnum from ResponseDefinitions
 * @psalm-import-type SharingPropertyBoolean from ResponseDefinitions
 * @psalm-import-type SharingPropertyPassword from ResponseDefinitions
 * @psalm-import-type SharingPropertyString from ResponseDefinitions
 */
final readonly class ShareProperty {
	public function __construct(
		/** @var class-string<IShareProperty> $type */
		public string $type,
		public ?string $value,
	) {
		/** @psalm-suppress DocblockTypeContradiction */
		if ($type === '') {
			throw new RuntimeException('The type is empty.');
		}
	}

	/**
	 * @return SharingPropertyDate|SharingPropertyEnum|SharingPropertyBoolean|SharingPropertyPassword|SharingPropertyString
	 */
	public function format(): array {
		if (($propertyType = (Server::get(IRegistry::class)->getProperties()[$this->type] ?? null)) === null) {
			throw new ShareInvalidException('The property type is not registered: ' . $this->type);
		}

		$out = [
			'type' => $this->type,
			'display_name' => $propertyType->getDisplayName(),
			'priority' => $propertyType->getPriority(),
			'required' => $propertyType->getRequired(),
			'value' => $this->value,
		];
		if (($hint = $propertyType->getHint()) !== null) {
			$out['hint'] = $hint;
		}

		return $propertyType->format($out);
	}
}
