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

namespace mod_videomission\external;

use context_module;
use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;
use mod_videomission\mission_manager;
use mod_videomission\progress_manager;

/**
 * AJAX function for mission responses.
 *
 * @package   mod_videomission
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class save_mission extends external_api {
    /**
     * Method execute_parameters.
     *
     * @return external_function_parameters Return value.
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'Course module ID'),
            'missionid' => new external_value(PARAM_INT, 'Mission ID'),
            'response' => new external_value(PARAM_RAW, 'Text response'),
            'starttime' => new external_value(PARAM_FLOAT, 'Moment or interval start, -1 when empty'),
            'endtime' => new external_value(PARAM_FLOAT, 'Interval end, -1 when empty'),
            'occurrences' => new external_value(PARAM_RAW, 'JSON occurrence times'),
            'confirmed' => new external_value(PARAM_BOOL, 'Task confirmation'),
        ]);
    }

    /**
     * Method execute.
     *
     * @param int $cmid Parameter cmid.
     * @param int $missionid Parameter missionid.
     * @param string $response Parameter response.
     * @param float $starttime Parameter starttime.
     * @param float $endtime Parameter endtime.
     * @param string $occurrences Parameter occurrences.
     * @param bool $confirmed Parameter confirmed.
     * @return array Return value.
     */
    public static function execute(int $cmid, int $missionid, string $response, float $starttime, float $endtime,
                                   string $occurrences, bool $confirmed): array {
        global $CFG, $DB, $USER;
        require_once($CFG->dirroot . '/mod/videomission/lib.php');
        $params = self::validate_parameters(self::execute_parameters(), compact('cmid', 'missionid', 'response',
            'starttime', 'endtime', 'occurrences', 'confirmed'));
        $cm = get_coursemodule_from_id('videomission', $params['cmid'], 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/videomission:submit', $context);
        $activity = $DB->get_record('videomission', ['id' => $cm->instance], '*', MUST_EXIST);
        $mission = $DB->get_record('videomission_missions', ['id' => $params['missionid'], 'videomissionid' => $activity->id],
            '*', MUST_EXIST);
        $start = $params['starttime'] < 0 ? null : (float)$params['starttime'];
        $end = $params['endtime'] < 0 ? null : (float)$params['endtime'];
        $answer = mission_manager::save_answer($activity, $mission, (int)$USER->id, $params['response'], $start, $end,
            $params['occurrences'], (bool)$params['confirmed']);
        $summary = mission_manager::summary((int)$activity->id, (int)$USER->id);
        $progress = progress_manager::get((int)$activity->id, (int)$USER->id);
        $overall = videomission_calculate_overall_progress($activity, $summary, $progress);

        videomission_update_grades($activity, (int)$USER->id, false);
        $completion = new \completion_info(get_course($cm->course));
        $completion->update_state($cm, COMPLETION_UNKNOWN, $USER->id);

        return [
            'completed' => !empty($answer->completed),
            'score' => (float)$answer->score,
            'completedmissions' => (int)$summary->completed,
            'totalmissions' => (int)$summary->total,
            'requiredcompleted' => (int)$summary->requiredcompleted,
            'requiredtotal' => (int)$summary->requiredtotal,
            'overall' => $overall,
            'missionprogress' => get_string('missionprogress', 'mod_videomission', (object)[
                'completed' => (int)$summary->completed, 'total' => (int)$summary->total]),
            'mandatoryprogress' => get_string('mandatoryprogress', 'mod_videomission', (object)[
                'completed' => (int)$summary->requiredcompleted, 'total' => (int)$summary->requiredtotal]),
            'message' => get_string('saved', 'mod_videomission'),
        ];
    }

    /**
     * Method execute_returns.
     *
     * @return external_single_structure Return value.
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'completed' => new external_value(PARAM_BOOL, 'Whether the mission is complete'),
            'score' => new external_value(PARAM_FLOAT, 'Mission score'),
            'completedmissions' => new external_value(PARAM_INT, 'Completed mission count'),
            'totalmissions' => new external_value(PARAM_INT, 'Total mission count'),
            'requiredcompleted' => new external_value(PARAM_INT, 'Completed mandatory mission count'),
            'requiredtotal' => new external_value(PARAM_INT, 'Total mandatory mission count'),
            'overall' => new external_value(PARAM_FLOAT, 'Overall activity progress'),
            'missionprogress' => new external_value(PARAM_TEXT, 'Localized mission progress label'),
            'mandatoryprogress' => new external_value(PARAM_TEXT, 'Localized mandatory mission progress label'),
            'message' => new external_value(PARAM_TEXT, 'Status message'),
        ]);
    }
}
