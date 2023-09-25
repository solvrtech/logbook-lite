<?php

namespace App\Common;

class EnvReplacer
{
    /**
     * Bulk replace the environment value.
     *
     * @param array $environments
     * @param string $dir
     */
    public function bulkReplace(array $environments, string $dir): void
    {
        foreach ($environments as $key => $value) {
            self::replaceEnvironmentValue($key, $value, $dir);
        }
    }

    /**
     * Replace the environment value with the given key.
     *
     * @param string $key
     * @param string $value
     * @param string $dir
     */
    public function replaceEnvironmentValue(string $key, string $value, string $dir): void
    {
        $path = $dir.'.env';

        $content = file_get_contents($path);
        $pattern = '/^'.preg_quote($key).'=.*/m';
        $replacement = $key.'='.$value;
        $modifiedContent = preg_replace($pattern, $replacement, $content);

        file_put_contents($path, $modifiedContent);
    }
}