<?php

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace OCP\Sharing\Model;

use OCA\Sharing\ResponseDefinitions;
use OCP\Server;
use OCP\Sharing\IRegistry;
use OCP\Sharing\IShareSourceType;
use RuntimeException;

/**
 * @psalm-import-type SharingSource from ResponseDefinitions
 */
final readonly class ShareSource {
	public function __construct(
		/** @var class-string<IShareSourceType> $type */
		public string $type,
		/** @var non-empty-string $value */
		public string $value,
	) {
		/** @psalm-suppress DocblockTypeContradiction */
		if ($type === '') {
			throw new RuntimeException('The type is empty.');
		}

		/** @psalm-suppress DocblockTypeContradiction */
		if ($value === '') {
			throw new RuntimeException('The value is empty.');
		}
	}

	/**
	 * @return SharingSource
	 */
	public function format(bool $isUnique): array {
		$sourceType = Server::get(IRegistry::class)->getSourceTypes()[$this->type];

		$displayName = $sourceType->getSourceDisplayName($this->value) ?? $this->value;
		if (!$isUnique) {
			$displayName .= ' (' . $sourceType->getDisplayName() . ': ' . $this->value . ')';
		}

		return [
			'type' => $this->type,
			'value' => $this->value,
			'display_name' => $displayName,
		];
	}

	/**
	 * @param list<self> $sources
	 * @return list<SharingSource>
	 */
	public static function formatMultiple(array $sources): array {
		$sourceTypes = Server::get(IRegistry::class)->getSourceTypes();

		$sourceDisplayNames = [];
		foreach ($sources as $source) {
			$displayName = $sourceTypes[$source->type]->getSourceDisplayName($source->value) ?? $source->value;
			$sourceDisplayNames[$displayName] ??= 0;
			++$sourceDisplayNames[$displayName];
		}

		return array_map(static fn (ShareSource $source): array => $source->format($sourceDisplayNames[$sourceTypes[$source->type]->getSourceDisplayName($source->value) ?? $source->value] === 1), $sources);
	}
}
