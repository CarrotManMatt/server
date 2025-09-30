<?php

/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);


namespace Tests\Core\Sharing\Property;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use OC\Core\Sharing\Property\ExpirationDateShareProperty;
use OCP\Sharing\Model\Share;
use OCP\Sharing\Model\ShareAccessContext;
use OCP\Sharing\Model\ShareOwner;
use OCP\Sharing\Model\ShareProperty;
use OCP\Sharing\Model\ShareState;
use PHPUnit\Framework\Attributes\Group;
use Test\TestCase;

#[Group(name: 'DB')]
final class ExpirationDateSharePropertyTest extends TestCase {
	private ExpirationDateShareProperty $property;

	#[\Override]
	public function setUp(): void {
		parent::setUp();

		$this->property = new ExpirationDateShareProperty();
	}

	private function createDummyShare(ShareProperty $property): Share {
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
			[$property],
			[],
			skipValidation: true,
		);
	}

	public function testIsFiltered(): void {
		$now = new DateTimeImmutable();
		$future = $now->add(new DateInterval('PT1M'))->format(DateTimeInterface::ATOM);
		$past = $now->sub(new DateInterval('PT1M'))->format(DateTimeInterface::ATOM);

		$this->assertFalse($this->property->isFiltered(new ShareAccessContext(), $this->createDummyShare(new ShareProperty($this->property::class, $future))));
		$this->assertTrue($this->property->isFiltered(new ShareAccessContext(), $this->createDummyShare(new ShareProperty($this->property::class, $now->format(DateTimeInterface::ATOM)))));
		$this->assertTrue($this->property->isFiltered(new ShareAccessContext(), $this->createDummyShare(new ShareProperty($this->property::class, $past))));
	}
}
