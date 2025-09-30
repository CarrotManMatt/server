<?php

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace OCP\Sharing\Model;

use OCA\Sharing\ResponseDefinitions;
use OCP\IUser;
use OCP\IUserManager;
use OCP\Server;
use OCP\Sharing\Exception\ShareInvalidException;
use RuntimeException;

/**
 * @psalm-import-type SharingOwner from ResponseDefinitions
 */
final readonly class ShareOwner {
	private IUser $user;

	public function __construct(
		/** @var non-empty-string $owner */
		public string $userId,
		// TODO: Remove default value
		/** @var ?non-empty-string $instance */
		public ?string $instance = null,
		bool $skipValidation = false,
	) {
		// TODO: Still do some sanity checking
		if ($skipValidation) {
			return;
		}

		/** @psalm-suppress DocblockTypeContradiction */
		if ($this->userId === '') {
			throw new ShareInvalidException('The userId is empty.');
		}

		// TODO: Will not work for remote owner
		$ownerUser = Server::get(IUserManager::class)->get($this->userId);
		if ($ownerUser === null) {
			throw new ShareInvalidException('The userId does not exist: ' . $this->userId);
		}

		$this->user = $ownerUser;

		if ($instance !== null && !preg_match('/^https?:\/\//', $instance)) {
			throw new RuntimeException('The instance is not a valid absolute URL: ' . $instance);
		}
	}

	public function getUser(): IUser {
		return $this->user;
	}

	/**
	 * @return SharingOwner
	 */
	public function format(): array {
		$out = [
			'user_id' => $this->userId,
			'display_name' => $this->user->getDisplayName(),
			'icon' => (new ShareIconURL(
				$this->user->getUserAvatarUrlLight(64),
				$this->user->getUserAvatarUrlDark(64),
			))->format(),
		];

		if ($this->instance !== null) {
			$out['instance'] = $this->instance;
		}

		return $out;
	}
}
