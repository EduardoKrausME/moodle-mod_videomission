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
 * Student activity view.
 *
 * @package   mod_videomission
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once(__DIR__ . '/lib.php');

use mod_videomission\mission_manager;
use mod_videomission\progress_manager;

$id = required_param('id', PARAM_INT);
$cm = get_coursemodule_from_id('videomission', $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$activity = $DB->get_record('videomission', ['id' => $cm->instance], '*', MUST_EXIST);
require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/videomission:view', $context);

$PAGE->set_url('/mod/videomission/view.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($activity->name));
$PAGE->set_heading($course->fullname);
$PAGE->set_context($context);

$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$event = \mod_videomission\event\course_module_viewed::create([
    'objectid' => (int)$activity->id,
    'context' => $context,
]);
$event->add_record_snapshot('videomission', $activity);
$event->trigger();

$canrespond = has_capability('mod/videomission:submit', $context);
$canmanage = has_capability('mod/videomission:manage', $context);
$canreport = has_capability('mod/videomission:viewreports', $context);

$progress = progress_manager::get((int)$activity->id, (int)$USER->id);
$summary = mission_manager::summary((int)$activity->id, (int)$USER->id);
$missions = $DB->get_records('videomission_missions', ['videomissionid' => $activity->id], 'sortorder ASC, id ASC');

$missiondata = [];
foreach ($missions as $mission) {
    $answer = $DB->get_record('videomission_answers', ['missionid' => $mission->id, 'userid' => $USER->id]);
    $locked = $canrespond && !mission_manager::is_unlocked($activity, $mission, (int)$USER->id);
    $occurrences = $answer ? json_decode((string)$answer->occurrences, true) : [];
    if (!is_array($occurrences)) {
        $occurrences = [];
    }
    $occurrencedata = [];
    foreach ($occurrences as $value) {
        $occurrencedata[] = ['time' => (float)$value, 'label' => videomission_format_seconds((float)$value)];
    }
    $missiondata[] = [
        'id' => (int)$mission->id,
        'name' => format_string($mission->name),
        'description' => format_text($mission->description, $mission->descriptionformat,
            ['context' => $context, 'overflowdiv' => true]),
        'required' => !empty($mission->required),
        'optional' => empty($mission->required),
        'points' => format_float((float)$mission->points, 2, true, true),
        'completed' => !empty($answer->completed),
        'pending' => empty($answer->completed),
        'locked' => $locked,
        'canrespond' => $canrespond && !$locked,
        'response' => $answer ? (string)$answer->response : '',
        'starttime' => $answer && $answer->starttime !== null ? (float)$answer->starttime : '',
        'endtime' => $answer && $answer->endtime !== null ? (float)$answer->endtime : '',
        'startlabel' => $answer && $answer->starttime !== null ? videomission_format_seconds((float)$answer->starttime) : '--:--',
        'endlabel' => $answer && $answer->endtime !== null ? videomission_format_seconds((float)$answer->endtime) : '--:--',
        'occurrencesjson' => json_encode(array_values($occurrences)),
        'occurrences' => $occurrencedata,
        'requiredcount' => (int)$mission->requiredcount,
        'ismoment' => $mission->missiontype === mission_manager::TYPE_MOMENT,
        'isinterval' => $mission->missiontype === mission_manager::TYPE_INTERVAL,
        'istext' => $mission->missiontype === mission_manager::TYPE_TEXT,
        'isobservation' => $mission->missiontype === mission_manager::TYPE_OBSERVATION,
        'isoccurrences' => $mission->missiontype === mission_manager::TYPE_OCCURRENCES,
        'isconfirm' => $mission->missiontype === mission_manager::TYPE_CONFIRM,
        'confirmed' => $answer && !empty($answer->completed) && $mission->missiontype === mission_manager::TYPE_CONFIRM,
    ];
}

$videourl = videomission_get_video_url($activity, $context);
$youtubeid = $activity->sourcetype === 'youtube' ? videomission_youtube_id($videourl) : '';
$vimeoid = $activity->sourcetype === 'vimeo' ? videomission_vimeo_id($videourl) : '';
$sourcevalid = $videourl !== '' && ($activity->sourcetype !== 'youtube' || $youtubeid !== '')
    && ($activity->sourcetype !== 'vimeo' || $vimeoid !== '');
$overallprogress = videomission_calculate_overall_progress($activity, $summary, $progress);

$data = [
    'cmid' => (int)$cm->id,
    'activityid' => (int)$activity->id,
    'intro' => format_module_intro('videomission', $activity, $cm->id),
    'hasintro' => trim((string)$activity->intro) !== '',
    'sourcevalid' => $sourcevalid,
    'isdirect' => in_array($activity->sourcetype, ['upload', 'url'], true),
    'isyoutube' => $activity->sourcetype === 'youtube',
    'isvimeo' => $activity->sourcetype === 'vimeo',
    'videourl' => $videourl,
    'youtubeembed' => $youtubeid ? 'https://www.youtube-nocookie.com/embed/' .
        rawurlencode($youtubeid) . '?enablejsapi=1&rel=0' : '',
    'vimeoembed' => $vimeoid ? 'https://player.vimeo.com/video/' . rawurlencode($vimeoid) : '',
    'missions' => array_values($missiondata),
    'hasmissions' => !empty($missiondata),
    'completedmissions' => (int)$summary->completed,
    'totalmissions' => (int)$summary->total,
    'requiredcompleted' => (int)$summary->requiredcompleted,
    'requiredtotal' => (int)$summary->requiredtotal,
    'points' => format_float((float)$summary->points, 2, true, true),
    'maxpoints' => format_float((float)$summary->maxpoints, 2, true, true),
    'percentage' => format_float((float)$progress->percentage, 1, true, true),
    'progressvalue' => $overallprogress,
    'overallpercentage' => format_float($overallprogress, 1, true, true),
    'requiredwatch' => (int)$activity->requiredwatch,
    'haswatchgoal' => (int)$activity->requiredwatch > 0,
    'missionprogresslabel' => get_string('missionprogress', 'mod_videomission', (object)[
        'completed' => (int)$summary->completed,
        'total' => (int)$summary->total,
    ]),
    'mandatoryprogresslabel' => get_string('mandatoryprogress', 'mod_videomission', (object)[
        'completed' => (int)$summary->requiredcompleted,
        'total' => (int)$summary->requiredtotal,
    ]),
    'videoprogresslabel' => get_string('videoprogress', 'mod_videomission',
        format_float((float)$progress->percentage, 1, true, true)),
    'watchgoallabel' => get_string('watchgoal', 'mod_videomission', (int)$activity->requiredwatch),
    'canmanage' => $canmanage,
    'canreport' => $canreport,
    'manageurl' => (new moodle_url('/mod/videomission/missions.php', ['id' => $cm->id]))->out(false),
    'reporturl' => (new moodle_url('/mod/videomission/report.php', ['id' => $cm->id]))->out(false),
];

$PAGE->requires->js_call_amd('mod_videomission/player', 'init', [[
    'cmid' => (int)$cm->id,
    'source' => $activity->sourcetype,
    'resume' => !empty($activity->resumeplayback),
    'restrictseek' => !empty($activity->restrictseek),
    'lastposition' => (float)$progress->lastposition,
    'ranges' => json_decode((string)$progress->watchedsegments, true) ?: [],
    'playtime' => (float)$progress->playtime,
    'sequential' => !empty($activity->missionorder),
    'completedmissions' => (int)$summary->completed,
    'totalmissions' => (int)$summary->total,
]]);

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($activity->name));
echo $OUTPUT->render_from_template('mod_videomission/view', $data);
echo $OUTPUT->footer();
