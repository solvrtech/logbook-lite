<?php

if (!class_exists(LogBookRequirementChecker::class)) {
    class LogBookRequirementChecker
    {
        public function checkRequirement(int $phpVersion, array $extension): array
        {
            $versionMessages = $this->checkPHPVersion($phpVersion);
            $extensionMessages = $this->checkPHPExtension($extension);

            return array_merge($versionMessages, $extensionMessages);
        }

        private function checkPHPVersion(int $minVersionNumber): array
        {
            if (!defined('PHP_VERSION_ID')) {
                $version = explode('.', PHP_VERSION);

                define('PHP_VERSION_ID', ((int)$version[0] * 10000 + (int)$version[1] * 100 + (int)$version[2]));
            }

            if ($minVersionNumber > PHP_VERSION_ID) {
                $minVersionSplit = str_split($minVersionNumber);
                $minVersion = "{$minVersionSplit[0]}.{$minVersionSplit[2]}";

                return ["PHP version {$minVersion} or higher is required to run this script."];
            }

            return [];
        }

        private function checkPHPExtension(array $extRequire): array
        {
            $availableExt = get_loaded_extensions();
            $availableExt = array_map('strtolower', $availableExt);
            $messages = [];

            foreach ($extRequire as $ext) {
                if (!in_array(strtolower($ext), $availableExt)) {
                    $messages[] = "The extension '{$ext}' is missing.";
                }
            }

            return $messages;
        }
    }
}

$extension = [
    'curl',
    'gd',
    'iconv',
    'imagick',
    'intl',
    'mbstring',
    'openssl',
    'pdo',
    'redis',
];
$requirementMsg = (new LogbookRequirementChecker())->checkRequirement(80100, $extension);

if (0 < count($requirementMsg)) {
    fwrite(STDERR, "Your system doesn't meet the following requirements:\n");

    foreach ($requirementMsg as $msg) {
        fwrite(STDERR, "- {$msg}\n");
    }

    exit(1);
}

if (!is_file(dirname(__DIR__).'/vendor/autoload_runtime.php')) {
    shell_exec('composer install');
}

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';