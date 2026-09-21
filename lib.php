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
 * Core callbacks for Video Mission.
 *
 * @package   mod_videomission
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_videomission\mission_manager;
use mod_videomission\progress_manager;

/**
 * Supported Moodle features.
 *
 * @param string $feature
 * @return bool|int|string|null
 */
function videomission_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_ARCHETYPE:
            return MOD_ARCHETYPE_OTHER;
        case FEATURE_MOD_PURPOSE:
            return MOD_PURPOSE_CONTENT;
        case FEATURE_MOD_INTRO:
        case FEATURE_SHOW_DESCRIPTION:
        case FEATURE_COMPLETION:
        case FEATURE_COMPLETION_TRACKS_VIEWS:
        case FEATURE_COMPLETION_HAS_RULES:
        case FEATURE_GRADE_HAS_GRADE:
        case FEATURE_BACKUP_MOODLE2:
            return true;
        default:
            return null;
    }
}

/**
 * Add an activity instance.
 *
 * @param stdClass $data
 * @param mod_videomission_mod_form|null $mform
 * @return int
 */
function videomission_add_instance($data, $mform = null): int {
    global $DB;

    $data->timecreated = time();
    $data->timemodified = $data->timecreated;
    $data->id = $DB->insert_record('videomission', $data);

    if (!empty($data->coursemodule) && !empty($data->video)) {
        $context = context_module::instance($data->coursemodule);
        file_save_draft_area_files($data->video, $context->id, 'mod_videomission', 'video', 0,
            ['subdirs' => 0, 'maxfiles' => 1]);
    }

    videomission_grade_item_update($data);
    return (int)$data->id;
}

/**
 * Update an activity instance.
 *
 * @param stdClass $data
 * @param mod_videomission_mod_form|null $mform
 * @return bool
 */
function videomission_update_instance($data, $mform = null): bool {
    global $DB;

    $data->id = $data->instance;
    $data->timemodified = time();
    $DB->update_record('videomission', $data);

    if (!empty($data->coursemodule) && !empty($data->video)) {
        $context = context_module::instance($data->coursemodule);
        file_save_draft_area_files($data->video, $context->id, 'mod_videomission', 'video', 0,
            ['subdirs' => 0, 'maxfiles' => 1]);
    }

    videomission_grade_item_update($data);
    videomission_update_grades($data);
    return true;
}

/**
 * Delete an activity instance.
 *
 * @param int $id
 * @return bool
 */
function videomission_delete_instance($id): bool {
    global $DB;

    $activity = $DB->get_record('videomission', ['id' => $id]);
    if (!$activity) {
        return false;
    }

    $missions = $DB->get_records('videomission_missions', ['videomissionid' => $id], '', 'id');
    if ($missions) {
        $missionids = array_keys($missions);
        [$insql, $params] = $DB->get_in_or_equal($missionids, SQL_PARAMS_NAMED);
        $DB->delete_records_select('videomission_answers', "missionid {$insql}", $params);
    }
    $DB->delete_records('videomission_missions', ['videomissionid' => $id]);
    $DB->delete_records('videomission_progress', ['videomissionid' => $id]);
    $DB->delete_records('videomission', ['id' => $id]);

    if ($cm = get_coursemodule_from_instance('videomission', $id)) {
        $context = context_module::instance($cm->id);
        get_file_storage()->delete_area_files($context->id, 'mod_videomission');
    }

    videomission_grade_item_delete($activity);
    return true;
}

/**
 * File serving callback.
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return bool
 */
function videomission_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []): bool {
    if ($context->contextlevel !== CONTEXT_MODULE || $filearea !== 'video') {
        return false;
    }
    require_login($course, true, $cm);
    require_capability('mod/videomission:view', $context);

    $itemid = array_shift($args);
    if ((int)$itemid !== 0) {
        return false;
    }
    $filename = array_pop($args);
    $filepath = '/' . implode('/', $args) . '/';
    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'mod_videomission', 'video', 0, $filepath, $filename);
    if (!$file || $file->is_directory()) {
        return false;
    }
    send_stored_file($file, 0, 0, false, $options);
    return true;
}

