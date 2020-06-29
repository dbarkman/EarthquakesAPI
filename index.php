<?php

/**
 * index.php
 * Description:
 *
 */

session_start();
session_regenerate_id();

require_once dirname(__FILE__) . '/../../Earthquakes/includes/includes.php';

$earthquakesAPI = new EarthquakesAPI();
$earthquakesAPI->validateAPICommon();

if (isset($_REQUEST['noun'])) {
    if ($_REQUEST['noun'] === 'testPass') {
        $earthquakesAPI->testPass();

    } else if ($_REQUEST['noun'] === 'earthquakes') {
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $earthquakesAPI->validateGetEarthquakes();
            $parameters = array();
            if (isset($_REQUEST['recent']) && $_REQUEST['recent'] == true) {
                $parameters['interval'] = $_REQUEST['interval'];
            } else if (isset($_REQUEST['timePeriod']) && $_REQUEST['timePeriod'] == true) {
                $parameters['startDate'] = $_REQUEST['startDate'];
                $parameters['endDate'] = $_REQUEST['endDate'];
            } else {
                $parameters['count'] = (isset($_REQUEST['count'])) ? $_REQUEST['count'] : 100;
            }
            if (isset($_REQUEST['location']) && $_REQUEST['location'] == true) {
                $parameters['latitude'] = $_REQUEST['latitude'];
                $parameters['longitude'] = $_REQUEST['longitude'];
                $parameters['radius'] = $_REQUEST['radius'];
                $parameters['units'] = $_REQUEST['units'];
            }
            if (isset($_REQUEST['magnitude'])) {
                $parameters['magnitude'] = $_REQUEST['magnitude'];
            }
            if (isset($_REQUEST['intensity'])) {
                $parameters['intensity'] = $_REQUEST['intensity'];
            }
            if (isset($_REQUEST['intensity'])) {
                $parameters['intensity'] = $_REQUEST['intensity'];
            }
            if (isset($_REQUEST['type'])) {
                $parameters['type'] = $_REQUEST['type'];
            }
            if (isset($_REQUEST['hotSpot'])) {
                $parameters['hotSpot'] = $_REQUEST['hotSpot'];
            }
            $earthquakesAPI->getEarthquakes($parameters);
        } else {
            $earthquakesAPI->badRequest();
        }

    } else if ($_REQUEST['noun'] === 'feltIt') {
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$earthquakesAPI->validateCreateCategory();
			$category = array(
				'name' => $_REQUEST['name']
			);
			$earthquakesAPI->createCategory($category);
		} else {
			$earthquakesAPI->badRequest();
		}

	} else {
		$earthquakesAPI->resourceNotFound();
	}

} else {
	$earthquakesAPI->resourceNotDefined();
}


class EarthquakesAPI
{
	private $_uuid;

	private $_timeStamp;
	private $_ip;
	private $_agent;
	private $_language;
	private $_method;

	private $_platform;

	private $_errorCode;
	private $_response;

	private $_queryTime;

	private $_start;
	private $_time;
	private $_packageSize;
	private $_size;
	private $_memoryUsage;
	private $_count;

	private $_appVersion;
	private $_osVersion;
	private $_device;
	private $_machine;

	private $_logger;
	private $_db;

	private $_validation;

	public function __construct()
	{
		$this->_start = microtime(true);
		$this->_packageSize = null;
		$this->_response = null;
		$this->_responseType = 'json';

		$this->_queryTime = 0.0;

		$container = new Container();

		$this->_logger = $container->getLogger();

		$this->_db = $container->getMySQLDBConnect();

		$this->_validation = $container->getValidation();

		$this->beginRequest();
	}

