<?php
namespace App\Core;

/**
 * 足环号标准化工具类
 * 
 * 用于跨数据集（pigeons ↔ race_results）匹配足环号
 */
class RingNormalizer
{
    /**
     * 标准化足环号
     * 
     * 规则：
     * 1. 去除所有空格
     * 2. 统一分隔符（-、_、空格）→ -
     * 3. 转大写
     * 
     * @param string $ring 原始足环号
     * @return string 标准化后的足环号
     */
    public static function normalize(string $ring): string
    {
        if (empty($ring)) {
            return '';
        }
        
        // 1. 去除所有空格
        $normalized = preg_replace('/\s+/', '', $ring);
        
        // 2. 统一分隔符：将各种分隔符（-、_、空格）统一为 -
        $normalized = preg_replace('/[_\s]+/', '-', $normalized);
        
        // 3. 转大写
        $normalized = strtoupper($normalized);
        
        return $normalized;
    }
    
    /**
     * 批量标准化足环号
     * 
     * @param array $rings 原始足环号数组
     * @return array 标准化后的足环号数组
     */
    public static function normalizeBatch(array $rings): array
    {
        return array_map([self::class, 'normalize'], $rings);
    }
    
    /**
     * 比较两个足环号是否匹配（标准化后比较）
     * 
     * @param string $ring1
     * @param string $ring2
     * @return bool
     */
    public static function matches(string $ring1, string $ring2): bool
    {
        return self::normalize($ring1) === self::normalize($ring2);
    }
    
    /**
     * 测试标准化函数
     * 
     * @return array 测试结果
     */
    public static function test(): array
    {
        $testCases = [
            '2025-01-0702812' => '2025-01-0702812',
            '2025 01 0702812' => '2025-01-0702812',
            '2025_01_0702812' => '2025-01-0702812',
            '2025-01-0702812' => '2025-01-0702812',
        ];
        
        $results = [];
        foreach ($testCases as $input => $expected) {
            $actual = self::normalize($input);
            $results[] = [
                'input' => $input,
                'expected' => $expected,
                'actual' => $actual,
                'passed' => $actual === $expected,
            ];
        }
        
        return $results;
    }
}
