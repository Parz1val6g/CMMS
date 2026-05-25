<?php

namespace Tests\Feature\Services;

use App\Core\Services\CacheManager;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CacheManagerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_get_stores_and_returns_cached_value(): void
    {
        $key = CacheManager::make('so', 'list', 'a');
        $callCount = 0;

        $value1 = CacheManager::get($key, function () use (&$callCount) {
            $callCount++;
            return ['order1', 'order2'];
        });

        $this->assertEquals(1, $callCount);
        $this->assertEquals(['order1', 'order2'], $value1);

        $value2 = CacheManager::get($key, function () use (&$callCount) {
            $callCount++;
            return ['different'];
        });

        $this->assertEquals(1, $callCount);
        $this->assertEquals(['order1', 'order2'], $value2);
    }

    public function test_invalidate_pattern_removes_matching_keys(): void
    {
        $key1 = CacheManager::make('so', 'list', 'a');
        $key2 = CacheManager::make('so', 'item', 'abc');
        $key3 = CacheManager::make('tickets', 'list', 'b');

        $callCount = 0;
        CacheManager::get($key1, function () use (&$callCount) { $callCount++; return ['v1']; });
        $this->assertEquals(1, $callCount);

        CacheManager::get($key2, function () use (&$callCount) { $callCount++; return ['v2']; });
        $this->assertEquals(2, $callCount);

        CacheManager::get($key3, function () use (&$callCount) { $callCount++; return ['v3']; });
        $this->assertEquals(3, $callCount);

        CacheManager::invalidatePattern('so');

        $getCount = 0;
        CacheManager::get($key1, function () use (&$getCount) { $getCount++; return ['fresh']; });
        $this->assertEquals(1, $getCount, 'so:list should be a cache miss');

        $getCount2 = 0;
        CacheManager::get($key2, function () use (&$getCount2) { $getCount2++; return ['fresh']; });
        $this->assertEquals(1, $getCount2, 'so:item should be a cache miss');

        $getCount3 = 0;
        CacheManager::get($key3, function () use (&$getCount3) { $getCount3++; return ['fresh']; });
        $this->assertEquals(0, $getCount3, 'tickets should still be cached');
    }

    public function test_no_filesystem_calls_in_invalidate_pattern(): void
    {
        $key = CacheManager::make('testx', 'list', 'x');

        $count = 0;
        CacheManager::get($key, function () use (&$count) { $count++; return 'value'; });
        $this->assertEquals(1, $count, 'First get should call callback');

        $count = 0;
        CacheManager::get($key, function () use (&$count) { $count++; return 'cached'; });
        $this->assertEquals(0, $count, 'Second get should hit cache');

        CacheManager::invalidatePattern('testx');

        $count = 0;
        $result = CacheManager::get($key, function () use (&$count) { $count++; return 'fresh'; });
        $this->assertEquals(1, $count, 'After invalidation, get should call callback again');
        $this->assertEquals('fresh', $result);
    }

    public function test_invalidate_model_specific_by_id(): void
    {
        $key = CacheManager::make('equipment', 'item', 'eq-123');

        $count = 0;
        CacheManager::get($key, function () use (&$count) { $count++; return 'cached'; });
        $this->assertEquals(1, $count, 'First get should call callback');

        $count = 0;
        CacheManager::get($key, function () use (&$count) { $count++; return 'cached2'; });
        $this->assertEquals(0, $count, 'Second get should hit cache');

        CacheManager::invalidate('equipment', 'item', 'eq-123');

        $count = 0;
        CacheManager::get($key, function () use (&$count) { $count++; return 'fresh'; });
        $this->assertEquals(1, $count, 'After invalidate, get should call callback again');
    }

    public function test_invalidate_model_without_id_flushes_all(): void
    {
        $key1 = CacheManager::make('equipment_x', 'list', 'z1');
        $key2 = CacheManager::make('tickets_x', 'list', 'z2');

        $count1 = 0; $count2 = 0;
        CacheManager::get($key1, function () use (&$count1) { $count1++; return 'val1'; });
        CacheManager::get($key2, function () use (&$count2) { $count2++; return 'val2'; });
        $this->assertEquals(1, $count1);
        $this->assertEquals(1, $count2);

        CacheManager::invalidate('equipment_x');

        $count1 = 0;
        CacheManager::get($key1, function () use (&$count1) { $count1++; return 'fresh1'; });
        $this->assertEquals(1, $count1, 'After flush, get should call callback for key1');

        $count2 = 0;
        CacheManager::get($key2, function () use (&$count2) { $count2++; return 'fresh2'; });
        $this->assertEquals(1, $count2, 'After flush, get should call callback for key2');
    }
}