/**
 * Return the playable source URL for an activity.
 *
 * @param stdClass $activity
 * @param context_module $context
 * @return string
 */
function videomission_get_video_url($activity, context_module $context): string {
    if ($activity->sourcetype !== 'upload') {
        return trim((string)$activity->videourl);
    }
    $files = get_file_storage()->get_area_files($context->id, 'mod_videomission', 'video', 0,
        'itemid, filepath, filename', false);
    if (!$files) {
        return '';
    }
    $file = reset($files);
    return moodle_url::make_pluginfile_url($context->id, 'mod_videomission', 'video', 0,
        $file->get_filepath(), $file->get_filename())->out(false);
}

/**
 * Extract a YouTube video ID.
 *
 * @param string $url
 * @return string
 */
function videomission_youtube_id(string $url): string {
    $patterns = [
        '~youtu\.be/([A-Za-z0-9_-]{6,})~',
        '~youtube(?:-nocookie)?\.com/(?:watch\?[^#]*v=|embed/|shorts/)([A-Za-z0-9_-]{6,})~',
    ];
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }
    }
    return '';
}

/**
 * Extract a Vimeo video ID.
 *
 * @param string $url
 * @return string
 */
function videomission_vimeo_id(string $url): string {
    if (preg_match('~vimeo\.com/(?:video/)?([0-9]+)~', $url, $matches)) {
        return $matches[1];
    }
    return '';
}

/**
 * Create or update the grade item.
 *
 * @param stdClass $activity
 * @param mixed $grades
 * @return int
 */
function videomission_grade_item_update($activity, $grades = null): int {
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');

    $params = [
        'itemname' => $activity->name,
        'gradetype' => GRADE_TYPE_VALUE,
        'grademax' => max(0, (float)$activity->grade),
        'grademin' => 0,
    ];
    return grade_update('mod/videomission', $activity->course, 'mod', 'videomission', $activity->id, 0, $grades, $params);
}

/**
 * Delete the grade item.
 *
 * @param stdClass $activity
 * @return int
 */
function videomission_grade_item_delete($activity): int {
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');
    return grade_update('mod/videomission', $activity->course, 'mod', 'videomission', $activity->id, 0, null,
        ['deleted' => 1]);
}

/**
 * Calculate one user's activity grade.
 *
 * @param stdClass $activity
 * @param int $userid
 * @return float
 */
function videomission_calculate_grade($activity, int $userid): float {
    $summary = mission_manager::summary((int)$activity->id, $userid);
    if ($summary->maxpoints <= 0 || (float)$activity->grade <= 0) {
        return 0.0;
    }
    return round(($summary->points / $summary->maxpoints) * (float)$activity->grade, 5);
}

/**
 * Push grades to gradebook.
 *
 * @param stdClass $activity
 * @param int $userid
 * @param bool $nullifnone
 * @return void
 */
function videomission_update_grades($activity, int $userid = 0, bool $nullifnone = true): void {
    global $DB;

    $grades = [];
    if ($userid > 0) {
        $grades[$userid] = (object)['userid' => $userid, 'rawgrade' => videomission_calculate_grade($activity, $userid)];
    } else {
        $sql = "SELECT DISTINCT a.userid
                  FROM {videomission_answers} a
                  JOIN {videomission_missions} m ON m.id = a.missionid
                 WHERE m.videomissionid = :activity";
        $userids = $DB->get_fieldset_sql($sql, ['activity' => $activity->id]);
        foreach ($userids as $id) {
            $grades[$id] = (object)['userid' => $id, 'rawgrade' => videomission_calculate_grade($activity, (int)$id)];
        }
    }
    videomission_grade_item_update($activity, $grades ?: null);
}

/**
 * Return user grades for Moodle gradebook.
 *
 * @param stdClass $activity
 * @param int $userid
 * @return array
 */
