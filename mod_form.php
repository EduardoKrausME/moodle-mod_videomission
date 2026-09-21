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
 * Activity settings form.
 *
 * @package   mod_videomission
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/course/moodleform_mod.php');

/**
 * Class mod_videomission_mod_form.
 */
class mod_videomission_mod_form extends moodleform_mod {
    /**
     * Method definition.
     *
     * @return void Return value.
     */
    public function definition(): void {
        $mform = $this->_form;

        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('text', 'name', get_string('name'), ['size' => 64]);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $this->standard_intro_elements();

        $mform->addElement('header', 'videosource', get_string('source', 'mod_videomission'));
        $sources = [
            'upload' => get_string('sourceupload', 'mod_videomission'),
            'url' => get_string('sourceurl', 'mod_videomission'),
            'youtube' => get_string('sourceyoutube', 'mod_videomission'),
            'vimeo' => get_string('sourcevimeo', 'mod_videomission'),
        ];
        $mform->addElement('select', 'sourcetype', get_string('source', 'mod_videomission'), $sources);
        $mform->setDefault('sourcetype', 'upload');

        $mform->addElement('filemanager', 'video', get_string('videofile', 'mod_videomission'), null, [
            'subdirs' => 0,
            'maxfiles' => 1,
            'accepted_types' => ['video'],
        ]);
        $mform->addHelpButton('video', 'videofile', 'mod_videomission');
        $mform->hideIf('video', 'sourcetype', 'neq', 'upload');

        $mform->addElement('url', 'videourl', get_string('videourl', 'mod_videomission'), ['size' => 64]);
        $mform->setType('videourl', PARAM_URL);
        $mform->addHelpButton('videourl', 'videourl', 'mod_videomission');
        $mform->hideIf('videourl', 'sourcetype', 'eq', 'upload');

        $mform->addElement('header', 'trackingheader', get_string('tracking', 'mod_videomission'));
        $mform->addElement('select', 'requiredwatch', get_string('requiredwatch', 'mod_videomission'),
            array_combine(range(0, 100, 5), array_map(static fn($v) => $v . '%', range(0, 100, 5))));
        $mform->setDefault('requiredwatch', 80);
        $mform->addHelpButton('requiredwatch', 'requiredwatch', 'mod_videomission');
        $mform->addElement('advcheckbox', 'restrictseek', get_string('restrictseek', 'mod_videomission'));
        $mform->addHelpButton('restrictseek', 'restrictseek', 'mod_videomission');
        $mform->addElement('advcheckbox', 'resumeplayback', get_string('resumeplayback', 'mod_videomission'));
        $mform->setDefault('resumeplayback', 1);
        $mform->addHelpButton('resumeplayback', 'resumeplayback', 'mod_videomission');
        $mform->addElement('select', 'missionorder', get_string('missionorder', 'mod_videomission'), [
            0 => get_string('missionorderany', 'mod_videomission'),
            1 => get_string('missionordersequential', 'mod_videomission'),
        ]);
        $mform->addHelpButton('missionorder', 'missionorder', 'mod_videomission');

        $mform->addElement('text', 'grade', get_string('grade', 'mod_videomission'), ['size' => 8]);
        $mform->setType('grade', PARAM_FLOAT);
        $mform->setDefault('grade', 100);

        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }

    /**
     * Method add_completion_rules.
     *
     * @return array Return value.
     */
    public function add_completion_rules(): array {
        $mform = $this->_form;
        $mform->addElement('checkbox', 'completionmissions', get_string('completionmissions', 'mod_videomission'));
        $mform->addHelpButton('completionmissions', 'completionmissions', 'mod_videomission');
        $mform->setDefault('completionmissions', 1);

        $options = [0 => get_string('none')];
        for ($i = 5; $i <= 100; $i += 5) {
            $options[$i] = $i . '%';
        }
        $mform->addElement('select', 'completionwatch', get_string('completionwatch', 'mod_videomission'), $options);
        $mform->addHelpButton('completionwatch', 'completionwatch', 'mod_videomission');
        return ['completionmissions', 'completionwatch'];
    }

    /**
     * Method completion_rule_enabled.
     *
     * @param mixed $data Parameter data.
     * @return bool Return value.
     */
    public function completion_rule_enabled($data): bool {
        return !empty($data['completionmissions']) || !empty($data['completionwatch']);
    }

    /**
     * Method data_preprocessing.
     *
     * @param mixed $defaultvalues Parameter defaultvalues.
     * @return void Return value.
     */
    public function data_preprocessing(&$defaultvalues): void {
        parent::data_preprocessing($defaultvalues);
        if (!empty($this->current->instance) && $this->context) {
            $draftitemid = file_get_submitted_draft_itemid('video');
            file_prepare_draft_area($draftitemid, $this->context->id, 'mod_videomission', 'video', 0, [
                'subdirs' => 0,
                'maxfiles' => 1,
                'accepted_types' => ['video'],
            ]);
            $defaultvalues['video'] = $draftitemid;
        }
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
        if (($data['sourcetype'] ?? 'upload') !== 'upload' && empty($data['videourl'])) {
            $errors['videourl'] = get_string('required');
        }
        if (isset($data['grade']) && (float)$data['grade'] < 0) {
            $errors['grade'] = get_string('error');
        }
        return $errors;
    }
}
