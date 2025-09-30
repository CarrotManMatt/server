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
use OCP\Sharing\IShareRecipientType;
use RuntimeException;

/**
 * @psalm-import-type SharingRecipient from ResponseDefinitions
 */
final readonly class ShareRecipient {
	public function __construct(
		/** @var class-string<IShareRecipientType> $type */
		public string $type,
		/** @var non-empty-string $value */
		public string $value,
		// TODO: Remove default value
		/** @var ?non-empty-string $instance */
		public ?string $instance = null,
	) {
		/** @psalm-suppress DocblockTypeContradiction */
		if ($type === '') {
			throw new RuntimeException('The type is empty.');
		}

		/** @psalm-suppress DocblockTypeContradiction */
		if ($value === '') {
			throw new RuntimeException('The value is empty.');
		}

		if ($instance !== null && !preg_match('/^https?:\/\//', $instance)) {
			throw new RuntimeException('The instance is not a valid absolute URL: ' . $instance);
		}
	}

	/**
	 * @return SharingRecipient
	 */
	public function format(bool $isUnique): array {
		$recipientType = Server::get(IRegistry::class)->getRecipientTypes()[$this->type];

		$displayName = $recipientType->getRecipientDisplayName($this->value) ?? $this->value;
		if (!$isUnique) {
			$displayName .= ' (' . $recipientType->getDisplayName() . ': ' . $this->value . ')';
		}

		$out = [
			'type' => $this->type,
			'value' => $this->value,
			'display_name' => $displayName,
		];

		if ($this->instance !== null) {
			$out['instance'] = $this->instance;
		}

		$icon = $recipientType->getRecipientIcon($this->value);
		if ($icon !== null) {
			$out['icon'] = $icon->format();
		}

		return $out;
	}

	/**
	 * @param list<self> $recipients
	 * @return list<SharingRecipient>
	 */
	public static function formatMultiple(array $recipients): array {
		$recipientTypes = Server::get(IRegistry::class)->getRecipientTypes();

		$recipientDisplayNames = [];
		foreach ($recipients as $recipient) {
			$displayName = $recipientTypes[$recipient->type]->getRecipientDisplayName($recipient->value) ?? $recipient->value;
			$recipientDisplayNames[$displayName] ??= 0;
			++$recipientDisplayNames[$displayName];
		}

		return array_map(static fn (ShareRecipient $recipient): array => $recipient->format($recipientDisplayNames[$recipientTypes[$recipient->type]->getRecipientDisplayName($recipient->value) ?? $recipient->value] === 1), $recipients);
	}
}
