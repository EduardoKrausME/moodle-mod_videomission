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

use moodleform;

/**
 * Mission editing form.
 *
 * @package   mod_videomission
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mission_form extends moodleform {
    /**
     * Method definition.
     *
     * @return void Return value.
     */
    public function definition(): void {
        $mform = $this->_form;
        $mform->addElement('hidden', 'cmid');
        $mform->setType('cmid', PARAM_INT);
        $mform->addElement('hidden', 'missionid');
        $mform->setType('missionid', PARAM_INT);

        $mform->addElement('text', 'name', get_string('missionname', 'mod_videomission'), ['size' => 64]);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('editor', 'description_editor', get_string('missiondescription', 'mod_videomission'), null, [
            'maxfiles' => 0,
        ]);

        $types = [
            'moment' => get_string('missiontypemoment', 'mod_videomission'),
            'interval' => get_string('missiontypeinterval', 'mod_videomission'),
            'text' => get_string('missiontypetext', 'mod_videomission'),
            'observation' => get_string('missiontypeobservation', 'mod_videomission'),
            'occurrences' => get_string('missiontypeoccurrences', 'mod_videomission'),
            'confirm' => get_string('missiontypeconfirm', 'mod_videomission'),
        ];
        $mform->addElement('select', 'missiontype', get_string('missiontype', 'mod_videomission'), $types);
        $mform->addElement('advcheckbox', 'required', get_string('required', 'mod_videomission'));
        $mform->setDefault('required', 1);
        $mform->addElement('text', 'points', get_string('points', 'mod_videomission'), ['size' => 8]);
        $mform->setType('points', PARAM_FLOAT);
        $mform->setDefault('points', 1);
        $mform->addElement('text', 'requiredcount', get_string('requiredcount', 'mod_videomission'), ['size' => 8]);
        $mform->setType('requiredcount', PARAM_INT);
        $mform->setDefault('requiredcount', 1);
        $mform->addHelpButton('requiredcount', 'requiredcount', 'mod_videomission');
        $mform->hideIf('requiredcount', 'missiontype', 'neq', 'occurrences');

        $this->add_action_buttons();
    }

    /**
     * Method validation.
     *
     * @param mixed $data Parameter data.
     * @param mixed $files Parameter files.
     * @return array Return value.
     */
    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);
        if ((float)$data['points'] < 0) {
            $errors['points'] = get_string('error');
        }
        if (($data['missiontype'] ?? '') === 'occurrences' && (int)$data['requiredcount'] < 1) {
            $errors['requiredcount'] = get_string('error');
        }
        return $errors;
    }
}
