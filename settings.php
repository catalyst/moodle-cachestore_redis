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
 * Advanced Redis Cache Store - Settings
 *
 * @package   cachestore_advredis
 * @copyright 2013 Adam Durana
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$settings->add(
    new admin_setting_configtext(
        'cachestore_advredis/test_server',
        get_string('test_server', 'cachestore_advredis'),
        get_string('test_server_desc', 'cachestore_advredis'),
        '',
        PARAM_TEXT,
        16
    )
);

if (class_exists('Redis')) { // Only if Redis is available.

    $options = array(Redis::SERIALIZER_PHP => get_string('serializer_php', 'cachestore_advredis'));

    if (defined('Redis::SERIALIZER_IGBINARY')) {
        $options[Redis::SERIALIZER_IGBINARY] = get_string('serializer_igbinary', 'cachestore_advredis');
    }

    $settings->add(new admin_setting_configselect(
            'cachestore_advredis/test_serializer',
            get_string('test_serializer', 'cachestore_advredis'),
            get_string('test_serializer_desc', 'cachestore_advredis'),
            Redis::SERIALIZER_PHP,
            $options
        )
    );
}
