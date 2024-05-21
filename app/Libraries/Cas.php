<?php

namespace App\Libraries;

use phpCAS;
use stdClass;

class Cas
{
    public $CASUSER;
    public function __construct()
    {
        $config = new \Config\Cas;

        $this->CASUSER = new stdClass();
        $this->CASUSER->userlogin = '';
        $this->CASUSER->attributes = [];
        $this->CASUSER->local_account = '';
        if (!function_exists('curl_init')) {
            throw new \Exception('<strong>ERROR:</strong> You need to install the PHP module
				<strong><a href="http://php.net/curl">curl</a></strong> to be able
				to use CAS authentication.');
        }

        if (empty($config->phpCasPath) or filter_var($config->casServerUrl, FILTER_VALIDATE_URL) === false) {
            echo 'error config';
        }
        $cas_lib_file = $config->phpCasPath . '/CAS.php';
        if (!file_exists($cas_lib_file)) throw new \Exception("could not find " . $cas_lib_file);

        require_once $cas_lib_file;
        if (($config->casDebug) === true) phpCAS::setDebug($config->casLogFile);
        if (($config->casSetVerbose) === true) phpCAS::setVerbose($config->casSetVerbose);

        // init CAS client
        $defaults = ['path' => $config->casServerPath, 'port' => $config->casServerPort];
        $cas_url = array_merge($defaults, parse_url($config->casServerUrl));

        // koneksi client cas
        phpCAS::client(
            $config->casVersion,
            $cas_url['host'],
            $cas_url['port'],
            $cas_url['path'],
            false,
        );

        // configures SSL behavior
        $casDisableValidation = $config->casDisableValidation;
        if ($casDisableValidation === true) {
            phpCAS::setNoCasServerValidation($casDisableValidation);
        } else {
            $ca_cert_file = $config->caCertFile;
            if (empty($ca_cert_file)) {
                throw new \Exception("Error File " . $ca_cert_file);
            }
            return phpCAS::setCasServerCACert($ca_cert_file);
        }
    }

    public function force_auth()
    {
        phpCAS::forceAuthentication();
        d(session_name());
        d(session_id());
    }

    public function is_authenticated()
    {
        return phpCAS::isAuthenticated();
    }

    public function authenticate()
    {
        return phpCAS::checkAuthentication();
    }

    public function user()
    {
        if (phpCAS::isAuthenticated()) {
            $userlogin = phpCAS::getUser();
            $attributes = phpCAS::getAttributes();
            return (object) [
                'userlogin' => $userlogin,
                'attributes' => $attributes,
            ];
        } else {
            throw new \Exception("User was not authenticated yet.");
        }
    }

    public function logout($url = '')
    {
        if (empty($url)) {
            $url = base_url();
        }
        phpCAS::logoutWithRedirectService($url);
    }
}
