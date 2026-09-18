// Practical 6 - reusable JSON Fetch service with localStorage cache

export async function fetchJsonWithCache(url, cacheKey) {
    try {
        const response = await fetch(url, { cache: "no-store" });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: Unable to load ${url}`);
        }

        const data = await response.json();

        if (!Array.isArray(data)) {
            throw new Error(`${url} must contain a JSON array.`);
        }

        localStorage.setItem(
            cacheKey,
            JSON.stringify({
                savedAt: new Date().toISOString(),
                data: data
            })
        );

        return {
            data: data,
            source: "network",
            message: `Loaded ${data.length} records from ${url}.`
        };

    } catch (error) {
        const cachedValue = localStorage.getItem(cacheKey);

        if (cachedValue) {
            try {
                const cached = JSON.parse(cachedValue);

                if (Array.isArray(cached.data)) {
                    return {
                        data: cached.data,
                        source: "cache",
                        message: `Network load failed. Showing ${cached.data.length} cached records.`,
                        originalError: error.message
                    };
                }
            } catch (cacheError) {
                console.warn("Invalid cached JSON:", cacheError);
            }
        }

        throw error;
    }
}
