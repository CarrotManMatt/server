<?php

/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace OCP\Sharing\Model;

use OCA\Sharing\ResponseDefinitions;
use OCP\Server;
use OCP\Sharing\Exception\ShareInvalidException;
use OCP\Sharing\Exception\ShareInvalidPropertyValueException;
use OCP\Sharing\IRegistry;
use OCP\Sharing\ISharePermission;
use OCP\Sharing\IShareRecipientType;
use OCP\Sharing\IShareSourceType;

// TODO: Move all validation to only run on insert and updates

/**
 * @psalm-import-type SharingShare from ResponseDefinitions
 */
final readonly class Share {
	public function __construct(
		/** @var non-empty-string $id */
		public string $id,
		public ShareOwner $owner,
		/** @var non-negative-int $lastUpdated Unix time in milliseconds */
		public int $lastUpdated,
		public ShareState $state,
		/** @var list<ShareSource> $sources */
		public array $sources,
		/** @var list<ShareRecipient> $recipients */
		public array $recipients,
		/** @var list<ShareProperty> */
		public array $properties,
		/** @var list<SharePermission> */
		public array $permissions,
		bool $skipValidation = false,
	) {
		// TODO: Still do some sanity checking
		if ($skipValidation) {
			return;
		}

		// TODO: Some of these might need to be skipped when loading existing shares from the DB

		/** @psalm-suppress DocblockTypeContradiction */
		if ($id === '') {
			throw new ShareInvalidException('The id is empty.');
		}

		/** {@see \OC\Snowflake\Decoder::decode} */
		if (!ctype_digit($id)) {
			throw new ShareInvalidException('The id is not a valid Snowflake ID.');
		}

		/** @psalm-suppress DocblockTypeContradiction */
		if ($lastUpdated < 0) {
			throw new ShareInvalidException('The last updated is negative.');
		}

		$registry = Server::get(IRegistry::class);

		if (!array_is_list($sources)) {
			throw new ShareInvalidException('The sources are not a list.');
		}

		if ((!defined('PHPUNIT_RUN') || !PHPUNIT_RUN) && count($sources) > 1) {
			throw new ShareInvalidException('More than one source is currently not allowed for legacy compatibility.');
		}

		/** @var array<class-string<IShareSourceType>, bool> $shareSourceTypes */
		$shareSourceTypes = [];
		$sourceTypes = $registry->getSourceTypes();
		foreach ($sources as $source) {
			if (!isset($sourceTypes[$source->type])) {
				throw new ShareInvalidException('The source type is not registered: ' . $source->type);
			}

			if (!$sourceTypes[$source->type]->validateSource($this->owner->getUser(), $source->value)) {
				throw new ShareInvalidException('The source ' . $source->value . ' for ' . $source->type . ' is not valid.');
			}

			$shareSourceTypes[$source->type] = true;
		}

		$shareSourceTypes = array_keys($shareSourceTypes);

		if (!array_is_list($recipients)) {
			throw new ShareInvalidException('The recipients are not a list.');
		}

		if ((!defined('PHPUNIT_RUN') || !PHPUNIT_RUN) && count($recipients) > 1) {
			throw new ShareInvalidException('More than one recipien is currently not allowed for legacy compatibility.');
		}

		/** @var array<class-string<IShareRecipientType>, bool> $shareRecipientTypes */
		$shareRecipientTypes = [];
		$recipientTypes = $registry->getRecipientTypes();
		foreach ($recipients as $recipient) {
			if (!isset($recipientTypes[$recipient->type])) {
				throw new ShareInvalidException('The recipient type is not registered: ' . $recipient->type);
			}

			if (!$recipientTypes[$recipient->type]->validateRecipient($this->owner->getUser(), $recipient->value)) {
				throw new ShareInvalidException('The recipient ' . $recipient->value . ' for ' . $recipient->type . ' is not valid.');
			}

			$shareRecipientTypes[$recipient->type] = true;
		}

		$shareRecipientTypes = array_keys($shareRecipientTypes);

		$registryProperties = $registry->getProperties();
		foreach ($properties as $property) {
			// TODO: Instead of failing we might need to strip them silently, in case the app that provided the property is disabled now.
			if (array_intersect($registry->getSourceTypesCompatibleWithProperty($property->type), $shareSourceTypes) === []) {
				throw new ShareInvalidException('The property type is not compatible with any of the source types of the share: ' . var_export($property->type, true));
			}

			if (array_intersect($registry->getRecipientTypesCompatibleWithProperty($property->type), $shareRecipientTypes) === []) {
				throw new ShareInvalidException('The property type is not compatible with any of the recipient types of the share: ' . var_export($property->type, true));
			}

			if ($property->value !== null && ($message = $registryProperties[$property->type]->validateValue($property->value)) !== true) {
				// TODO: Catch in controller
				throw new ShareInvalidPropertyValueException($property->type, $message);
			}
		}

		/** @var array<class-string<ISharePermission>, bool> $shareSourceTypePermissions */
		$shareSourceTypePermissions = [];
		$sourceTypePermissions = $registry->getSourceTypePermissions();
		foreach ($shareSourceTypes as $shareSourceType) {
			foreach (($sourceTypePermissions[$shareSourceType] ?? []) as $permissionClass) {
				$shareSourceTypePermissions[$permissionClass] = true;
			}
		}

		$shareSourceTypePermissions = array_keys($shareSourceTypePermissions);

		foreach ($permissions as $permission) {
			if (!in_array($permission->type, $shareSourceTypePermissions, true)) {
				throw new ShareInvalidException('The permission is not compatible with any of the source types of the share: ' . var_export($permission->type, true));
			}
		}
	}

	/**
	 * @return SharingShare
	 */
	public function format(): array {
		return [
			'id' => $this->id,
			'owner' => $this->owner->format(),
			'last_updated' => $this->lastUpdated,
			'state' => $this->state->value,
			'sources' => ShareSource::formatMultiple($this->sources),
			'recipients' => ShareRecipient::formatMultiple($this->recipients),
			'properties' => array_map(static fn (ShareProperty $property): array => $property->format(), $this->properties),
			'permissions' => array_map(static fn (SharePermission $permission): array => $permission->format(), $this->permissions),
		];
	}
}