function videomission_get_user_grades($activity, int $userid = 0): array {
    global $DB;
    $result = [];
    if ($userid > 0) {
        $result[$userid] = (object)['id' => $userid, 'userid' => $userid,
            'rawgrade' => videomission_calculate_grade($activity, $userid)];
        return $result;
    }
    $sql = "SELECT DISTINCT a.userid
              FROM {videomission_answers} a
              JOIN {videomission_missions} m ON m.id = a.missionid
             WHERE m.videomissionid = :activity";
    foreach ($DB->get_fieldset_sql($sql, ['activity' => $activity->id]) as $id) {
        $result[$id] = (object)['id' => $id, 'userid' => $id,
            'rawgrade' => videomission_calculate_grade($activity, (int)$id)];
    }
    return $result;
}

/**
 * Completion state callback.
 *
 * @param stdClass $course
 * @param cm_info $cm
 * @param int $userid
 * @param int $type
 * @return bool
 */
function videomission_get_completion_state($course, $cm, $userid, $type): bool {
    global $DB;
    $activity = $DB->get_record('videomission', ['id' => $cm->instance], '*', MUST_EXIST);
    $conditions = [];

    if (!empty($activity->completionmissions)) {
        $summary = mission_manager::summary((int)$activity->id, (int)$userid);
        $conditions[] = $summary->requiredpending === 0;
    }
    if (!empty($activity->completionwatch)) {
        $progress = progress_manager::get((int)$activity->id, (int)$userid);
        $conditions[] = (float)$progress->percentage >= (float)$activity->completionwatch;
    }
    if ($conditions === []) {
        return false;
    }
    if ($type === COMPLETION_AND) {
        return !in_array(false, $conditions, true);
    }
    return in_array(true, $conditions, true);
}

/**
 * Calculate the overall activity progress from missions and the viewing goal.
 *
 * Mission progress considers every configured mission. When a viewing goal is
 * configured, the video component reaches 100% when that goal is reached.
 *
 * @param stdClass $activity
 * @param object $summary
 * @param object $progress
 * @return float
 */
function videomission_calculate_overall_progress($activity, object $summary, object $progress): float {
    $parts = [];
    if ((int)$summary->total > 0) {
        $parts[] = min(100.0, max(0.0, ((float)$summary->completed / (float)$summary->total) * 100.0));
    }
    if ((int)$activity->requiredwatch > 0) {
        $parts[] = min(100.0, max(0.0, ((float)$progress->percentage / (float)$activity->requiredwatch) * 100.0));
    } else if ($parts === []) {
        $parts[] = min(100.0, max(0.0, (float)$progress->percentage));
    }
    return round(array_sum($parts) / count($parts), 2);
}

/**
 * Add module-specific information for course display.
 *
 * @param cm_info $cm
 * @return cached_cm_info
 */
function videomission_get_coursemodule_info($cm) {
    global $DB;
    $info = new cached_cm_info();
    $activity = $DB->get_record('videomission', ['id' => $cm->instance], 'id,name,intro,introformat');
    if ($activity) {
        $info->name = $activity->name;
        if ($cm->showdescription) {
            $info->content = format_module_intro('videomission', $activity, $cm->id, false);
        }
    }
    return $info;
}

/**
 * Describe custom completion rules.
 *
 * @param cm_info|stdClass $cm
 * @return array
 */
function videomission_get_completion_active_rule_descriptions($cm): array {
    global $DB;
    $activity = $DB->get_record('videomission', ['id' => $cm->instance], '*', MUST_EXIST);
    $rules = [];
    if (!empty($activity->completionmissions)) {
        $rules[] = get_string('completiondetail:missions', 'mod_videomission');
    }
    if (!empty($activity->completionwatch)) {
        $rules[] = get_string('completiondetail:watch', 'mod_videomission', $activity->completionwatch);
    }
    return $rules;
}

/**
 * Format seconds as hh:mm:ss or mm:ss.
 *
 * @param float $seconds
 * @return string
 */
function videomission_format_seconds(float $seconds): string {
    $seconds = max(0, (int)round($seconds));
    $hours = intdiv($seconds, 3600);
    $minutes = intdiv($seconds % 3600, 60);
    $secs = $seconds % 60;
    if ($hours > 0) {
        return sprintf('%d:%02d:%02d', $hours, $minutes, $secs);
    }
    return sprintf('%d:%02d', $minutes, $secs);
}
