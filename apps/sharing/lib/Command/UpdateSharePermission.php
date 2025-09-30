<?php

/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace OCA\Sharing\Command;

use OCP\Sharing\Model\SharePermission;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class UpdateSharePermission extends SharingBase {
	#[\Override]
	public function configure(): void {
		$this
			->setName('sharing:update-share-permission')
			->setDescription('Update a permission of a share')
			->addArgument('id', InputArgument::REQUIRED, 'Share ID')
			->addArgument('type', InputArgument::REQUIRED, 'Permission type')
			->addArgument('enabled', InputArgument::REQUIRED, 'Permission enabled. Only takes "true" or "false".');
	}

	#[\Override]
	public function execute(InputInterface $input, OutputInterface $output): int {
		return $this->wrapExecution($output, function () use ($input): string {
			$id = (string)$input->getArgument('id');
			$type = (string)$input->getArgument('type');
			$enabled = (string)$input->getArgument('enabled') === 'true';

			$this->manager->updateSharePermission($this->accessContext, $id, new SharePermission($type, $enabled));
			return $id;
		});
	}
}
