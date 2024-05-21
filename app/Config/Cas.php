<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Cas extends BaseConfig
{
    public $casServerUrl = 'https://sso.stmikelrahma.ac.id';
    public $phpCasPath = APPPATH . '/ThirdParty/CAS-1.3.8';
    public $casServerPort = 443;
    public $casServerPath = '';
    public $casVersion = '3.0';
    public $casDisableValidation = TRUE;
    public $casSetVerbose = TRUE;
    public $casDebug = TRUE;
    public $casLogFile = '/tmp/phpcas-akun.log';
    public $changeSessionId = FALSE;
    public $caCertFile = '';
}
