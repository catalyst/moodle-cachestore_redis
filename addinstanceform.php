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
 * Advanced Redis Cache Store - Add instance form
 *
 * @package   cachestore_advredis
 * @copyright 2013 Adam Durana
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @var $CFG  stdClass
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot.'/cache/forms.php');
require_once(__DIR__.'/lib.php');

/**
 * Form for adding instance of Redis Cache Store.
 *
 * @copyright   2013 Adam Durana
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cachestore_advredis_addinstance_form extends cachestore_addinstance_form {
    /**
     * Builds the form for creating an instance.
     */
    protected function configuration_definition() {
        $form = $this->_form;

        $form->addElement('checkbox', 'clustermode',
                          get_string('clustermode', 'cachestore_advredis'),
                          cachestore_advredis::is_cluster_available() ? '' : get_string('clustermodeunavailable', 'cachestore_advredis'),
                          cachestore_advredis::is_cluster_available() ? '' : 'disabled');

        $form->addElement('textarea', 'server', get_string('server', 'cachestore_advredis'), array('size' => 24));
        $form->setType('server', PARAM_TEXT);
        $form->addHelpButton('server', 'server', 'cachestore_advredis');
        $form->addRule('server', get_string('required'), 'required');

        $form->addElement('text', 'prefix', get_string('prefix', 'cachestore_advredis'), array('size' => 16));
        $form->setType('prefix', PARAM_TEXT); // We set to text but we have a rule to limit to alphanumext.
        $form->addHelpButton('prefix', 'prefix', 'cachestore_advredis');
        $form->addRule('prefix', get_string('prefixinvalid', 'cachestore_advredis'), 'regex', '#^[a-zA-Z0-9\-_]+$#');

        $serializeroptions = cachestore_advredis::config_get_serializer_options();
        $form->addElement('select', 'serializer', get_string('useserializer', 'cachestore_advredis'), $serializeroptions);
        $form->addHelpButton('serializer', 'useserializer', 'cachestore_advredis');
        $form->setDefault('serializer', Redis::SERIALIZER_PHP);
        $form->setType('serializer', PARAM_INT);

        $compressoroptions = cachestore_advredis::config_get_compressor_options();
        $form->addElement('select', 'compressor', get_string('usecompressor', 'cachestore_advredis'), $compressoroptions);
        $form->addHelpButton('compressor', 'usecompressor', 'cachestore_advredis');
        $form->setDefault('compressor', cachestore_advredis::COMPRESSOR_NONE);
        $form->setType('compressor', PARAM_INT);
    }

    /**
     * Validates the configuration form data
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function configuration_validation($data, $files, $errors) {
        $clusteravailable = cachestore_advredis::is_cluster_available();
        $clustermode = !empty($data['clustermode']);
        $servers = explode("\n", $data['server']);

        // Sanity check, check if RedisCluster installed (should not happend as checkbox is disabled).
        if (!$clusteravailable && $clustermode) {
            $errors['clustermode'] = get_string('clustermodeunavailable', 'cachestore_advredis');
        }

        if ($clustermode) {
            $errors = $this->configuration_validation_serverscluster($servers, $errors);
        } else {
            // Multiple servers only allowed in cluster mode.
            if (count($servers) != 1) {
                $errors['server'] = get_string('formerror_singleserveronly', 'cachestore_advredis');
            }
        }

        return $errors;
    }

    private function configuration_validation_serverscluster($servers, $errors) {
        // In cluster mode port is mandatory.
        foreach ($servers as $server) {
            $server = trim($server);
            if (empty($server)) {
                continue;
            }

            $addressport = explode(':', $server);
            if (count($addressport) != 2) {
                if (!array_key_exists('server', $errors)) {
                    $errors['server'] = [];
                }
                $errors['server'][] = get_string(
                    'formerror_clusterserver',
                    'cachestore_advredis',
                    ['server' => $server]
                );
            }
        }

        if (array_key_exists('server', $errors)) {
            $errors['server'] = implode('<br />', $errors['server']);
        }

        return $errors;
    }
}
