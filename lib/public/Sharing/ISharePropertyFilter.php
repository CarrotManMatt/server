<?php

/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace OCP\Sharing;

use OCP\Sharing\Model\Share;
use OCP\Sharing\Model\ShareAccessContext;

interface ISharePropertyFilter extends IShareProperty {
	/**
	 * Evaluates if a share should be filtered out.
	 *
	 * The method is called for every share, regardless if the property itself is present or not.
	 */
	public function isFiltered(ShareAccessContext $accessContext, Share $share): bool;
}
