<?php
/**
 * The manifest of files that are local to specific environment.
 * This file returns a list of environments that the application
 * may be installed under. The returned data must be in the following
 * format:
 *
 * ```php
 * return [
 *     'environment name' => [
 *         'path' => 'directory storing the local files',
 *         'skipFiles'  => [
 *             // list of files that should only copied once and skipped if they already exist
 *         ],
 *         'setWritable' => [
 *             // list of directories that should be set writable
 *         ],
 *         'setExecutable' => [
 *             // list of files that should be set executable
 *         ],
 *         'setCookieValidationKey' => [
 *             // list of config files that need to be inserted with automatically generated cookie validation keys
 *         ],
 *         'createSymlink' => [
 *             // list of symlinks to be created. Keys are symlinks, and values are the targets.
 *         ],
 *     ],
 * ];
 * ```
 */
return [
    'dev' => [
        'path' => 'dev',
        'webroot'=>'webroot',
        'setWritable' => [
            'mobile/runtime',
            'webroot/mobile/assets',
            'pc/runtime',
            'webroot/pc/assets',
            'backend/runtime',
            'webroot/backend/assets',
            'api/runtime',
            'agencyapi/runtime',
            'minyingapi/runtime',
        ],
        'clearCache'=>[
            'mobile/runtime/cache',
            'pc/runtime/cache',
            'backend/runtime/cache',
            'api/runtime/cache',
            'agencyapi/runtime/cache',
            'minyingapi/runtime/cache',
        ],
        'setExecutable' => [
            'yii',
            'yii_test',
        ],
        'setCookieValidationKey' => [
            'mobile/config/main-local.php',
            'pc/config/main-local.php',
            'backend/config/main-local.php',
            'api/config/main-local.php',
            'agencyapi/config/main-local.php',
            'minyingapi/config/main-local.php',
        ],
    ],
    'test' => [
        'path' => 'test',
        'webroot'=>'webroot',
        'setWritable' => [
            'mobile/runtime',
            'webroot/mobile/assets',
            'pc/runtime',
            'webroot/pc/assets',
            'backend/runtime',
            'webroot/backend/assets',
            'api/runtime',
            'agencyapi/runtime',
            'minyingapi/runtime',
        ],
        'clearCache'=>[
            'mobile/runtime/cache',
            'pc/runtime/cache',
            'backend/runtime/cache',
            'api/runtime/cache',
            'agencyapi/runtime/cache',
            'minyingapi/runtime/cache',
        ],
        'setExecutable' => [
            'yii',
            'yii_test',
        ],
        'setCookieValidationKey' => [
            'mobile/config/main-local.php',
            'pc/config/main-local.php',
            'backend/config/main-local.php',
            'api/config/main-local.php',
            'agencyapi/config/main-local.php',
            'minyingapi/config/main-local.php',
        ],
    ],
    'prod' => [
        'path' => 'prod',
        'webroot'=>'webroot',
        'setWritable' => [
            'mobile/runtime',
            'webroot/mobile/assets',
            'pc/runtime',
            'webroot/pc/assets',
            'backend/runtime',
            'webroot/backend/assets',
            'api/runtime',
            'agencyapi/runtime',
            'minyingapi/runtime',
        ],
        'clearCache'=>[
            'mobile/runtime/cache',
            'pc/runtime/cache',
            'backend/runtime/cache',
            'api/runtime/cache',
            'agencyapi/runtime/cache',
            'minyingapi/runtime/cache',
        ],
        'setExecutable' => [
            'yii',
        ],
        'setCookieValidationKey' => [
            'mobile/config/main-local.php',
            'pc/config/main-local.php',
            'backend/config/main-local.php',
            'api/config/main-local.php',
            'agencyapi/config/main-local.php',
            'minyingapi/config/main-local.php',
        ],
    ],
];
