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
 * Restore structure.
 *
 * @package   mod_videomission
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_videomission_activity_structure_step extends restore_activity_structure_step {
    /**
     * Method define_structure.
     *
     * @return array Return value.
     */
    protected function define_structure(): array {
        $paths = [];
        $paths[] = new restore_path_element('videomission', '/activity/videomission');
        $paths[] = new restore_path_element('videomission_mission', '/activity/videomission/missions/mission');
        if ($this->get_setting_value('userinfo')) {
            $paths[] = new restore_path_element('videomission_answer', '/activity/videomission/missions/mission/answers/answer');
            $paths[] = new restore_path_element('videomission_progress', '/activity/videomission/progresses/progress');
        }
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Method process_videomission.
     *
     * @param mixed $data Parameter data.
     * @return void Return value.
     */
    protected function process_videomission($data): void {
        global $DB;
        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();
        $data->timemodified = $this->apply_date_offset($data->timemodified);
        $newitemid = $DB->insert_record('videomission', $data);
        $this->apply_activity_instance($newitemid);
        $this->set_mapping('videomission', $oldid, $newitemid, true);
    }

    /**
     * Method process_videomission_mission.
     *
     * @param mixed $data Parameter data.
     * @return void Return value.
     */
    protected function process_videomission_mission($data): void {
        global $DB;
        $data = (object)$data;
        $oldid = $data->id;
        $data->videomissionid = $this->get_new_parentid('videomission');
        $newitemid = $DB->insert_record('videomission_missions', $data);
        $this->set_mapping('videomission_mission', $oldid, $newitemid, true);
    }

    /**
     * Method process_videomission_answer.
     *
     * @param mixed $data Parameter data.
     * @return void Return value.
     */
    protected function process_videomission_answer($data): void {
        global $DB;
        $data = (object)$data;
        $data->missionid = $this->get_new_parentid('videomission_mission');
        $data->userid = $this->get_mappingid('user', $data->userid);
        if ($data->userid) {
            $DB->insert_record('videomission_answers', $data);
        }
    }

    /**
     * Method process_videomission_progress.
     *
     * @param mixed $data Parameter data.
     * @return void Return value.
     */
    protected function process_videomission_progress($data): void {
        global $DB;
        $data = (object)$data;
        $data->videomissionid = $this->get_new_parentid('videomission');
        $data->userid = $this->get_mappingid('user', $data->userid);
        if ($data->userid) {
            $DB->insert_record('videomission_progress', $data);
        }
    }

    /**
     * Method after_execute.
     *
     * @return void Return value.
     */
    protected function after_execute(): void {
        $this->add_related_files('mod_videomission', 'intro', null);
        $this->add_related_files('mod_videomission', 'video', null);
    }
}
