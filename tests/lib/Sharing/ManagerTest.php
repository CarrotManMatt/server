<?php

/*
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace lib\Sharing;

use OCP\Sharing\Model\Share;
use OCP\Sharing\Model\ShareAccessContext;
use OCP\Sharing\Model\SharePermission;
use OCP\Sharing\Model\ShareProperty;
use OCP\Sharing\Model\ShareRecipient;
use OCP\Sharing\Model\ShareSource;
use OCP\Sharing\Model\ShareState;
use PHPUnit\Framework\Attributes\Group;
use Test\Sharing\AbstractManagerTests;

#[Group(name: 'DB')]
final class ManagerTest extends AbstractManagerTests {
	#[\Override]
	protected function searchRecipients(ShareAccessContext $accessContext, ?string $recipientTypeClass, string $query, int $limit, int $offset): array {
		return ShareRecipient::formatMultiple($this->manager->searchRecipients($accessContext, $recipientTypeClass, $query, $limit, $offset));
	}

	#[\Override]
	protected function createShare(ShareAccessContext $accessContext): array {
		$id = $this->manager->createShare($accessContext);
		return $this->manager->getShare($accessContext, $id)->format();
	}

	#[\Override]
	protected function updateShareState(ShareAccessContext $accessContext, string $id, ShareState $state): array {
		$this->manager->updateShareState($accessContext, $id, $state);
		return $this->manager->getShare($accessContext, $id)->format();
	}

	#[\Override]
	protected function addShareSource(ShareAccessContext $accessContext, string $id, ShareSource $source): array {
		$this->manager->addShareSource($accessContext, $id, $source);
		return $this->manager->getShare($accessContext, $id)->format();
	}

	#[\Override]
	protected function removeShareSource(ShareAccessContext $accessContext, string $id, ShareSource $source): array {
		$this->manager->removeShareSource($accessContext, $id, $source);
		return $this->manager->getShare($accessContext, $id)->format();
	}

	#[\Override]
	protected function addShareRecipient(ShareAccessContext $accessContext, string $id, ShareRecipient $recipient): array {
		$this->manager->addShareRecipient($accessContext, $id, $recipient);
		return $this->manager->getShare($accessContext, $id)->format();
	}

	#[\Override]
	protected function removeShareRecipient(ShareAccessContext $accessContext, string $id, ShareRecipient $recipient): array {
		$this->manager->removeShareRecipient($accessContext, $id, $recipient);
		return $this->manager->getShare($accessContext, $id)->format();
	}

	#[\Override]
	protected function updateShareProperty(ShareAccessContext $accessContext, string $id, ShareProperty $property): array {
		$this->manager->updateShareProperty($accessContext, $id, $property);
		return $this->manager->getShare($accessContext, $id)->format();
	}

	#[\Override]
	protected function updateSharePermission(ShareAccessContext $accessContext, string $id, SharePermission $permission): array {
		$this->manager->updateSharePermission($accessContext, $id, $permission);
		return $this->manager->getShare($accessContext, $id)->format();
	}

	#[\Override]
	protected function deleteShare(ShareAccessContext $accessContext, string $id): void {
		$this->manager->deleteShare($accessContext, $id);
	}

	#[\Override]
	protected function getShare(ShareAccessContext $accessContext, string $id): array {
		return $this->manager->getShare($accessContext, $id)->format();
	}

	/**
	 * @return list<array<string, mixed>>
	 */
	#[\Override]
	protected function listShares(ShareAccessContext $accessContext, ?string $sourceType, ?string $lastShareID, ?int $limit): array {
		return array_map(static fn (Share $share): array => $share->format(), $this->manager->listShares($accessContext, $sourceType, $lastShareID, $limit));
	}
}
