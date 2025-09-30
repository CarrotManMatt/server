<?php

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);


namespace Tests\Core\Sharing\Property;

use OC\Core\Sharing\Property\PasswordShareProperty;
use OCP\Security\IHasher;
use OCP\Server;
use OCP\Sharing\Model\Share;
use OCP\Sharing\Model\ShareAccessContext;
use OCP\Sharing\Model\ShareOwner;
use OCP\Sharing\Model\ShareProperty;
use OCP\Sharing\Model\ShareState;
use Test\TestCase;

final class PasswordSharePropertyTest extends TestCase {
	private PasswordShareProperty $property;

	#[\Override]
	public function setUp(): void {
		parent::setUp();

		$this->property = new PasswordShareProperty();
	}

	private function createDummyShare(?ShareProperty $property): Share {
		$properties = [];
		if ($property instanceof ShareProperty) {
			$properties[] = $property;
		}

		return new Share(
			'',
			new ShareOwner(
				'',
				skipValidation: true,
			),
			0,
			ShareState::Active,
			[],
			[],
			$properties,
			[],
			skipValidation: true,
		);
	}

	public function testIsFiltered(): void {
		$this->assertFalse($this->property->isFiltered(new ShareAccessContext(arguments: [$this->property::class => '123']), $this->createDummyShare(new ShareProperty($this->property::class, Server::get(IHasher::class)->hash('123')))));
		$this->assertFalse($this->property->isFiltered(new ShareAccessContext(arguments: [$this->property::class => '123']), $this->createDummyShare(new ShareProperty($this->property::class, null))));
		$this->assertFalse($this->property->isFiltered(new ShareAccessContext(arguments: [$this->property::class => '123']), $this->createDummyShare(null)));
		$this->assertTrue($this->property->isFiltered(new ShareAccessContext(arguments: [$this->property::class => '456']), $this->createDummyShare(new ShareProperty($this->property::class, Server::get(IHasher::class)->hash('123')))));
		$this->assertTrue($this->property->isFiltered(new ShareAccessContext(arguments: [$this->property::class => null]), $this->createDummyShare(new ShareProperty($this->property::class, Server::get(IHasher::class)->hash('123')))));
		$this->assertTrue($this->property->isFiltered(new ShareAccessContext(), $this->createDummyShare(new ShareProperty($this->property::class, Server::get(IHasher::class)->hash('123')))));
	}
}
