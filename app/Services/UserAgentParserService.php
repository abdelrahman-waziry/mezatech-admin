<?php

namespace App\Services;

class UserAgentParserService
{
    /**
     * Parse a User-Agent string into its components.
     *
     * @return array{browser: string|null, browser_version: string|null, operating_system: string|null, device_type: string|null, platform: string|null}
     */
    public function parse(?string $userAgent): array
    {
        if (empty($userAgent)) {
            return [
                'browser' => null,
                'browser_version' => null,
                'operating_system' => null,
                'device_type' => null,
                'platform' => null,
            ];
        }

        return [
            'browser' => $this->detectBrowser($userAgent),
            'browser_version' => $this->detectBrowserVersion($userAgent),
            'operating_system' => $this->detectOS($userAgent),
            'device_type' => $this->detectDeviceType($userAgent),
            'platform' => $this->detectPlatform($userAgent),
        ];
    }

    /**
     * Detect the browser from a User-Agent string.
     */
    protected function detectBrowser(string $ua): ?string
    {
        // Order matters — check specific browsers before generic ones
        $browsers = [
            'Edge' => '/Edg(?:e|A|iOS)?\//',
            'Opera' => '/(?:OPR|Opera)\//',
            'Brave' => '/Brave\//',
            'Vivaldi' => '/Vivaldi\//',
            'Samsung Internet' => '/SamsungBrowser\//',
            'UC Browser' => '/UCBrowser\//',
            'Firefox' => '/Firefox\//',
            'Chrome' => '/Chrome\//',
            'Safari' => '/Safari\//',
            'IE' => '/(?:MSIE|Trident)/',
        ];

        foreach ($browsers as $name => $pattern) {
            if (preg_match($pattern, $ua)) {
                return $name;
            }
        }

        return null;
    }

    /**
     * Detect browser version from a User-Agent string.
     */
    protected function detectBrowserVersion(string $ua): ?string
    {
        $patterns = [
            '/Edg(?:e|A|iOS)?\/(\d+[\.\d]*)/',
            '/(?:OPR|Opera)\/(\d+[\.\d]*)/',
            '/Brave\/(\d+[\.\d]*)/',
            '/Vivaldi\/(\d+[\.\d]*)/',
            '/SamsungBrowser\/(\d+[\.\d]*)/',
            '/UCBrowser\/(\d+[\.\d]*)/',
            '/Firefox\/(\d+[\.\d]*)/',
            '/Chrome\/(\d+[\.\d]*)/',
            '/Version\/(\d+[\.\d]*).*Safari/',
            '/MSIE\s(\d+[\.\d]*)/',
            '/rv:(\d+[\.\d]*)/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $ua, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Detect operating system from a User-Agent string.
     */
    protected function detectOS(string $ua): ?string
    {
        $osMap = [
            'Windows 11' => '/Windows NT 10\.0.*Build\/[2-9]\d{4}|Windows NT 10\.0.*Win64/',
            'Windows 10' => '/Windows NT 10\.0/',
            'Windows 8.1' => '/Windows NT 6\.3/',
            'Windows 8' => '/Windows NT 6\.2/',
            'Windows 7' => '/Windows NT 6\.1/',
            'Windows Vista' => '/Windows NT 6\.0/',
            'Windows XP' => '/Windows NT 5\.1/',
            'macOS' => '/Macintosh|Mac OS X/',
            'iOS' => '/iPhone|iPad|iPod/',
            'Android' => '/Android/',
            'Chrome OS' => '/CrOS/',
            'Linux' => '/Linux/',
            'Ubuntu' => '/Ubuntu/',
            'FreeBSD' => '/FreeBSD/',
        ];

        foreach ($osMap as $name => $pattern) {
            if (preg_match($pattern, $ua)) {
                return $name;
            }
        }

        return null;
    }

    /**
     * Detect device type from a User-Agent string.
     */
    protected function detectDeviceType(string $ua): ?string
    {
        if (preg_match('/Mobile|Android.*Mobile|iPhone|iPod|Windows Phone|BlackBerry/', $ua)) {
            return 'mobile';
        }

        if (preg_match('/iPad|Android(?!.*Mobile)|Tablet/', $ua)) {
            return 'tablet';
        }

        if (preg_match('/bot|crawl|spider|slurp|Googlebot/i', $ua)) {
            return 'bot';
        }

        // Default to desktop for standard browsers
        if (preg_match('/Mozilla|Chrome|Safari|Firefox|Edge|Opera/', $ua)) {
            return 'desktop';
        }

        return null;
    }

    /**
     * Detect the platform from a User-Agent string.
     */
    protected function detectPlatform(string $ua): ?string
    {
        if (preg_match('/Windows/', $ua)) return 'Windows';
        if (preg_match('/Macintosh|Mac OS/', $ua)) return 'macOS';
        if (preg_match('/iPhone|iPad|iPod/', $ua)) return 'iOS';
        if (preg_match('/Android/', $ua)) return 'Android';
        if (preg_match('/CrOS/', $ua)) return 'Chrome OS';
        if (preg_match('/Linux/', $ua)) return 'Linux';

        return null;
    }
}
