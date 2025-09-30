<?php

/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace OCA\Sharing\Command;

use OCP\Sharing\Model\ShareProperty;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class UpdateShareProperty extends SharingBase {
	#[\Override]
	public function configure(): void {
		$this
			->setName('sharing:update-share-property')
			->setDescription('Update a property of a share')
			->addArgument('id', InputArgument::REQUIRED, 'Share ID')
			->addArgument('type', InputArgument::REQUIRED, 'Property type')
			->addArgument('value', InputArgument::OPTIONAL, 'Property value. Omitting it will remove the value.');
	}

	#[\Override]
	public function execute(InputInterface $input, OutputInterface $output): int {
		return $this->wrapExecution($output, function () use ($input): string {
			$id = (string)$input->getArgument('id');
			$type = (string)$input->getArgument('type');
			$value = $input->getArgument('value');
			if ($value !== null) {
				$value = (string)$value;
			}

			$this->manager->updateShareProperty($this->accessContext, $id, new ShareProperty($type, $value));
			return $id;
		});
	}
}
