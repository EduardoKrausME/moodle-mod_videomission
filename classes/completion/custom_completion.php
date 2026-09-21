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

namespace mod_videomission\completion;

use core_completion\activity_custom_completion;
use mod_videomission\mission_manager;
use mod_videomission\progress_manager;

/**
 * Custom completion rules.
 *
 * @package   mod_videomission
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class custom_completion extends activity_custom_completion {
    /**
     * Method get_state.
     *
     * @param string $rule Parameter rule.
     * @return int Return value.
     */
    public function get_state(string $rule): int {
        global $DB;
        $activity = $DB->get_record('videomission', ['id' => $this->cm->instance], '*', MUST_EXIST);
        switch ($rule) {
            case 'completionmissions':
                $summary = mission_manager::summary((int)$activity->id, (int)$this->userid);
                return $summary->requiredpending === 0 ? COMPLETION_COMPLETE : COMPLETION_INCOMPLETE;
            case 'completionwatch':
                $progress = progress_manager::get((int)$activity->id, (int)$this->userid);
                return (float)$progress->percentage >= (float)$activity->completionwatch
                    ? COMPLETION_COMPLETE : COMPLETION_INCOMPLETE;
            default:
                return COMPLETION_INCOMPLETE;
        }
    }

    /**
     * Method get_defined_custom_rules.
     *
     * @return array Return value.
     */
    public static function get_defined_custom_rules(): array {
        return ['completionmissions', 'completionwatch'];
    }

    /**
     * Method get_custom_rule_descriptions.
     *
     * @return array Return value.
     */
    public function get_custom_rule_descriptions(): array {
        global $DB;
        $activity = $DB->get_record('videomission', ['id' => $this->cm->instance], '*', MUST_EXIST);
        return [
            'completionmissions' => get_string('completiondetail:missions', 'mod_videomission'),
            'completionwatch' => get_string('completiondetail:watch', 'mod_videomission', $activity->completionwatch),
        ];
    }

    /**
     * Method get_sort_order.
     *
     * @return array Return value.
     */
    public function get_sort_order(): array {
        return ['completionmissions', 'completionwatch'];
    }
}
