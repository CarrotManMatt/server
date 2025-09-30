<?php

/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace OCP\Sharing;

use OCA\Sharing\ResponseDefinitions;
use OCP\Sharing\Exception\ShareInvalidException;
use OCP\Sharing\Exception\ShareNotFoundException;
use OCP\Sharing\Exception\ShareOperationNotAllowedException;
use OCP\Sharing\Model\Share;
use OCP\Sharing\Model\ShareAccessContext;
use OCP\Sharing\Model\SharePermission;
use OCP\Sharing\Model\ShareProperty;
use OCP\Sharing\Model\ShareRecipient;
use OCP\Sharing\Model\ShareSource;
use OCP\Sharing\Model\ShareState;

// TODO: Update throws annotations and catch them in the controller

/**
 * @psalm-import-type SharingShare from ResponseDefinitions
 * @psalm-import-type SharingPartialShare from ResponseDefinitions
 */
interface IManager {
	/**
	 * @param ?class-string<IShareRecipientType> $recipientTypeClass
	 * @param non-empty-string $query
	 * @param positive-int $limit
	 * @param non-negative-int $offset
	 * @return list<ShareRecipient>
	 * @throws ShareInvalidException
	 */
	public function searchRecipients(ShareAccessContext $accessContext, ?string $recipientTypeClass, string $query, int $limit, int $offset): array;

	public function createShare(ShareAccessContext $accessContext): string;

	/**
	 * @throws ShareOperationNotAllowedException
	 * @throws ShareNotFoundException
	 */
	public function updateShareState(ShareAccessContext $accessContext, string $id, ShareState $state): void;

	/**
	 * @throws ShareOperationNotAllowedException
	 * @throws ShareNotFoundException
	 * @throws ShareInvalidException
	 */
	public function addShareSource(ShareAccessContext $accessContext, string $id, ShareSource $source): void;

	/**
	 * @throws ShareOperationNotAllowedException
	 * @throws ShareNotFoundException
	 */
	public function removeShareSource(ShareAccessContext $accessContext, string $id, ShareSource $source): void;

	/**
	 * @throws ShareOperationNotAllowedException
	 * @throws ShareNotFoundException
	 * @throws ShareInvalidException
	 */
	public function addShareRecipient(ShareAccessContext $accessContext, string $id, ShareRecipient $recipient): void;

	/**
	 * @throws ShareOperationNotAllowedException
	 * @throws ShareNotFoundException
	 */
	public function removeShareRecipient(ShareAccessContext $accessContext, string $id, ShareRecipient $recipient): void;

	/**
	 * @throws ShareOperationNotAllowedException
	 * @throws ShareNotFoundException
	 * @throws ShareInvalidException
	 */
	public function updateShareProperty(ShareAccessContext $accessContext, string $id, ShareProperty $property): void;

	/**
	 * @throws ShareOperationNotAllowedException
	 * @throws ShareNotFoundException
	 * @throws ShareInvalidException
	 */
	public function updateSharePermission(ShareAccessContext $accessContext, string $id, SharePermission $permission): void;

	/**
	 * @throws ShareOperationNotAllowedException
	 * @throws ShareNotFoundException
	 */
	public function deleteShare(ShareAccessContext $accessContext, string $id): void;

	/**
	 * @throws ShareNotFoundException
	 */
	public function getShare(ShareAccessContext $accessContext, string $id): Share;

	/**
	 * @param ?class-string<IShareSourceType> $sourceType
	 * @return list<Share>
	 * @throws ShareInvalidException
	 */
	public function listShares(ShareAccessContext $accessContext, ?string $sourceType, ?string $lastShareID, ?int $limit): array;
}
