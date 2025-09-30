<?php

/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);


use OCA\Sharing\AppInfo\Application;
use OCA\Sharing\Capabilities;
use OCA\Sharing\Tests\TestSharePermissionCategory;
use OCA\Sharing\Tests\TestSharePermissionCategory2;
use OCA\Sharing\Tests\TestShareProperty;
use OCA\Sharing\Tests\TestShareProperty2;
use OCA\Sharing\Tests\TestShareRecipientType;
use OCA\Sharing\Tests\TestShareRecipientType2;
use OCA\Sharing\Tests\TestShareSourceType;
use OCA\Sharing\Tests\TestShareSourceType2;
use OCP\Server;
use OCP\Sharing\IRegistry;
use Test\TestCase;

final class CapabilitiesTest extends TestCase {
	private IRegistry $registry;

	private Capabilities $capabilities;

	#[\Override]
	public function setUp(): void {
		parent::setUp();

		$this->registry = Server::get(IRegistry::class);
		$this->registry->clear();

		$this->capabilities = Server::get(Capabilities::class);
	}

	#[\Override]
	protected function tearDown(): void {
		$this->registry->clear();

		parent::tearDown();
	}

	public function testGetCapabilities(): void {
		$this->registry->registerSourceType(new TestShareSourceType([]));
		$this->registry->registerSourceType(new TestShareSourceType2([]));
		$this->registry->registerRecipientType(new TestShareRecipientType([], [], []));
		$this->registry->registerRecipientType(new TestShareRecipientType2([], [], []));
		$this->registry->registerProperty(new TestShareProperty([]));
		$this->registry->registerPropertyCompatibleWithSourceType(TestShareProperty::class, TestShareSourceType::class);
		$this->registry->registerPropertyCompatibleWithRecipientType(TestShareProperty::class, TestShareRecipientType::class);
		$this->registry->registerProperty(new TestShareProperty2([]));
		$this->registry->registerPropertyCompatibleWithSourceType(TestShareProperty2::class, TestShareSourceType2::class);
		$this->registry->registerPropertyCompatibleWithRecipientType(TestShareProperty2::class, TestShareRecipientType2::class);
		$this->registry->registerPermissionCategory(new TestSharePermissionCategory());
		$this->registry->registerPermissionCategory(new TestSharePermissionCategory2());

		$this->assertEquals(
			[
				Application::APP_ID => [
					'api_versions' => ['v1'],
					'legacy' => [
						'max_sources' => 1,
						'max_recipients' => 1,
					],
					'permission_categories' => [
						[
							'type' => TestSharePermissionCategory::class,
							'display_name' => 'TestSharePermissionCategory',
						],
						[
							'type' => TestSharePermissionCategory2::class,
							'display_name' => 'TestSharePermissionCategory2',
						],
					],
				],
			],
			$this->capabilities->getCapabilities(),
		);
	}
}
