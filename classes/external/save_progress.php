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
use mod_videomission\progress_manager;

/**
 * AJAX function for video progress.
 *
 * @package   mod_videomission
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class save_progress extends external_api {
    /**
     * Method execute_parameters.
     *
     * @return external_function_parameters Return value.
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'Course module ID'),
            'duration' => new external_value(PARAM_FLOAT, 'Video duration'),
            'position' => new external_value(PARAM_FLOAT, 'Current position'),
            'playtime' => new external_value(PARAM_FLOAT, 'Total playback time'),
            'ranges' => new external_value(PARAM_RAW, 'JSON watched ranges'),
        ]);
    }

    /**
     * Method execute.
     *
     * @param int $cmid Parameter cmid.
     * @param float $duration Parameter duration.
     * @param float $position Parameter position.
     * @param float $playtime Parameter playtime.
     * @param string $ranges Parameter ranges.
     * @return array Return value.
     */
    public static function execute(int $cmid, float $duration, float $position, float $playtime, string $ranges): array {
        global $CFG, $DB, $USER;
        require_once($CFG->dirroot . '/mod/videomission/lib.php');
        $params = self::validate_parameters(self::execute_parameters(),
            compact('cmid', 'duration', 'position', 'playtime', 'ranges'));
        $cm = get_coursemodule_from_id('videomission', $params['cmid'], 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/videomission:view', $context);
        $activity = $DB->get_record('videomission', ['id' => $cm->instance], '*', MUST_EXIST);
        $progress = progress_manager::save((int)$activity->id, (int)$USER->id, max(0, $params['duration']),
            max(0, $params['position']), max(0, $params['playtime']), $params['ranges']);

        $summary = \mod_videomission\mission_manager::summary((int)$activity->id, (int)$USER->id);
        $overall = videomission_calculate_overall_progress($activity, $summary, $progress);

        $completion = new \completion_info(get_course($cm->course));
        $completion->update_state($cm, COMPLETION_UNKNOWN, $USER->id);

        return [
            'percentage' => (float)$progress->percentage,
            'watchedseconds' => (float)$progress->watchedseconds,
            'overall' => $overall,
            'videoprogress' => get_string('videoprogress', 'mod_videomission', format_float((float)$progress->percentage, 1)),
            'message' => get_string('progresssaved', 'mod_videomission'),
        ];
    }

    /**
     * Method execute_returns.
     *
     * @return external_single_structure Return value.
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'percentage' => new external_value(PARAM_FLOAT, 'Unique watched percentage'),
            'watchedseconds' => new external_value(PARAM_FLOAT, 'Unique watched seconds'),
            'overall' => new external_value(PARAM_FLOAT, 'Overall activity progress'),
            'videoprogress' => new external_value(PARAM_TEXT, 'Localized video progress label'),
            'message' => new external_value(PARAM_TEXT, 'Status message'),
        ]);
    }
}
