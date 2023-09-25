<?php

namespace App\Common;

use Exception;

class CommonHelper
{
    /**
     * Removes duplicate values from an array.
     *
     * @param array $array
     * @param string $key The key of array
     * @param bool $last Set last value of array by key
     *
     * @return array
     *
     * @throws Exception
     */
    public function uniqueArray(array $array, string $key, bool $last = false): array
    {
        if (false === $this->isMultidimArray($array)) {
            throw new Exception("Array is not multidimensional");
        }

        $tempArray = array();
        $i = 0;
        $keyArray = array();

        foreach ($array as $val) {
            if (in_array($val[$key], $keyArray)) {
                if ($last) {
                    $keySearch = array_search(
                        $val[$key],
                        array_column($tempArray, $key)
                    );
                    $tempArray[$keySearch] = $val;
                }
            } else {
                $keyArray[$i] = $val[$key];
                $tempArray[$i] = $val;
            }

            $i++;
        }

        return $tempArray;
    }

    /**
     * Checking that the given array is a multidimensional array.
     *
     * @param array $array
     *
     * @return bool
     */
    private function isMultidimArray(array $array): bool
    {
        foreach ($array as $val) {
            if (is_array($val)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Extract user agent into an array.
     *
     * @param string $userAgent
     *
     * @return array
     */
    public function extractUserAgent(?string $userAgent): array
    {
        if (null === $userAgent) {
            return [
                'platform' => 'Unknown',
                'os' => 'Unknown',
                'browser' => 'Unknown',
            ];
        }

        return [
            'platform' => $this->getPlatform($userAgent),
            'os' => $this->getOS($userAgent),
            'browser' => $this->getBrowser($userAgent),
        ];
    }

    /**
     * Get the platform from user agent.
     *
     * @param string $userAgent
     *
     * @return string
     */
    private function getPlatform(string $userAgent): string
    {
        if (preg_match('/iphone/i', $userAgent)) {
            return "Iphone";
        } elseif (preg_match('/ipad/i', $userAgent)) {
            return "Ipad";
        } elseif (preg_match('/ipod/i', $userAgent)) {
            return "Ipod";
        } elseif (
            preg_match('/mobile/i', $userAgent) &&
            !preg_match('/iphone/i', $userAgent) &&
            !preg_match('/ipad/i', $userAgent) &&
            !preg_match('/ipod/i', $userAgent)
        ) {
            return "Mobile";
        } elseif (preg_match('/xbox/i', $userAgent)) {
            return "Xbox One";
        } else {
            return "Desktop";
        }
    }

    /**
     * Get the OS from user agent.
     *
     * @param string $userAgent
     *
     * @return string
     */
    private function getOS(string $userAgent): string
    {
        $matches = [];

        if (
            preg_match('/linux/i', $userAgent) &&
            !preg_match('/android/i', $userAgent)
        ) {
            $matches = [
                'key' => "linux",
                'output' => "Linux",
            ];
        } elseif (preg_match('/cros/i', $userAgent)) {
            $matches = [
                'key' => "cros",
                'output' => "Chrome OS",
            ];
        } elseif (preg_match('/mac os|macintosh/i', $userAgent)) {
            $matches = [
                'key' => "mac os|macintosh",
                'output' => "macOS",
            ];
        } elseif (preg_match('/iphone os|iphone/i', $userAgent)) {
            $matches = [
                'key' => "iphone os|iphone",
                'output' => "iOS",
            ];
        } elseif (preg_match('/windows/i', $userAgent)) {
            $matches = [
                'key' => "windows",
                'output' => "Windows",
            ];
        } elseif (preg_match('/android/i', $userAgent)) {
            $matches = [
                'key' => "android",
                'output' => "Android",
            ];
        }

        if (0 < count($matches)) {
            preg_match(
                "/({$matches['key']})([^;|)]+)/i",
                $userAgent,
                $output
            );

            return 0 < count($output) ? $output[0] : $matches['output'];
        }

        return "Unknown";
    }

    /**
     * Get the browser from user agent.
     *
     * @param string $userAgent
     *
     * @return string
     */
    private function getBrowser(string $userAgent): string
    {
        $matches = [];

        if (preg_match('/firefox|fxios/i', $userAgent)) {
            $matches = [
                'key' => "firefox|fxios",
                'output' => "Firefox",
            ];
        } elseif (preg_match('/msie/i', $userAgent)) {
            $matches = [
                'key' => "msie",
                'output' => "Internet Explorer",
            ];
        } elseif (
            preg_match('/safari/i', $userAgent) &&
            !preg_match('/chrome|crios/i', $userAgent) &&
            !preg_match('/edg|edga|edge/i', $userAgent) &&
            !preg_match('/opr/i', $userAgent) &&
            !preg_match('/vivaldi/i', $userAgent) &&
            !preg_match('/yowser|yabrowser/i', $userAgent)
        ) {
            $matches = [
                'key' => "safari",
                'output' => "Safari",
            ];
        } elseif (
            preg_match('/chrome|crios/i', $userAgent) &&
            !preg_match('/edg|edga|edge/i', $userAgent) &&
            !preg_match('/opr/i', $userAgent) &&
            !preg_match('/vivaldi/i', $userAgent) &&
            !preg_match('/yowser|yabrowser/i', $userAgent)
        ) {
            $matches = [
                'key' => "chrome|crios",
                'output' => "Chrome",
            ];
        } elseif (preg_match('/edga|edge|edg/i', $userAgent)) {
            $matches = [
                'key' => "edga|edge|edg",
                'output' => "Edge",
            ];
        } elseif (preg_match('/opr/i', $userAgent)) {
            $matches = [
                'key' => "opr",
                'output' => "Opera",
            ];
        } elseif (preg_match('/vivaldi/i', $userAgent)) {
            $matches = [
                'key' => "vivaldi",
                'output' => "Vivaldi",
            ];
        } elseif (preg_match('/yowser|yabrowser/i', $userAgent)) {
            $matches = [
                'key' => "yowser|yabrowser",
                'output' => "Yandex",
            ];
        }

        if (0 < count($matches)) {
            preg_match(
                "/(\b{$matches['key']})([^\s]+)/i",
                $userAgent,
                $output
            );
            $v = 0 < count($output) ? substr($output[2], 1) : null;

            return $v ? "{$matches['output']} v{$v}" : $matches['output'];
        }

        return "Unknown";
    }

    /**
     * Change the string key of the array from snake_case to camelCase.
     *
     * @param array $array
     *
     * @return array
     */
    public function arraySnakeToCamelCase(array $array): array
    {
        $outputArray = [];

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $value = self::arraySnakeToCamelCase($value);
            }

            $outputArray[$this->snakeToCamelCase($key)] = $value;
        }

        return $outputArray;
    }

    /**
     * Change the string from snake_case into camelCase
     *
     * @param string $string
     *
     * @return string
     */
    private function snakeToCamelCase(string $string): string
    {
        return lcfirst(
            str_replace(
                '_',
                '',
                ucwords($string, '_')
            )
        );
    }
}