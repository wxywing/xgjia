<?php
/**
 * 简易文件缓存类
 * 用于缓存数据库密集查询结果，减少页面加载时间
 * 
 * 用法:
 *   $cache = new Cache(__DIR__ . '/../../../cache');
 *   $data = $cache->get('race_detail_' . $id);
 *   if ($data === null) {
 *       $data = $this->raceModel->getRaceDetail($id);
 *       $cache->set('race_detail_' . $id, $data, 3600);
 *   }
 */
class Cache {
    private $dir;
    private $defaultTtl;

    public function __construct($dir, $defaultTtl = 3600) {
        $this->dir = rtrim($dir, '/');
        $this->defaultTtl = $defaultTtl;
        if (!is_dir($this->dir)) {
            mkdir($this->dir, 0755, true);
        }
    }

    /**
     * 读取缓存
     * @param string $key
     * @return mixed|null 过期或不存在返回 null
     */
    public function get($key) {
        $file = $this->filePath($key);
        if (!file_exists($file)) return null;
        $content = @file_get_contents($file);
        if ($content === false) return null;
        $data = @unserialize($content);
        if (!$data || !isset($data['expires'], $data['value'])) return null;
        if ($data['expires'] > 0 && time() > $data['expires']) {
            @unlink($file);
            return null;
        }
        return $data['value'];
    }

    /**
     * 写入缓存
     * @param string $key
     * @param mixed $value
     * @param int|null $ttl 秒，null 使用默认值
     */
    public function set($key, $value, $ttl = null) {
        $ttl = $ttl ?? $this->defaultTtl;
        $file = $this->filePath($key);
        $dir = dirname($file);
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $data = [
            'expires' => $ttl > 0 ? time() + $ttl : 0,
            'value' => $value,
        ];
        return file_put_contents($file, serialize($data), LOCK_EX) !== false;
    }

    /**
     * 删除缓存
     */
    public function delete($key) {
        $file = $this->filePath($key);
        if (file_exists($file)) @unlink($file);
    }

    /**
     * 按前缀清理缓存
     */
    public function clearPrefix($prefix) {
        $pattern = $this->dir . '/' . $this->sanitize($prefix) . '*';
        foreach (glob($pattern) as $f) @unlink($f);
    }

    private function filePath($key) {
        return $this->dir . '/' . $this->sanitize($key) . '.cache';
    }

    private function sanitize($key) {
        return preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $key);
    }
}
