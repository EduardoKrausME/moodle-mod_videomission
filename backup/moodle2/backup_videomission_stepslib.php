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

/**
 * Backup structure.
 *
 * @package   mod_videomission
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_videomission_activity_structure_step extends backup_activity_structure_step {
    /**
     * Method define_structure.
     *
     * @return mixed Return value.
     */
    protected function define_structure() {
        $userinfo = $this->get_setting_value('userinfo');

        $activity = new backup_nested_element('videomission', ['id'], [
            'course', 'name', 'intro', 'introformat', 'sourcetype', 'videourl', 'requiredwatch', 'restrictseek',
            'resumeplayback', 'missionorder', 'completionmissions', 'completionwatch', 'grade', 'timecreated', 'timemodified',
        ]);
        $missions = new backup_nested_element('missions');
        $mission = new backup_nested_element('mission', ['id'], [
            'sortorder', 'name', 'description', 'descriptionformat', 'missiontype', 'required', 'points', 'requiredcount',
            'timecreated', 'timemodified',
        ]);
        $answers = new backup_nested_element('answers');
        $answer = new backup_nested_element('answer', ['id'], [
            'userid', 'response', 'starttime', 'endtime', 'occurrences', 'completed', 'score', 'timecreated', 'timemodified',
        ]);
        $progresses = new backup_nested_element('progresses');
        $progress = new backup_nested_element('progress', ['id'], [
            'userid', 'duration', 'watchedsegments', 'watchedseconds', 'percentage', 'lastposition', 'playtime',
            'timecreated', 'timemodified',
        ]);

        $activity->add_child($missions);
        $missions->add_child($mission);
        $mission->add_child($answers);
        $answers->add_child($answer);
        $activity->add_child($progresses);
        $progresses->add_child($progress);

        $activity->set_source_table('videomission', ['id' => backup::VAR_ACTIVITYID]);
        $mission->set_source_table('videomission_missions', ['videomissionid' => backup::VAR_PARENTID]);
        if ($userinfo) {
            $answer->set_source_table('videomission_answers', ['missionid' => backup::VAR_PARENTID]);
            $progress->set_source_table('videomission_progress', ['videomissionid' => backup::VAR_ACTIVITYID]);
            $answer->annotate_ids('user', 'userid');
            $progress->annotate_ids('user', 'userid');
        }
        $activity->annotate_files('mod_videomission', 'intro', null);
        $activity->annotate_files('mod_videomission', 'video', null);
        return $this->prepare_activity_structure($activity);
    }
}
