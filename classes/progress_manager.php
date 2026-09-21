<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace mod_videomission;

/**
 * Video progress utilities.
 *
 * @package   mod_videomission
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class progress_manager {
    /**
     * Normalize and merge time ranges.
     *
     * @param array $ranges
     * @return array<int,array{0:float,1:float}>
     */
    public static function normalize_ranges(array $ranges): array {
        $clean = [];
        foreach ($ranges as $range) {
            if (!is_array($range) || count($range) < 2) {
                continue;
            }
            $start = max(0.0, (float)$range[0]);
            $end = max($start, (float)$range[1]);
            if ($end <= $start) {
                continue;
            }
            $clean[] = [$start, $end];
        }
        usort($clean, static fn(array $a, array $b): int => $a[0] <=> $b[0]);

        $merged = [];
        foreach ($clean as $range) {
            if ($merged === []) {
                $merged[] = $range;
                continue;
            }
            $index = count($merged) - 1;
            if ($range[0] <= $merged[$index][1] + 0.75) {
                $merged[$index][1] = max($merged[$index][1], $range[1]);
            } else {
                $merged[] = $range;
            }
        }
        return $merged;
    }

    /**
     * Merge previously stored and incoming ranges.
     *
     * @param string|null $storedjson
     * @param string $incomingjson
     * @return array<int,array{0:float,1:float}>
     */
    public static function merge_json_ranges(?string $storedjson, string $incomingjson): array {
        $stored = json_decode((string)$storedjson, true);
        $incoming = json_decode($incomingjson, true);
        if (!is_array($stored)) {
            $stored = [];
        }
        if (!is_array($incoming)) {
            $incoming = [];
        }
        return self::normalize_ranges(array_merge($stored, $incoming));
    }

    /**
     * Calculate unique watched seconds.
     *
     * @param array $ranges
     * @return float
     */
    public static function watched_seconds(array $ranges): float {
        $seconds = 0.0;
        foreach (self::normalize_ranges($ranges) as $range) {
            $seconds += $range[1] - $range[0];
        }
        return round($seconds, 3);
    }

    /**
     * Save progress.
     *
     * @param int $videomissionid
     * @param int $userid
     * @param float $duration
     * @param float $position
     * @param float $playtime
     * @param string $rangesjson
     * @return object
     */
    public static function save(int $videomissionid, int $userid, float $duration, float $position,
                                float $playtime, string $rangesjson): object {
        global $DB;

        $record = $DB->get_record('videomission_progress', [
            'videomissionid' => $videomissionid,
            'userid' => $userid,
        ]);
        $merged = self::merge_json_ranges($record->watchedsegments ?? null, $rangesjson);
        $watchedseconds = self::watched_seconds($merged);
        $effectiveduration = max($duration, (float)($record->duration ?? 0));
        $percentage = $effectiveduration > 0 ? min(100, ($watchedseconds / $effectiveduration) * 100) : 0;
        $now = time();

        if ($record) {
            $record->duration = $effectiveduration;
            $record->watchedsegments = json_encode($merged);
            $record->watchedseconds = $watchedseconds;
            $record->percentage = round($percentage, 2);
            $record->lastposition = max(0, $position);
            $record->playtime = max((float)$record->playtime, $playtime);
            $record->timemodified = $now;
            $DB->update_record('videomission_progress', $record);
        } else {
            $record = (object)[
                'videomissionid' => $videomissionid,
                'userid' => $userid,
                'duration' => $effectiveduration,
                'watchedsegments' => json_encode($merged),
                'watchedseconds' => $watchedseconds,
                'percentage' => round($percentage, 2),
                'lastposition' => max(0, $position),
                'playtime' => max(0, $playtime),
                'timecreated' => $now,
                'timemodified' => $now,
            ];
            $record->id = $DB->insert_record('videomission_progress', $record);
        }
        return $record;
    }

    /**
     * Load progress or return an empty record.
     *
     * @param int $videomissionid
     * @param int $userid
     * @return object
     */
    public static function get(int $videomissionid, int $userid): object {
        global $DB;
        $record = $DB->get_record('videomission_progress', [
            'videomissionid' => $videomissionid,
            'userid' => $userid,
        ]);
        if ($record) {
            return $record;
        }
        return (object)[
            'id' => 0,
            'videomissionid' => $videomissionid,
            'userid' => $userid,
            'duration' => 0,
            'watchedsegments' => '[]',
            'watchedseconds' => 0,
            'percentage' => 0,
            'lastposition' => 0,
            'playtime' => 0,
            'timecreated' => 0,
            'timemodified' => 0,
        ];
    }
}
