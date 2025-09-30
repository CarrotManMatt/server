<?php

/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace OCA\Sharing\Command;

use OCP\Sharing\Model\ShareSource;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class RemoveShareSource extends SharingBase {
	#[\Override]
	public function configure(): void {
		$this
			->setName('sharing:remove-share-source')
			->setDescription('Remove a source from a share')
			->addArgument('id', InputArgument::REQUIRED, 'Share ID')
			->addArgument('type', InputArgument::REQUIRED, 'Source type')
			->addArgument('value', InputArgument::REQUIRED, 'Source value');
	}

	#[\Override]
	public function execute(InputInterface $input, OutputInterface $output): int {
		return $this->wrapExecution($output, function () use ($input): string {
			$id = (string)$input->getArgument('id');
			$type = (string)$input->getArgument('type');
			$value = (string)$input->getArgument('value');

			$this->manager->removeShareSource($this->accessContext, $id, new ShareSource($type, $value));
			return $id;
		});
	}
}
