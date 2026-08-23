<?php

declare(strict_types=1);

namespace GesimaticLoginAttempts\Repositories;

use GesimaticLoginAttempts\Core\Config;

/**
 * Provides access to the network-wide login attempts storage.
 *
 * @package GesimaticLoginAttempts
 */
final class LoginAttemptsRepository
{
    /**
     * Returns the network-wide login attempts table name.
     */
    public static function table_name(): string
    {
        global $wpdb;

        return $wpdb->base_prefix . Config::TABLE_NAME_STATUS_IP;
    }

    /**
     * Returns the blocking data stored for an IP address.
     *
     * @param string $ip Normalized IP address.
     * @return array|null
     */
    public static function find_lock_by_ip(string $ip): ?array
    {
        global $wpdb;

        $query = $wpdb->prepare(
            'SELECT id, lockUntil FROM ' . self::table_name() . ' WHERE ip = %s LIMIT 1',
            $ip
        );
        $status = $wpdb->get_row($query, ARRAY_A);

        return is_array($status) ? $status : null;
    }

    /**
     * Unlocks an IP only when its blocking period has expired.
     *
     * @param string $ip  Normalized IP address.
     * @param int    $now Current Unix timestamp.
     */
    public static function unlock_expired_ip(string $ip, int $now): bool
    {
        global $wpdb;

        $query = $wpdb->prepare(
            'UPDATE ' . self::table_name() . '
            SET lockUntil = 0, status = %s
            WHERE ip = %s AND lockUntil > 0 AND lockUntil <= %d',
            'enabled',
            $ip,
            $now
        );

        return intval($wpdb->query($query)) > 0;
    }

    /**
     * Deletes the network-wide status associated with an IP address.
     *
     * @param string $ip Normalized IP address.
     */
    public static function delete_by_ip(string $ip): bool
    {
        global $wpdb;

        return $wpdb->delete(self::table_name(), ['ip' => $ip], ['%s']) !== false;
    }

    /**
     * Atomically records a failed access attempt for an IP address.
     *
     * @param string $ip         Normalized IP address.
     * @param string $identifier Login or access identifier.
     * @param array  $settings   Validated limiter settings.
     * @return array|null Updated status, or null when persistence fails.
     */
    public static function record_failed_attempt(
        string $ip,
        string $identifier,
        array $settings
    ): ?array
    {
        global $wpdb;

        if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
            return null;
        }

        $attempts_limit = max(1, intval($settings['attempts'] ?? 1));
        $initial_lock = max(1, intval($settings['initialLock'] ?? 1));
        $multiplier = max(1, intval($settings['multiplier'] ?? 1));
        $identifier = substr(sanitize_text_field($identifier), 0, 255);
        $transaction_started = false;

        try {
            if ($wpdb->query('START TRANSACTION') === false) {
                return null;
            }

            $transaction_started = true;
            $seed_query = $wpdb->prepare(
                'INSERT INTO ' . self::table_name() . '
                    (userLogin, ip, attempts, currentPeriod, lockUntil, lastAttempt, status)
                VALUES (%s, %s, 0, %d, 0, 0, \'enabled\')
                ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id)',
                '',
                $ip,
                $initial_lock
            );

            if ($wpdb->query($seed_query) === false) {
                throw new \RuntimeException('Unable to initialize the IP status.');
            }

            $status_query = $wpdb->prepare(
                'SELECT * FROM ' . self::table_name() . ' WHERE ip = %s LIMIT 1 FOR UPDATE',
                $ip
            );
            $status = $wpdb->get_row($status_query, ARRAY_A);

            if (!is_array($status)) {
                throw new \RuntimeException('Unable to lock the IP status.');
            }

            $now = time();

            if (intval($status['lockUntil']) <= $now) {
                $status['lockUntil'] = 0;
                $status['status'] = 'enabled';
            }

            if (intval($status['lockUntil']) === 0) {
                $status['userLogin'] = $identifier;
                $status['attempts'] = intval($status['attempts']) + 1;
                $status['lastAttempt'] = $now;

                if (intval($status['attempts']) % $attempts_limit === 0) {
                    $current_period = max(
                        1,
                        intval($status['currentPeriod']) ?: $initial_lock
                    );
                    $status['lockUntil'] = $now + ($current_period * MINUTE_IN_SECONDS);
                    $status['currentPeriod'] = $current_period * $multiplier;
                    $status['status'] = 'blocked';
                }

                $updated = $wpdb->update(
                    self::table_name(),
                    $status,
                    ['id' => intval($status['id'])]
                );

                if ($updated === false) {
                    throw new \RuntimeException('Unable to update the IP status.');
                }
            }

            if ($wpdb->query('COMMIT') === false) {
                throw new \RuntimeException('Unable to commit the IP status.');
            }

            $transaction_started = false;

            return $status;
        } catch (\Throwable $exception) {
            if ($transaction_started) {
                $wpdb->query('ROLLBACK');
            }

            return null;
        }
    }

    private function __construct()
    {
    }
}
