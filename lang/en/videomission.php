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
 * English language strings.
 *
 * @package   mod_videomission
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['addmission'] = 'Add mission';
$string['addoccurrence'] = 'Add occurrence';
$string['answers'] = 'Answers';
$string['backtoactivity'] = 'Back to activity';
$string['completed'] = 'Completed';
$string['completiondetail:missions'] = 'Complete all mandatory missions';
$string['completiondetail:watch'] = 'Watch at least {$a}% of the video';
$string['completionmissions'] = 'Require all mandatory missions';
$string['completionmissions_help'] = 'The activity is complete only after every mandatory mission is completed.';
$string['completionwatch'] = 'Require watched percentage';
$string['completionwatch_help'] = 'Minimum unique watched percentage required for completion. Use 0 to disable this rule.';
$string['confirmdeletemission'] = 'Delete mission "{$a}"?';
$string['confirmtask'] = 'I confirm that I completed this task';
$string['csvfilename'] = 'video-mission-report';
$string['deletemission'] = 'Delete mission';
$string['editmission'] = 'Edit mission';
$string['exportcsv'] = 'Export CSV';
$string['grade'] = 'Maximum grade';
$string['invalidmission'] = 'Invalid mission.';
$string['invalidresponse'] = 'The mission response is incomplete.';
$string['invalidvideo'] = 'The video could not be loaded.';
$string['lastaccess'] = 'Last update';
$string['locked'] = 'Locked';
$string['managemissions'] = 'Manage missions';
$string['mandatoryprogress'] = '{$a->completed} of {$a->total} mandatory missions completed';
$string['missiondescription'] = 'Instructions';
$string['missionlist'] = 'Mission list';
$string['missionlocked'] = 'Complete the previous mission to unlock this mission.';
$string['missionlockederror'] = 'This mission is still locked.';
$string['missionname'] = 'Mission name';
$string['missionoptional'] = 'Optional';
$string['missionorder'] = 'Mission order';
$string['missionorder_help'] = 'In sequential mode, the next mission is unlocked after the previous mission is completed.';
$string['missionorderany'] = 'Any order';
$string['missionordersequential'] = 'Sequential';
$string['missionpoints'] = '{$a} points';
$string['missionprogress'] = '{$a->completed} of {$a->total} missions completed';
$string['missionrequired'] = 'Mandatory';
$string['missionscompleted'] = 'Missions completed';
$string['missionspending'] = 'Missions pending';
$string['missiontype'] = 'Mission type';
$string['missiontypeconfirm'] = 'Confirm a task';
$string['missiontypeinterval'] = 'Select an interval';
$string['missiontypemoment'] = 'Select a moment';
$string['missiontypeobservation'] = 'Write an observation';
$string['missiontypeoccurrences'] = 'Find occurrences';
$string['missiontypetext'] = 'Answer a question';
$string['modulename'] = 'Video Mission';
$string['modulename_help'] = 'Turn a video into a mission-based activity with objectives, timestamped evidence, responses and progress tracking.';
$string['modulenameplural'] = 'Video Missions';
$string['movedown'] = 'Move down';
$string['moveup'] = 'Move up';
$string['noattempts'] = 'No student attempts are available yet.';
$string['nomissions'] = 'No missions have been created yet.';
$string['notgraded'] = 'Not graded';
$string['occurrences'] = 'Occurrences';
$string['optional'] = 'Optional';
$string['overallprogress'] = 'Activity progress';
$string['pending'] = 'Pending';
$string['pluginadministration'] = 'Video Mission administration';
$string['pluginname'] = 'Video Mission';
$string['points'] = 'Points';
$string['privacy:metadata:videomission_answers'] = 'Stores student mission responses.';
$string['privacy:metadata:videomission_answers:completed'] = 'Whether the mission was completed.';
$string['privacy:metadata:videomission_answers:endtime'] = 'The selected video interval end.';
$string['privacy:metadata:videomission_answers:occurrences'] = 'The selected occurrence timestamps.';
$string['privacy:metadata:videomission_answers:response'] = 'The textual mission response.';
$string['privacy:metadata:videomission_answers:score'] = 'Points earned for the mission.';
$string['privacy:metadata:videomission_answers:starttime'] = 'The selected video moment or interval start.';
$string['privacy:metadata:videomission_answers:timemodified'] = 'When the mission response was last changed.';
$string['privacy:metadata:videomission_answers:userid'] = 'The user who submitted the mission response.';
$string['privacy:metadata:videomission_progress'] = 'Stores video viewing progress.';
$string['privacy:metadata:videomission_progress:duration'] = 'Known video duration.';
$string['privacy:metadata:videomission_progress:lastposition'] = 'Last playback position.';
$string['privacy:metadata:videomission_progress:percentage'] = 'Percentage of unique video content watched.';
$string['privacy:metadata:videomission_progress:playtime'] = 'Total playback time including repetitions.';
$string['privacy:metadata:videomission_progress:timemodified'] = 'When viewing progress was last updated.';
$string['privacy:metadata:videomission_progress:userid'] = 'The user whose viewing progress is stored.';
$string['privacy:metadata:videomission_progress:watchedseconds'] = 'Unique seconds watched.';
$string['privacy:metadata:videomission_progress:watchedsegments'] = 'The video segments watched by the user.';
$string['progresssaved'] = 'Video progress saved.';
$string['removeoccurrence'] = 'Remove';
$string['report'] = 'Report';
$string['required'] = 'Mandatory mission';
$string['requiredcount'] = 'Required number of occurrences';
$string['requiredcount_help'] = 'Used only by missions that ask the student to find repeated occurrences in the video.';
$string['requiredwatch'] = 'Recommended watched percentage';
$string['requiredwatch_help'] = 'Percentage displayed as the activity viewing goal. Custom completion can use a separate threshold.';
$string['response'] = 'Response';
$string['restrictseek'] = 'Restrict seeking to unseen parts';
$string['restrictseek_help'] = 'When enabled, students cannot jump far beyond the furthest part they have already watched.';
$string['resumeplayback'] = 'Resume from last position';
$string['resumeplayback_help'] = 'Resume playback from the last stored position.';
$string['saved'] = 'Saved';
$string['savemission'] = 'Save mission';
$string['score'] = 'Score';
$string['selectedinterval'] = 'Selected interval';
$string['selectedmoment'] = 'Selected moment';
$string['setend'] = 'Set end';
$string['setstart'] = 'Set start';
$string['source'] = 'Video source';
$string['sourceupload'] = 'Upload';
$string['sourceurl'] = 'Direct URL';
$string['sourcevimeo'] = 'Vimeo';
$string['sourceyoutube'] = 'YouTube';
$string['student'] = 'Student';
$string['timeminutesseconds'] = '{$a->minutes}:{$a->seconds}';
$string['totalscore'] = 'Total points';
$string['tracking'] = 'Tracking and navigation';
$string['unknownsource'] = 'The configured video source is not valid.';
$string['usecurrenttime'] = 'Use current time';
$string['videofile'] = 'Video file';
$string['videofile_help'] = 'Upload one video file to Moodle.';
$string['videomission:addinstance'] = 'Add a new Video Mission activity';
$string['videomission:manage'] = 'Manage Video Mission missions';
$string['videomission:submit'] = 'Submit Video Mission responses';
$string['videomission:view'] = 'View Video Mission activities';
$string['videomission:viewreports'] = 'View Video Mission reports';
$string['videoprogress'] = 'Video watched: {$a}%';
$string['videourl'] = 'Video URL';
$string['videourl_help'] = 'Enter a direct video URL, YouTube URL, or Vimeo URL according to the selected source.';
$string['watched'] = 'Watched';
$string['watchgoal'] = 'Viewing goal: {$a}%';
