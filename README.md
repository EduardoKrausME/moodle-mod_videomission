# Video Mission

Video Mission (`mod_videomission`) transforms a video into a mission-based Moodle activity. Teachers add one video,
define missions, choose whether each mission is required or optional, assign points, and decide whether missions may be
completed in any order or sequentially.

Supported mission types:

- Select a video moment.
- Select a video interval.
- Answer a question.
- Write an observation.
- Find a required number of occurrences in the video.
- Confirm a task.

The activity records watched video segments instead of assuming that reaching the end means the whole video was watched.
It stores unique watched time, total playback time, last position and percentage watched, and can resume playback from
the last position.

Video sources:

- Moodle file upload.
- Direct HTTP/HTTPS video URL.
- YouTube.
- Vimeo.

Teachers have a report with mission status, answers, selected moments, earned points and watched percentage for each
student. Grades are synchronized with the Moodle gradebook. Activity completion can require all mandatory missions
and/or a minimum watched percentage.

The plugin includes backup and restore support, Privacy API support, external AJAX functions, English and Brazilian
Portuguese language packs, Mustache templates and AMD JavaScript.

## Requirements

Moodle 4.4 or later.

## Installation

Copy the directory to `mod/videomission` and complete the normal Moodle upgrade process.

## License

GNU GPL v3 or later.
