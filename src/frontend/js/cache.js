// Frontend caching utility for DRMS
// Implements localStorage caching with expiration

class FrontendCache {
    constructor() {
        this.prefix = 'drms_cache_';
        this.defaultTTL = 300000; // 5 minutes in milliseconds
    }
    
    // Set cache with expiration
    set(key, data, ttl = null) {
        try {
            const expiration = Date.now() + (ttl || this.defaultTTL);
            const cacheData = {
                data: data,
                expiration: expiration,
                created: Date.now()
            };
            localStorage.setItem(this.prefix + key, JSON.stringify(cacheData));
            return true;
        } catch (e) {
            console.warn('Cache storage failed:', e);
            return false;
        }
    }
    
    // Get cached data
    get(key) {
        try {
            const cached = localStorage.getItem(this.prefix + key);
            if (!cached) return null;
            
            const cacheData = JSON.parse(cached);
            
            // Check if expired
            if (Date.now() > cacheData.expiration) {
                this.delete(key);
                return null;
            }
            
            return cacheData.data;
        } catch (e) {
            console.warn('Cache retrieval failed:', e);
            return null;
        }
    }
    
    // Delete specific cache
    delete(key) {
        try {
            localStorage.removeItem(this.prefix + key);
            return true;
        } catch (e) {
            console.warn('Cache deletion failed:', e);
            return false;
        }
    }
    
    // Clear all cache
    clear() {
        try {
            const keys = Object.keys(localStorage);
            keys.forEach(key => {
                if (key.startsWith(this.prefix)) {
                    localStorage.removeItem(key);
                }
            });
            return true;
        } catch (e) {
            console.warn('Cache clear failed:', e);
            return false;
        }
    }
    
    // Get cache statistics
    getStats() {
        const keys = Object.keys(localStorage);
        const cacheKeys = keys.filter(key => key.startsWith(this.prefix));
        let totalSize = 0;
        let expiredCount = 0;
        
        cacheKeys.forEach(key => {
            try {
                const value = localStorage.getItem(key);
                totalSize += value.length;
                
                const cacheData = JSON.parse(value);
                if (Date.now() > cacheData.expiration) {
                    expiredCount++;
                }
            } catch (e) {
                // Invalid cache data
                localStorage.removeItem(key);
            }
        });
        
        return {
            totalItems: cacheKeys.length,
            totalSizeKB: Math.round(totalSize / 1024),
            expiredItems: expiredCount
        };
    }
    
    // Clean expired cache
    cleanExpired() {
        const keys = Object.keys(localStorage);
        const cacheKeys = keys.filter(key => key.startsWith(this.prefix));
        let cleaned = 0;
        
        cacheKeys.forEach(key => {
            try {
                const value = localStorage.getItem(key);
                const cacheData = JSON.parse(value);
                
                if (Date.now() > cacheData.expiration) {
                    localStorage.removeItem(key);
                    cleaned++;
                }
            } catch (e) {
                // Invalid cache data
                localStorage.removeItem(key);
                cleaned++;
            }
        });
        
        return cleaned;
    }
}

// Enhanced fetch with caching
class CachedFetch {
    constructor() {
        this.cache = new FrontendCache();
    }
    
    // Fetch with automatic caching
    async fetch(url, options = {}, cacheConfig = {}) {
        const method = options.method || 'GET';
        const cacheKey = this.generateCacheKey(url, method, options.body);
        const cacheTTL = cacheConfig.ttl || 300000; // 5 minutes default
        const useCache = cacheConfig.useCache !== false && method === 'GET';
        
        // Try cache first for GET requests
        if (useCache) {
            const cached = this.cache.get(cacheKey);
            if (cached) {
                console.log('📦 Cache hit for:', url);
                return cached;
            }
        }
        
        try {
            console.log('🌐 Fetching from server:', url);
            
            // Add timeout to fetch
            const timeoutPromise = new Promise((_, reject) => {
                setTimeout(() => reject(new Error('Request timeout')), 10000);
            });
            
            const fetchPromise = fetch(url, {
                ...options,
                credentials: 'include'
            });
            
            const response = await Promise.race([fetchPromise, timeoutPromise]);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            
            // Cache successful GET responses
            if (useCache && response.ok) {
                this.cache.set(cacheKey, data, cacheTTL);
            }
            
            return data;
            
        } catch (error) {
            console.error('❌ Fetch error:', error);
            throw error;
        }
    }
    
    // Generate cache key
    generateCacheKey(url, method, body) {
        const baseKey = `${method}_${url}`;
        if (body) {
            const bodyHash = this.simpleHash(typeof body === 'string' ? body : JSON.stringify(body));
            return `${baseKey}_${bodyHash}`;
        }
        return baseKey;
    }
    
    // Simple hash function
    simpleHash(str) {
        let hash = 0;
        for (let i = 0; i < str.length; i++) {
            const char = str.charCodeAt(i);
            hash = ((hash << 5) - hash) + char;
            hash = hash & hash; // Convert to 32-bit integer
        }
        return hash.toString(36);
    }
    
    // Invalidate cache for specific patterns
    invalidateCache(pattern) {
        const keys = Object.keys(localStorage);
        const cacheKeys = keys.filter(key => 
            key.startsWith(this.cache.prefix) && 
            key.includes(pattern)
        );
        
        cacheKeys.forEach(key => {
            localStorage.removeItem(key);
        });
        
        return cacheKeys.length;
    }
}

// Global instances
const frontendCache = new FrontendCache();
const cachedFetch = new CachedFetch();

// Utility functions
function cacheApiCall(url, options = {}, cacheOptions = {}) {
    return cachedFetch.fetch(url, options, cacheOptions);
}

function clearFrontendCache() {
    return frontendCache.clear();
}

function getCacheStats() {
    return frontendCache.getStats();
}

// Auto-cleanup expired cache on page load
document.addEventListener('DOMContentLoaded', () => {
    const cleaned = frontendCache.cleanExpired();
    if (cleaned > 0) {
        console.log(`🧹 Cleaned ${cleaned} expired cache items`);
    }
});

// Periodic cache cleanup (every 5 minutes)
setInterval(() => {
    frontendCache.cleanExpired();
}, 300000);

// Export for use in other scripts
window.FrontendCache = FrontendCache;
window.CachedFetch = CachedFetch;
window.frontendCache = frontendCache;
window.cachedFetch = cachedFetch;
window.cacheApiCall = cacheApiCall;
