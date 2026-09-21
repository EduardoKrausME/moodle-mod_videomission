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

namespace mod_videomission\privacy;

use context;
use context_module;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\writer;

/**
 * Privacy API provider.
 *
 * @package   mod_videomission
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    \core_privacy\local\request\core_userlist_provider {

    /**
     * Method get_metadata.
     *
     * @param collection $collection Parameter collection.
     * @return collection Return value.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table('videomission_answers', [
            'userid' => 'privacy:metadata:videomission_answers:userid',
            'response' => 'privacy:metadata:videomission_answers:response',
            'starttime' => 'privacy:metadata:videomission_answers:starttime',
            'endtime' => 'privacy:metadata:videomission_answers:endtime',
            'occurrences' => 'privacy:metadata:videomission_answers:occurrences',
            'completed' => 'privacy:metadata:videomission_answers:completed',
            'score' => 'privacy:metadata:videomission_answers:score',
            'timemodified' => 'privacy:metadata:videomission_answers:timemodified',
        ], 'privacy:metadata:videomission_answers');
        $collection->add_database_table('videomission_progress', [
            'userid' => 'privacy:metadata:videomission_progress:userid',
            'duration' => 'privacy:metadata:videomission_progress:duration',
            'watchedsegments' => 'privacy:metadata:videomission_progress:watchedsegments',
            'watchedseconds' => 'privacy:metadata:videomission_progress:watchedseconds',
            'percentage' => 'privacy:metadata:videomission_progress:percentage',
            'lastposition' => 'privacy:metadata:videomission_progress:lastposition',
            'playtime' => 'privacy:metadata:videomission_progress:playtime',
            'timemodified' => 'privacy:metadata:videomission_progress:timemodified',
        ], 'privacy:metadata:videomission_progress');
        return $collection;
    }

    /**
     * Method get_contexts_for_userid.
     *
     * @param int $userid Parameter userid.
     * @return contextlist Return value.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $sql = "SELECT DISTINCT ctx.id
                  FROM {context} ctx
                  JOIN {course_modules} cm ON cm.id = ctx.instanceid AND ctx.contextlevel = :contextlevel
                  JOIN {modules} md ON md.id = cm.module AND md.name = :modname
                  JOIN {videomission} v ON v.id = cm.instance
             LEFT JOIN {videomission_progress} p ON p.videomissionid = v.id AND p.userid = :progressuserid
             LEFT JOIN {videomission_missions} m ON m.videomissionid = v.id
             LEFT JOIN {videomission_answers} a ON a.missionid = m.id AND a.userid = :answeruserid
                 WHERE p.id IS NOT NULL OR a.id IS NOT NULL";
        $params = [
            'contextlevel' => CONTEXT_MODULE,
            'modname' => 'videomission',
            'progressuserid' => $userid,
            'answeruserid' => $userid,
        ];
        $contextlist = new contextlist();
        $contextlist->add_from_sql($sql, $params);
        return $contextlist;
    }

    /**
     * Method export_user_data.
     *
     * @param approved_contextlist $contextlist Parameter contextlist.
     * @return void Return value.
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;
        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof context_module) {
                continue;
            }
            $cm = get_coursemodule_from_id('videomission', $context->instanceid);
            if (!$cm) {
                continue;
            }
            $progress = $DB->get_record('videomission_progress', ['videomissionid' => $cm->instance, 'userid' => $userid]);
            if ($progress) {
                $data = (object)[
                    'percentage' => $progress->percentage,
                    'watchedseconds' => $progress->watchedseconds,
                    'duration' => $progress->duration,
                    'lastposition' => $progress->lastposition,
                    'playtime' => $progress->playtime,
                    'watchedsegments' => $progress->watchedsegments,
                    'timemodified' => transform::datetime($progress->timemodified),
                ];
                writer::with_context($context)->export_data([
                    get_string('videoprogress', 'mod_videomission', $progress->percentage),
                ], $data);
            }
            $sql = "SELECT a.*, m.name AS missionname
                      FROM {videomission_answers} a
                      JOIN {videomission_missions} m ON m.id = a.missionid
                     WHERE m.videomissionid = :activity AND a.userid = :userid";
            foreach ($DB->get_records_sql($sql, ['activity' => $cm->instance, 'userid' => $userid]) as $answer) {
                $data = (object)[
                    'response' => $answer->response,
                    'starttime' => $answer->starttime,
                    'endtime' => $answer->endtime,
                    'occurrences' => $answer->occurrences,
                    'completed' => transform::yesno($answer->completed),
                    'score' => $answer->score,
                    'timemodified' => transform::datetime($answer->timemodified),
                ];
                writer::with_context($context)->export_data([
                    get_string('missionlist', 'mod_videomission'), $answer->missionname,
                ], $data);
            }
        }
    }

    /**
     * Method delete_data_for_all_users_in_context.
     *
     * @param context $context Parameter context.
     * @return void Return value.
     */
    public static function delete_data_for_all_users_in_context(context $context): void {
        global $DB;
        if (!$context instanceof context_module) {
            return;
        }
        $cm = get_coursemodule_from_id('videomission', $context->instanceid);
        if (!$cm) {
            return;
        }
        $DB->delete_records('videomission_progress', ['videomissionid' => $cm->instance]);
        $missions = $DB->get_fieldset_select('videomission_missions', 'id',
            'videomissionid = :activity', ['activity' => $cm->instance]);
        if ($missions) {
            [$insql, $params] = $DB->get_in_or_equal($missions, SQL_PARAMS_NAMED);
            $DB->delete_records_select('videomission_answers', "missionid {$insql}", $params);
        }
    }

    /**
     * Method delete_data_for_user.
     *
     * @param approved_contextlist $contextlist Parameter contextlist.
     * @return void Return value.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        global $DB;
        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof context_module) {
                continue;
            }
            $cm = get_coursemodule_from_id('videomission', $context->instanceid);
            if (!$cm) {
                continue;
            }
            $DB->delete_records('videomission_progress', ['videomissionid' => $cm->instance, 'userid' => $userid]);
            $sql = "DELETE FROM {videomission_answers}
                     WHERE userid = :userid
                       AND missionid IN (SELECT id FROM {videomission_missions} WHERE videomissionid = :activity)";
            $DB->execute($sql, ['userid' => $userid, 'activity' => $cm->instance]);
        }
    }

    /**
     * Method get_users_in_context.
     *
     * @param \core_privacy\local\request\userlist $userlist Parameter userlist.
     * @return void Return value.
     */
    public static function get_users_in_context(\core_privacy\local\request\userlist $userlist): void {
        $context = $userlist->get_context();
        if (!$context instanceof context_module) {
            return;
        }
        $cm = get_coursemodule_from_id('videomission', $context->instanceid);
        if (!$cm) {
            return;
        }
        $sql = "SELECT p.userid FROM {videomission_progress} p WHERE p.videomissionid = :activity
                UNION
                SELECT a.userid
                  FROM {videomission_answers} a
                  JOIN {videomission_missions} m ON m.id = a.missionid
                 WHERE m.videomissionid = :activity2";
        $userlist->add_from_sql('userid', $sql, ['activity' => $cm->instance, 'activity2' => $cm->instance]);
    }

    /**
     * Method delete_data_for_users.
     *
     * @param approved_userlist $userlist Parameter userlist.
     * @return void Return value.
     */
    public static function delete_data_for_users(approved_userlist $userlist): void {
        global $DB;

        $context = $userlist->get_context();
        if (!$context instanceof context_module) {
            return;
        }
        $cm = get_coursemodule_from_id('videomission', $context->instanceid);
        if (!$cm) {
            return;
        }
        $userids = $userlist->get_userids();
        if (!$userids) {
            return;
        }

        [$usersql, $userparams] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED, 'vmuser');
        $params = ['activity' => $cm->instance] + $userparams;
        $DB->delete_records_select(
            'videomission_progress',
            "videomissionid = :activity AND userid {$usersql}",
            $params
        );

        $missions = $DB->get_fieldset_select(
            'videomission_missions',
            'id',
            'videomissionid = :activity',
            ['activity' => $cm->instance]
        );
        if ($missions) {
            [$missionsql, $missionparams] = $DB->get_in_or_equal($missions, SQL_PARAMS_NAMED, 'vmmission');
            $answerparams = $userparams + $missionparams;
            $DB->delete_records_select(
                'videomission_answers',
                "userid {$usersql} AND missionid {$missionsql}",
                $answerparams
            );
        }
    }
}
