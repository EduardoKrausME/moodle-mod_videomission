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
 * Add or edit a mission.
 *
 * @package   mod_videomission
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once("{$CFG->libdir}/formslib.php");
require_once(__DIR__ . '/lib.php');

$cmid = required_param('cmid', PARAM_INT);
$missionid = optional_param('missionid', 0, PARAM_INT);
$cm = get_coursemodule_from_id('videomission', $cmid, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$activity = $DB->get_record('videomission', ['id' => $cm->instance], '*', MUST_EXIST);
require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/videomission:manage', $context);

$PAGE->set_url('/mod/videomission/mission.php', ['cmid' => $cmid, 'missionid' => $missionid]);
$PAGE->set_title($missionid ? get_string('editmission', 'mod_videomission') : get_string('addmission', 'mod_videomission'));
$PAGE->set_heading($course->fullname);

$mission = null;
if ($missionid) {
    $mission = $DB->get_record('videomission_missions', ['id' => $missionid, 'videomissionid' => $activity->id],
        '*', MUST_EXIST);
}

$form = new \mod_videomission\mission_form();
if ($form->is_cancelled()) {
    redirect(new moodle_url('/mod/videomission/missions.php', ['id' => $cmid]));
}
if ($data = $form->get_data()) {
    $now = time();
    $record = (object)[
        'videomissionid' => $activity->id,
        'name' => $data->name,
        'description' => $data->description_editor['text'],
        'descriptionformat' => $data->description_editor['format'],
        'missiontype' => $data->missiontype,
        'required' => empty($data->required) ? 0 : 1,
        'points' => max(0, (float)$data->points),
        'requiredcount' => max(1, (int)$data->requiredcount),
        'timemodified' => $now,
    ];
    if (!empty($data->missionid)) {
        $record->id = $data->missionid;
        $DB->update_record('videomission_missions', $record);
        $DB->set_field_select(
            'videomission_answers',
            'score',
            (float)$record->points,
            'missionid = :missionid AND completed = 1',
            ['missionid' => $record->id]
        );
    } else {
        $record->sortorder = (int)$DB->get_field_sql(
            'SELECT COALESCE(MAX(sortorder), 0) + 1 FROM {videomission_missions} WHERE videomissionid = ?',
            [$activity->id]);
        $record->timecreated = $now;
        $DB->insert_record('videomission_missions', $record);
    }
    videomission_update_grades($activity);
    redirect(new moodle_url('/mod/videomission/missions.php', ['id' => $cmid]));
}

$defaults = (object)[
    'cmid' => $cmid,
    'missionid' => $missionid,
    'name' => $mission->name ?? '',
    'missiontype' => $mission->missiontype ?? 'text',
    'required' => $mission->required ?? 1,
    'points' => $mission->points ?? 1,
    'requiredcount' => $mission->requiredcount ?? 1,
    'description_editor' => [
        'text' => $mission->description ?? '',
        'format' => $mission->descriptionformat ?? FORMAT_HTML,
    ],
];
$form->set_data($defaults);

echo $OUTPUT->header();
echo $OUTPUT->heading($missionid ? get_string('editmission', 'mod_videomission') : get_string('addmission', 'mod_videomission'));
$form->display();
echo $OUTPUT->footer();
