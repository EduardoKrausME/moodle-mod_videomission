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
 * Teacher report.
 *
 * @package   mod_videomission
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once(__DIR__ . '/lib.php');
require_once($CFG->libdir . '/csvlib.class.php');

use mod_videomission\mission_manager;
use mod_videomission\progress_manager;

$id = required_param('id', PARAM_INT);
$download = optional_param('download', '', PARAM_ALPHA);
$cm = get_coursemodule_from_id('videomission', $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$activity = $DB->get_record('videomission', ['id' => $cm->instance], '*', MUST_EXIST);
require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/videomission:viewreports', $context);

$PAGE->set_url('/mod/videomission/report.php', ['id' => $cm->id]);
$PAGE->set_title(get_string('report', 'mod_videomission'));
$PAGE->set_heading($course->fullname);

$missions = $DB->get_records('videomission_missions', ['videomissionid' => $activity->id], 'sortorder ASC, id ASC');
$users = get_enrolled_users($context, 'mod/videomission:submit', 0,
    "u.id,u.firstname,u.lastname,u.email,u.picture,u.imagealt,u.firstnamephonetic,u.lastnamephonetic,u.middlename,u.alternatename",
    "u.lastname,u.firstname");
$rows = [];
foreach ($users as $user) {
    $summary = mission_manager::summary((int)$activity->id, (int)$user->id);
    $progress = progress_manager::get((int)$activity->id, (int)$user->id);
    $details = [];
    foreach ($missions as $mission) {
        $answer = $DB->get_record('videomission_answers', ['missionid' => $mission->id, 'userid' => $user->id]);
        if (!$answer) {
            $details[] = format_string($mission->name) . ': ' . get_string('pending', 'mod_videomission');
            continue;
        }
        $parts = [];
        if (trim((string)$answer->response) !== '') {
            $parts[] = trim((string)$answer->response);
        }
        if ($answer->starttime !== null) {
            $parts[] = videomission_format_seconds((float)$answer->starttime) .
                ($answer->endtime !== null ? '–' . videomission_format_seconds((float)$answer->endtime) : '');
        }
        $occurrences = json_decode((string)$answer->occurrences, true);
        if (is_array($occurrences) && $occurrences) {
            $parts[] = implode(', ', array_map(static fn($value):
            string => videomission_format_seconds((float)$value), $occurrences));
        }
        $details[] = format_string($mission->name) . ': ' . ($parts ? implode(' | ', $parts) :
                (!empty($answer->completed) ?
                    get_string('completed', 'mod_videomission') :
                    get_string('pending', 'mod_videomission')));
    }
    $rows[] = [
        'user' => fullname($user),
        'email' => $user->email,
        'completed' => $summary->completed,
        'pending' => $summary->pending,
        'points' => $summary->points,
        'maxpoints' => $summary->maxpoints,
        'watched' => (float)$progress->percentage,
        'updated' => max((int)$progress->timemodified, 0),
        'answers' => implode("\n", $details),
    ];
}

if ($download === 'csv') {
    $csv = new csv_export_writer();
    $csv->set_filename(get_string('csvfilename', 'mod_videomission'));
    $csv->add_data([
        get_string('student', 'mod_videomission'),
        get_string('email'),
        get_string('missionscompleted', 'mod_videomission'),
        get_string('missionspending', 'mod_videomission'),
        get_string('score', 'mod_videomission'),
        get_string('watched', 'mod_videomission'),
        get_string('lastaccess', 'mod_videomission'),
        get_string('answers', 'mod_videomission'),
    ]);
    foreach ($rows as $row) {
        $csv->add_data([$row['user'], $row['email'], $row['completed'], $row['pending'],
            $row['points'] . '/' . $row['maxpoints'], format_float($row['watched'], 1) . '%',
            $row['updated'] ? userdate($row['updated']) : '', $row['answers']]);
    }
    $csv->download_file();
    exit;
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('report', 'mod_videomission'));
echo $OUTPUT->single_button(new moodle_url('/mod/videomission/report.php', ['id' => $cm->id, 'download' => 'csv']),
    get_string('exportcsv', 'mod_videomission'), 'get');

if (!$rows) {
    echo $OUTPUT->notification(get_string('noattempts', 'mod_videomission'), 'info');
} else {
    $table = new html_table();
    $table->head = [get_string('student', 'mod_videomission'), get_string('missionscompleted', 'mod_videomission'),
        get_string('missionspending', 'mod_videomission'), get_string('score', 'mod_videomission'),
        get_string('watched', 'mod_videomission'), get_string('lastaccess', 'mod_videomission'),
        get_string('answers', 'mod_videomission')];
    foreach ($rows as $row) {
        $table->data[] = [s($row['user']), $row['completed'], $row['pending'],
            format_float((float)$row['points'], 2) . ' / ' . format_float((float)$row['maxpoints'], 2),
            format_float((float)$row['watched'], 1) . '%', $row['updated'] ? userdate($row['updated']) : '-',
            nl2br(s($row['answers']))];
    }
    echo html_writer::table($table);
}

echo $OUTPUT->single_button(new moodle_url('/mod/videomission/view.php', ['id' => $cm->id]),
    get_string('backtoactivity', 'mod_videomission'), 'get');
echo $OUTPUT->footer();
