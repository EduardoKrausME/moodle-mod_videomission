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

use moodle_exception;

/**
 * Mission response and grading utilities.
 *
 * @package   mod_videomission
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mission_manager {
    /** @var string */
    public const TYPE_MOMENT = 'moment';

    /** @var string */
    public const TYPE_INTERVAL = 'interval';

    /** @var string */
    public const TYPE_TEXT = 'text';

    /** @var string */
    public const TYPE_OBSERVATION = 'observation';

    /** @var string */
    public const TYPE_OCCURRENCES = 'occurrences';

    /** @var string */
    public const TYPE_CONFIRM = 'confirm';

    /** @return string[] */
    public static function types(): array {
        return [
            self::TYPE_MOMENT,
            self::TYPE_INTERVAL,
            self::TYPE_TEXT,
            self::TYPE_OBSERVATION,
            self::TYPE_OCCURRENCES,
            self::TYPE_CONFIRM,
        ];
    }

    /**
     * Determine whether a mission is available in sequential mode.
     *
     * @param object $activity
     * @param object $mission
     * @param int $userid
     * @return bool
     */
    public static function is_unlocked(object $activity, object $mission, int $userid): bool {
        global $DB;
        if (empty($activity->missionorder)) {
            return true;
        }
        $previous = $DB->get_records_select('videomission_missions',
            'videomissionid = :activity AND sortorder < :sortorder',
            ['activity' => $activity->id, 'sortorder' => $mission->sortorder], 'sortorder ASC');
        foreach ($previous as $item) {
            if (empty($item->required)) {
                continue;
            }
            $completed = $DB->record_exists('videomission_answers', [
                'missionid' => $item->id,
                'userid' => $userid,
                'completed' => 1,
            ]);
            if (!$completed) {
                return false;
            }
        }
        return true;
    }

    /**
     * Save a mission response.
     *
     * @param object $activity
     * @param object $mission
     * @param int $userid
     * @param string $response
     * @param float|null $starttime
     * @param float|null $endtime
     * @param string $occurrencesjson
     * @param bool $confirmed
     * @return object
     */
    public static function save_answer(object $activity, object $mission, int $userid, string $response,
                                       ?float $starttime, ?float $endtime, string $occurrencesjson, bool $confirmed): object {
        global $DB;

        if (!self::is_unlocked($activity, $mission, $userid)) {
            throw new moodle_exception('missionlockederror', 'mod_videomission');
        }

        $occurrences = json_decode($occurrencesjson, true);
        if (!is_array($occurrences)) {
            $occurrences = [];
        }
        $occurrences = array_values(array_filter(array_map(static function ($value) {
            return is_numeric($value) ? max(0, (float)$value) : null;
        }, $occurrences), static fn($value): bool => $value !== null));

        $completed = false;
        switch ($mission->missiontype) {
            case self::TYPE_MOMENT:
                $completed = $starttime !== null && $starttime >= 0;
                break;
            case self::TYPE_INTERVAL:
                $completed = $starttime !== null && $endtime !== null && $starttime >= 0 && $endtime > $starttime;
                break;
            case self::TYPE_TEXT:
            case self::TYPE_OBSERVATION:
                $completed = trim($response) !== '';
                break;
            case self::TYPE_OCCURRENCES:
                $completed = count($occurrences) >= max(1, (int)$mission->requiredcount);
                break;
            case self::TYPE_CONFIRM:
                $completed = $confirmed;
                break;
        }

        $now = time();
        $answer = $DB->get_record('videomission_answers', ['missionid' => $mission->id, 'userid' => $userid]);
        if (!$answer) {
            $answer = (object)[
                'missionid' => $mission->id,
                'userid' => $userid,
                'timecreated' => $now,
            ];
        }
        $answer->response = trim($response);
        $answer->starttime = $starttime;
        $answer->endtime = $endtime;
        $answer->occurrences = json_encode($occurrences);
        $answer->completed = $completed ? 1 : 0;
        $answer->score = $completed ? (float)$mission->points : 0;
        $answer->timemodified = $now;
        if (!empty($answer->id)) {
            $DB->update_record('videomission_answers', $answer);
        } else {
            $answer->id = $DB->insert_record('videomission_answers', $answer);
        }
        return $answer;
    }

    /**
     * Return completion summary.
     *
     * @param int $videomissionid
     * @param int $userid
     * @return object
     */
    public static function summary(int $videomissionid, int $userid): object {
        global $DB;
        $missions = $DB->get_records('videomission_missions', ['videomissionid' => $videomissionid], 'sortorder ASC');
        $completed = 0;
        $requiredtotal = 0;
        $requiredcompleted = 0;
        $points = 0.0;
        $maxpoints = 0.0;
        foreach ($missions as $mission) {
            $answer = $DB->get_record('videomission_answers', ['missionid' => $mission->id, 'userid' => $userid]);
            $maxpoints += (float)$mission->points;
            if (!empty($mission->required)) {
                $requiredtotal++;
            }
            if ($answer && !empty($answer->completed)) {
                $completed++;
                $points += (float)$answer->score;
                if (!empty($mission->required)) {
                    $requiredcompleted++;
                }
            }
        }
        return (object)[
            'total' => count($missions),
            'completed' => $completed,
            'pending' => max(0, count($missions) - $completed),
            'requiredtotal' => $requiredtotal,
            'requiredcompleted' => $requiredcompleted,
            'requiredpending' => max(0, $requiredtotal - $requiredcompleted),
            'points' => round($points, 2),
            'maxpoints' => round($maxpoints, 2),
        ];
    }
}
