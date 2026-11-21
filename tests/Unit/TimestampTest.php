<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use App\Classes\LDAP\Attribute\Syntax\Timestamp;

class TimestampTest extends TestCase
{
	public static function values(): array
	{
		return [
			'openldap' => ['20250803032604Z','2025-08-03 03:26:04.000000 +00:00'],
			'apacheds fraction' => ['20250803033900.291Z','2025-08-03 03:39:00.291000 +00:00'],
			'no seconds (ppolicy permanent lock)' => ['000001010000Z','0000-01-01 00:00:00.000000 +00:00'],
			'no minutes' => ['2025080303Z','2025-08-03 03:00:00.000000 +00:00'],
			'offset hours and minutes' => ['20250803112604+0800','2025-08-03 11:26:04.000000 +08:00'],
			'offset hours' => ['20250803112604-05','2025-08-03 11:26:04.000000 -05:00'],
			'lldap iso8601' => ['2026-01-30T23:05:17.578672322+00:00','2026-01-30 23:05:17.578672 +00:00'],
		];
	}

	#[DataProvider('values')]
	public function test_parse(string $value,string $expected): void
	{
		$this->assertEquals($expected,Timestamp::parse($value)?->format('Y-m-d H:i:s.u P'));
	}

	public function test_parse_invalid(): void
	{
		$this->assertNull(Timestamp::parse(NULL));
		$this->assertNull(Timestamp::parse(''));
		$this->assertNull(Timestamp::parse('FALSE'));
		$this->assertNull(Timestamp::parse('20251301000000Z'));
	}
}
