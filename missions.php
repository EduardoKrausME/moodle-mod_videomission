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
 * Mission management page.
 *
 * @package   mod_videomission
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once(__DIR__ . '/lib.php');

$id = required_param('id', PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHA);
$missionid = optional_param('missionid', 0, PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_BOOL);

$cm = get_coursemodule_from_id('videomission', $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$activity = $DB->get_record('videomission', ['id' => $cm->instance], '*', MUST_EXIST);
require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/videomission:manage', $context);

$PAGE->set_url('/mod/videomission/missions.php', ['id' => $cm->id]);
$PAGE->set_title(get_string('managemissions', 'mod_videomission'));
$PAGE->set_heading($course->fullname);

if ($action !== '' && $missionid > 0) {
    require_sesskey();
    $mission = $DB->get_record('videomission_missions', ['id' => $missionid, 'videomissionid' => $activity->id],
        '*', MUST_EXIST);

    if ($action === 'delete') {
        if (!$confirm) {
            echo $OUTPUT->header();
            $confirmurl = new moodle_url('/mod/videomission/missions.php', [
                'id' => $cm->id,
                'action' => 'delete',
                'missionid' => $mission->id,
                'confirm' => 1,
                'sesskey' => sesskey(),
            ]);
            $cancelurl = new moodle_url('/mod/videomission/missions.php', ['id' => $cm->id]);
            echo $OUTPUT->confirm(get_string('confirmdeletemission', 'mod_videomission', format_string($mission->name)),
                $confirmurl, $cancelurl);
            echo $OUTPUT->footer();
            exit;
        }
        $affecteduserids = $DB->get_fieldset_select(
            'videomission_answers',
            'userid',
            'missionid = :missionid',
            ['missionid' => $mission->id]
        );
        $DB->delete_records('videomission_answers', ['missionid' => $mission->id]);
        $DB->delete_records('videomission_missions', ['id' => $mission->id]);
        foreach ($affecteduserids as $affecteduserid) {
            videomission_update_grades($activity, (int)$affecteduserid, false);
        }
    } else if ($action === 'up' || $action === 'down') {
        $operator = $action === 'up' ? '<' : '>';
        $direction = $action === 'up' ? 'DESC' : 'ASC';
        $other = $DB->get_record_sql("SELECT *
                                       FROM {videomission_missions}
                                      WHERE videomissionid = :activity
                                        AND sortorder {$operator} :sortorder
                                   ORDER BY sortorder {$direction}, id {$direction}",
            ['activity' => $activity->id, 'sortorder' => $mission->sortorder], IGNORE_MULTIPLE);
        if ($other) {
            $oldorder = $mission->sortorder;
            $mission->sortorder = $other->sortorder;
            $other->sortorder = $oldorder;
            $DB->update_record('videomission_missions', $mission);
            $DB->update_record('videomission_missions', $other);
        }
    }
    redirect(new moodle_url('/mod/videomission/missions.php', ['id' => $cm->id]));
}

$missions = $DB->get_records('videomission_missions', ['videomissionid' => $activity->id], 'sortorder ASC, id ASC');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('managemissions', 'mod_videomission'));
echo $OUTPUT->single_button(new moodle_url('/mod/videomission/mission.php', ['cmid' => $cm->id]),
    get_string('addmission', 'mod_videomission'), 'get');

if (!$missions) {
    echo $OUTPUT->notification(get_string('nomissions', 'mod_videomission'), 'info');
} else {
    $table = new html_table();
    $table->head = [get_string('missionname', 'mod_videomission'), get_string('missiontype', 'mod_videomission'),
        get_string('required', 'mod_videomission'), get_string('points', 'mod_videomission'), get_string('actions')];
    foreach ($missions as $mission) {
        $typekey = 'missiontype' . $mission->missiontype;
        $edit = new moodle_url('/mod/videomission/mission.php', ['cmid' => $cm->id, 'missionid' => $mission->id]);
        $up = new moodle_url('/mod/videomission/missions.php', ['id' => $cm->id, 'action' => 'up',
            'missionid' => $mission->id, 'sesskey' => sesskey()]);
        $down = new moodle_url('/mod/videomission/missions.php', ['id' => $cm->id, 'action' => 'down',
            'missionid' => $mission->id, 'sesskey' => sesskey()]);
        $delete = new moodle_url('/mod/videomission/missions.php', ['id' => $cm->id, 'action' => 'delete',
            'missionid' => $mission->id, 'sesskey' => sesskey()]);
        $actions = html_writer::link($edit, get_string('edit')) . ' · ' .
            html_writer::link($up, get_string('moveup', 'mod_videomission')) . ' · ' .
            html_writer::link($down, get_string('movedown', 'mod_videomission')) . ' · ' .
            html_writer::link($delete, get_string('delete'));
        $table->data[] = [
            format_string($mission->name),
            get_string($typekey, 'mod_videomission'),
            !empty($mission->required) ? get_string('yes') : get_string('no'),
            format_float((float)$mission->points, 2, true, true),
            $actions,
        ];
    }
    echo html_writer::table($table);
}

echo $OUTPUT->single_button(new moodle_url('/mod/videomission/view.php', ['id' => $cm->id]),
    get_string('backtoactivity', 'mod_videomission'), 'get');
echo $OUTPUT->footer();
