<?php

declare(strict_types=1);

namespace GesimaticLoginAttempts\Security;

use GesimaticLoginAttempts\Repositories\LoginAttemptsRepository;

/**
 * Class Security.
 * 
 * @package GesimaticLoginAttempts\Security.
*/
class Security
{

    /**
     * Blocking results cached for the current request.
     *
     * @var array<string, bool>
     */
    private static array $request_cache = [];

    /**
     * This method gets the ip from client request.
     *       
     * @return string
     */
    public static function get_client_ip(): string
    {
        return self::normalize_ip($_SERVER['REMOTE_ADDR'] ?? '') ?? '';
    }

    /**
     * Checks whether an IP address has an active block.
     *
     * @param mixed $ip IP address to check.
     * @return bool
     */
    public static function is_ip_blocked(mixed $ip): bool
    {
        $ip = self::normalize_ip($ip);

        if ($ip === null) {
            return false;
        }

        if (array_key_exists($ip, self::$request_cache)) {
            return self::$request_cache[$ip];
        }

        $now = time();
        $status = LoginAttemptsRepository::find_lock_by_ip($ip);
        $is_blocked = is_array($status)
            && intval($status['lockUntil']) > $now;

        if (is_array($status) && !$is_blocked && intval($status['lockUntil']) > 0) {
            LoginAttemptsRepository::unlock_expired_ip($ip, $now);
        }

        self::$request_cache[$ip] = $is_blocked;

        return $is_blocked;
    }

    /**
     * Invalidates blocking results cached during the current request.
     *
     * @param string|null $ip IP to invalidate, or null to clear all entries.
     */
    public static function clear_request_cache(?string $ip = null): void
    {
        if ($ip === null) {
            self::$request_cache = [];
            return;
        }

        $normalized_ip = self::normalize_ip($ip);

        if ($normalized_ip !== null) {
            unset(self::$request_cache[$normalized_ip]);
        }
    }

    /**
     * Unlocks an IP when its blocking period has expired.
     *
     * @param mixed $ip IP address to unlock.
     * @return bool
     */
    public static function unlock_ip(mixed $ip): bool
    {
        $ip = self::normalize_ip($ip);

        if ($ip === null) {
            return false;
        }

        $status = LoginAttemptsRepository::find_lock_by_ip($ip);
        $now = time();

        if (
            !is_array($status)
            || intval($status['lockUntil']) <= 0
            || intval($status['lockUntil']) > $now
        ) {
            return false;
        }

        $updated = LoginAttemptsRepository::unlock_expired_ip($ip, $now);

        if ($updated) {
            self::clear_request_cache($ip);
        }

        return $updated;
    }

    /**
     * Validates and returns the canonical representation of an IP address.
     */
    public static function normalize_ip(mixed $ip): ?string
    {
        if (!is_string($ip) && !is_int($ip)) {
            return null;
        }

        $ip = trim((string) $ip);

        if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
            return null;
        }

        $packed_ip = inet_pton($ip);

        if ($packed_ip === false) {
            return null;
        }

        $normalized_ip = inet_ntop($packed_ip);

        return $normalized_ip === false ? null : $normalized_ip;
    }
}