	private function beginRequest()
	{
		$this->logIt('info', '');
		$this->logIt('info', '--------------------------------------------------------------------------------');
		$this->logIt('info', 'API Session Started');
        $this->logIt('info', 'Query String: ' . $_SERVER['QUERY_STRING']);

		$this->_uuid = (isset($_REQUEST['uuid'])) ? $_REQUEST['uuid'] : '';

		$this->_timeStamp = (isset($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : 'NA');
		$this->_ip = (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'NA');
		$this->_agent = (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'NA');
		$this->_language = (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : 'NA');
		$this->_method = (isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'NA');

		$this->_platform = 'iOS';
		if (strpos($this->_agent, 'Macintosh') !== FALSE) {
			$this->_platform = 'Mac';
		} else if (strpos($this->_agent, 'Apache') !== FALSE) {
			$this->_platform = 'Android';
		}

		$this->_appVersion = (isset($_REQUEST['appVersion'])) ? $_REQUEST['appVersion'] : '';
		$this->_osVersion = (isset($_REQUEST['osVersion'])) ? $_REQUEST['osVersion'] : '';
		$this->_device = (isset($_REQUEST['device'])) ? $_REQUEST['device'] : '';
		$this->_machine = (isset($_REQUEST['machine'])) ? $_REQUEST['machine'] : '';

		$this->logIt('info', 'TIME: ' . $this->_timeStamp);
		$this->logIt('info', 'IP: ' . $this->_ip);
		$this->logIt('info', 'AGENT: ' . $this->_agent);
		$this->logIt('info', 'LANGUAGE: ' . $this->_language);
		$this->logIt('info', 'VERB: ' . $this->_method);
		$this->logIt('info', 'NOUN: ' . $_REQUEST['noun']);
	}

	public function logIt($level, $message)
	{
		$this->_logger->$level($message);
	}

	///////////////////////////////////////////////////////////////////////////////
	////////////////////////////// RESPONSE FUNCTIONS /////////////////////////////
	///////////////////////////////////////////////////////////////////////////////

    public function testPass()
    {
        http_response_code(200);
        $this->echoResponse('none', array(), '', 'success', array());
        $this->completeRequest();
    }

    public function badRequest()
	{
		http_response_code(400);
		$errorCode = 'badRequest';
		$friendlyError = 'Bad Request';
		$errors = array($friendlyError);
		$this->echoResponse($errorCode, $errors, $friendlyError, 'fail', (object)array());
		$this->completeRequest();
	}

	public function resourceNotFound()
	{
		http_response_code(404);
		$errorCode = 'resourceNotFound';
		$friendlyError = 'Resource Not Found';
		$errors = array($friendlyError);
		$this->echoResponse($errorCode, $errors, $friendlyError, 'fail', (object)array());
		$this->completeRequest();
	}

	public function resourceNotDefined()
	{
		http_response_code(400);
		$errorCode = 'resourceNotDefined';
		$friendlyError = 'Resource Not Defined';
		$errors = array($friendlyError);
		$this->echoResponse($errorCode, $errors, $friendlyError, 'fail', (object)array());
		$this->completeRequest();
	}

	///////////////////////////////////////////////////////////////////////////////
	////////////////////////////// VALIDATION FUNCTIONS ///////////////////////////
	///////////////////////////////////////////////////////////////////////////////

    public function validateAPICommon()
    {
        $this->_validation->validateAPICommon();
        if ($this->_validation->getErrorCount() > 0) {
            $errorCode = $this->_validation->getErrorCode();
            if ($errorCode == 'invalidParameter') {
                http_response_code(400);
                $this->validationFailed();
            } else if ($errorCode == 'missingParameter') {
                http_response_code(404);
                $this->validationFailed();
            }
        }
    }

    public function validateGetEarthquakes()
    {
        $this->_validation->validateGetEarthquakes();
        if ($this->_validation->getErrorCount() > 0) {
            $errorCode = $this->_validation->getErrorCode();
            if ($errorCode == 'invalidParameter') {
                http_response_code(400);
                $this->validationFailed();
            } else if ($errorCode == 'missingParameter') {
                http_response_code(404);
                $this->validationFailed();
            }
        }
    }

	private function validationFailed()
	{
		http_response_code(400);
		$errorCode = $this->_validation->getErrorCode();
		$errors = $this->_validation->getErrors();
		$friendlyError = $this->_validation->getFriendlyError();
		$this->echoResponse($errorCode, $errors, $friendlyError, 'fail', array());
		$this->completeRequest();
	}

	///////////////////////////////////////////////////////////////////////////////
	////////////////////////////// EARTHQUAKE FUNCTIONS ///////////////////////////
	///////////////////////////////////////////////////////////////////////////////

	public function getEarthquakes($parameters)
	{
		http_response_code(200);
		$earthquakes = Earthquakes::GetEarthquakes($this->_db, $this->_logger, $parameters);
		$response = $earthquakes;
		$this->echoResponse('none', array(), '', 'success', $response);
		$this->completeRequest();
	}

    ///////////////////////////////////////////////////////////////////////////////
	////////////////////////////// CLOSING FUNCTIONS //////////////////////////////
	///////////////////////////////////////////////////////////////////////////////

	private function echoResponse($errorCode, $errors, $friendlyErrors, $result, $data)
	{
		// if a callback is set, assume jsonp and wrap the response in the callback function
		if (isset($_REQUEST['callback']) && strtolower($_REQUEST['responseType'] === 'jsonp')) {
			echo $_REQUEST['callback'] . '(';
		}

		$this->_count = count($data);
		$this->_errorCode = $errorCode;

		$jsonResponse = array();
		$jsonResponse['httpStatus'] = http_response_code();
		$jsonResponse['noun'] = $_REQUEST['noun'];
		$jsonResponse['verb'] = $_SERVER['REQUEST_METHOD'];
		$jsonResponse['errorCode'] = $errorCode;
		$jsonResponse['errors'] = $errors;
		$jsonResponse['friendlyError'] = $friendlyErrors;
		$jsonResponse['result'] = $result;
		$jsonResponse['count'] = $this->_count;
		$jsonResponse['data'] = $data;
		foreach ($errors as $error) {
			$this->logIt('info', $error);
		}
		$this->_response = json_encode($jsonResponse);
		header('Content-type: application/json');
		echo $this->_response;

		if (isset($_REQUEST['callback']) && strtolower($_REQUEST['responseType'] === 'jsonp')) {
			echo ')';
		}
	}

	private function completeRequest()
	{
		$this->_time = (microtime(true) - $this->_start);
		$this->_packageSize = strlen($this->_response);
		$this->_size = number_format($this->_packageSize);
		$this->_memoryUsage = number_format(memory_get_usage());

		$this->logIt('info', 'Query Time: ' . $this->_queryTime);
		$this->logIt('info', 'Payload Time: ' . $this->_time);
		$this->logIt('info', 'Payload Size: ' . $this->_size);
        $this->logIt('info', 'Count: ' . $this->_count);
		$this->logIt('info', 'Memory Usage: ' . $this->_memoryUsage);
		$this->logIt('info', 'API Session Ended');
		$this->logIt('info', '--------------------------------------------------------------------------------');
		$this->logIt('info', '');

		$osVersion = '';
		$osAPILevel = '';
		$carrier = '';
		$device = '';
		$display = '';
		$manufacturer = '';
		$model = '';
		if (isset($_REQUEST['androidInfo'])) {
			$androidInfoArray = explode('|',$_REQUEST['androidInfo']);
			$osVersion = $androidInfoArray[0];
			$osAPILevel = $androidInfoArray[1];
			$carrier = $androidInfoArray[2];
			$device = $androidInfoArray[3];
			$display = $androidInfoArray[4];
			$manufacturer = $androidInfoArray[5];
			$model = $androidInfoArray[6];
		}

		$countForRequest = 0;
		$requestForLogging = '';
		if (isset($_REQUEST['key'])) $_REQUEST['key'] = substr($_REQUEST['key'], 0, 8);
		foreach ($_REQUEST as $key => $value) {
			if ($countForRequest > 0) $requestForLogging .= ' - ';
			$value = urldecode($value);
			$requestForLogging .= $key . ': ' . $value;
			$countForRequest++;
		}
		$this->logIt('debug', ' - REQUESTSTRING: ' . $requestForLogging . ' - ' . $this->_agent);

		$logRequestArguments = array(
			'uuid' => $this->_uuid,
			'noun' => $_REQUEST['noun'],
			'verb' => $_SERVER['REQUEST_METHOD'],
			'request' => $requestForLogging,
			'agent' => $this->_agent,
			'timeStamp' => $this->_timeStamp,
			'language' => $this->_language,
			'httpStatus' => http_response_code(),
			'errorCode' => $this->_errorCode,
			'queryTime' => $this->_queryTime,
			'time' => $this->_time,
			'size' => $this->_size,
			'memory' => $this->_memoryUsage,
			'appVersion' => $this->_appVersion,
			'platform' => $this->_platform,
			'device' => urldecode($this->_device),
			'machine' => $this->_machine,
			'osVersion' => $this->_osVersion,
			'ip' => $this->_ip
		);

//		LogRequest::LogRequestToDB($logRequestArguments, $this->_db);
		exit();
	}
}
