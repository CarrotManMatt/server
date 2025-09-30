<?php

/*
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace OCP\Sharing;

use OC\Core\AppInfo\Application;
use OCA\Sharing\ResponseDefinitions;
use OCP\L10N\IFactory;
use OCP\Server;

/**
 * @psalm-import-type SharingProperty from ResponseDefinitions
 * @psalm-import-type SharingPropertyString from ResponseDefinitions
 */
abstract readonly class ASharePropertyString implements IShareProperty {
	/**
	 * @return ?positive-int
	 */
	abstract public function getMinLength(): ?int;

	/**
	 * @return ?positive-int
	 */
	abstract public function getMaxLength(): ?int;

	#[\Override]
	public function validateValue(string $value): true|string {
		if (($minLength = $this->getMinLength()) !== null && mb_strlen($value) < $minLength) {
			return Server::get(IFactory::class)->get(Application::APP_ID)->t('Need at least ' . $minLength . ' characters.');
		}

		if (($maxLength = $this->getMaxLength()) !== null && mb_strlen($value) > $maxLength) {
			return Server::get(IFactory::class)->get(Application::APP_ID)->t('Provide ' . $maxLength . ' characters at most.');
		}

		return true;
	}

	/**
	 * @param SharingProperty $property
	 * @return SharingPropertyString
	 */
	#[\Override]
	public function format(array $property): array {
		if (($minLength = $this->getMinLength()) !== null) {
			$property['min_length'] = $minLength;
		}

		if (($maxLength = $this->getMaxLength()) !== null) {
			$property['max_length'] = $maxLength;
		}

		return $property;
	}
}
