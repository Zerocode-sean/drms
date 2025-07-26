<?php
// Advanced caching system for DRMS
// Implements multiple caching layers for optimal performance

class CacheManager {
    private $cache_dir;
    private $default_ttl = 3600; // 1 hour default
    
    public function __construct($cache_dir = null) {
        $this->cache_dir = $cache_dir ?: __DIR__ . '/../../../cache';
        $this->ensureCacheDirectory();
    }
    
    private function ensureCacheDirectory() {
        if (!is_dir($this->cache_dir)) {
            mkdir($this->cache_dir, 0755, true);
        }
        
        // Create subdirectories for different cache types
        $subdirs = ['api', 'queries', 'pages', 'user_data'];
        foreach ($subdirs as $subdir) {
            $path = $this->cache_dir . '/' . $subdir;
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
        }
    }
    
    public function get($key, $type = 'api') {
        $filepath = $this->getCacheFilePath($key, $type);
        
        if (!file_exists($filepath)) {
            return null;
        }
        
        $data = file_get_contents($filepath);
        $cache_data = json_decode($data, true);
        
        if (!$cache_data || !isset($cache_data['expires']) || $cache_data['expires'] < time()) {
            // Cache expired
            unlink($filepath);
            return null;
        }
        
        return $cache_data['data'];
    }
    
    public function set($key, $data, $ttl = null, $type = 'api') {
        $ttl = $ttl ?: $this->default_ttl;
        $filepath = $this->getCacheFilePath($key, $type);
        
        $cache_data = [
            'data' => $data,
            'created' => time(),
            'expires' => time() + $ttl
        ];
        
        return file_put_contents($filepath, json_encode($cache_data)) !== false;
    }
    
    public function delete($key, $type = 'api') {
        $filepath = $this->getCacheFilePath($key, $type);
        if (file_exists($filepath)) {
            return unlink($filepath);
        }
        return true;
    }
    
    public function clear($type = null) {
        if ($type) {
            $dir = $this->cache_dir . '/' . $type;
            if (is_dir($dir)) {
                $files = glob($dir . '/*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
            }
        } else {
            // Clear all cache
            $this->clearDirectory($this->cache_dir);
        }
    }
    
    private function clearDirectory($dir) {
        $files = glob($dir . '/*');
        foreach ($files as $file) {
            if (is_dir($file)) {
                $this->clearDirectory($file);
            } else {
                unlink($file);
            }
        }
    }
    
    private function getCacheFilePath($key, $type) {
        $safe_key = md5($key);
        return $this->cache_dir . '/' . $type . '/' . $safe_key . '.cache';
    }
    
    // Get cache statistics
    public function getStats() {
        $stats = ['total_files' => 0, 'total_size' => 0];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->cache_dir));
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'cache') {
                $stats['total_files']++;
                $stats['total_size'] += $file->getSize();
            }
        }
        
        $stats['total_size_mb'] = round($stats['total_size'] / 1024 / 1024, 2);
        return $stats;
    }
}

// Global cache instance
$cache = new CacheManager();

// Utility functions for easy caching
function cache_get($key, $type = 'api') {
    global $cache;
    return $cache->get($key, $type);
}

function cache_set($key, $data, $ttl = null, $type = 'api') {
    global $cache;
    return $cache->set($key, $data, $ttl, $type);
}

function cache_delete($key, $type = 'api') {
    global $cache;
    return $cache->delete($key, $type);
}

function cache_clear($type = null) {
    global $cache;
    return $cache->clear($type);
}

// Auto-cleanup old cache files (run occasionally)
function cleanup_expired_cache() {
    global $cache;
    $cache_dir = __DIR__ . '/../../../cache';
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($cache_dir));
    
    $cleaned = 0;
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'cache') {
            $data = file_get_contents($file->getPathname());
            $cache_data = json_decode($data, true);
            
            if (!$cache_data || !isset($cache_data['expires']) || $cache_data['expires'] < time()) {
                unlink($file->getPathname());
                $cleaned++;
            }
        }
    }
    
    return $cleaned;
}
?>
