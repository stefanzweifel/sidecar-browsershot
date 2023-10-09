<?php

return [
    /**
     * Define the allocated memory available to SidecarBrowsershot in megabytes. (Defaults to 2GB)
     * We suggest to allocate at least 513 MB of memory to push Chrome/Puppeteer out of "low-spec" mode.
     * @see https://hammerstone.dev/sidecar/docs/main/functions/customization#memory
     * @see https://github.blog/2021-06-22-framework-building-open-graph-images/
     */
    'memory' => env('SIDECAR_BROWSERSHOT_MEMORY', 2048),

    /**
     * The default ephemeral storage available to SidecarBrowsershot, in megabytes. (Defaults to 512MB)
     * @see https://hammerstone.dev/sidecar/docs/main/functions/customization#storage
     */
    'storage' => env('SIDECAR_BROWSERSHOT_STORAGE', 512),

    /**
     * The default timeout to use for SidecarBrowsershot, in seconds. (Defaults to 300)
     * @see https://hammerstone.dev/sidecar/docs/main/functions/customization#timeout
     */
    'timeout' => env('SIDECAR_BROWSERSHOT_TIMEOUT', 300),

    /**
     * Define the number of warming instances to boot.
     * @see https://hammerstone.dev/sidecar/docs/main/functions/warming
     */
    'warming' => env('SIDECAR_BROWSERSHOT_WARMING_INSTANCES', 0),

    /**
     * AWS Layer to use by Lambda. Defaults to "shelfio/chrome-aws-lambda-layer" in your AWS region.
     * Must contain "chrome-aws-lambda".
     * @see https://github.com/shelfio/chrome-aws-lambda-layer
     * @deprecated
     */
    'layer' => env('SIDECAR_BROWSERSHOT_LAYER'),
];
