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
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $earthquakesAPI->testPass();
        } else {
            $earthquakesAPI->badRequest();
        }

    } else if ($_REQUEST['noun'] === 'token') {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            if (isset($_REQUEST['token']) && isset($_REQUEST['debug'])) {
                $earthquakesAPI->createToken($_REQUEST['token'], $_REQUEST['debug']);
            } else {
                $earthquakesAPI->badRequest('You must include both \'token\' and \'debug\' variables when calling the \'token\' endpoint with POST.');
            }
        } else {
            $earthquakesAPI->badRequest('There was another problem with your post.');
        }

    } else if (strpos(strtolower($_REQUEST['noun']), 'earthquakes') !== false) {
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $parameters = array();
            $maxCount = 1000;
            $maxLocationCount = 100;
            $maxStart = 100000;
            $parameters['count'] = 100;
//            $parameters['start'] = 0;
            if (isset($_REQUEST['count']) && $_REQUEST['count'] <= $maxCount) {
                $parameters['count'] = $_REQUEST['count'];
            }
            if (isset($_REQUEST['start'])) {
                if ($_REQUEST['start'] <= $maxStart) {
                    $parameters['start'] = $_REQUEST['start'];
                } else {
                    $earthquakesAPI->badRequest('Using parameter \'start\' with a value higher than 100k is not allowed. Use the \'earthquakesByDate\' endpoint with \'startDate\', \'endDate\', \'count\' and \'start\' parameters to narrow down your search and reduce the number of pages you need to retrieve.');
                }
            }
            if (isset($_REQUEST['magnitude'])) {
                $parameters['magnitude'] = $_REQUEST['magnitude'];
            }
            if (isset($_REQUEST['intensity'])) {
                $parameters['intensity'] = $_REQUEST['intensity'];
            }
            if (isset($_REQUEST['type'])) {
                $parameters['type'] = $_REQUEST['type'];
            }
            if (isset($_REQUEST['order'])) {
                $parameters['order'] = $_REQUEST['order'];
            }

            $locationData = 0;
            if (isset($_REQUEST['latitude'])) {
                $parameters['latitude'] = $_REQUEST['latitude'];
                $locationData++;
            }
            if (isset($_REQUEST['longitude'])) {
                $parameters['longitude'] = $_REQUEST['longitude'];
                $locationData++;
            }
            if (isset($_REQUEST['radius'])) {
                $parameters['radius'] = $_REQUEST['radius'];
                $locationData++;
            }
            if (isset($_REQUEST['units'])) {
                $parameters['units'] = $_REQUEST['units'];
                $locationData++;
            }
            $parameters['location'] = false;
            if ($locationData == 4) {
                $parameters['location'] = true;
                if ($parameters['count'] > $maxLocationCount) {
                    $earthquakesAPI->badRequest('When searching based on a location, using parameter \'count\' with a value higher than 100 is not allowed. Use the paging parameters \'count\' and \'start\' together, to retrieve the data you need.');
                }
            } else if ($locationData != 0) {
                $earthquakesAPI->badRequest('Parameter \'latitude\', \'longitude\', \'radius\' or \'units\' is missing. All four are required when searching by location.');
            }

            if ($_REQUEST['noun'] == 'recentEarthquakes') {
                if (isset($_REQUEST['interval'])) {
                    $parameters['interval'] = $_REQUEST['interval'];
                } else {
                    $earthquakesAPI->badRequest('Parameter \'interval\' required when calling \'recentEarthquakes\'. See ISO8601 Duration documentation for interval format.');
                }
            } else if ($_REQUEST['noun'] == 'earthquakesByDate') {
                if (isset($_REQUEST['startDate']) && isset($_REQUEST['endDate'])) {
                    $parameters['startDate'] = $_REQUEST['startDate'];
                    $parameters['endDate'] = $_REQUEST['endDate'];
                } else {
                    $earthquakesAPI->badRequest('Parameter \'startDate\' and \'endDate\' are required when calling \'earthquakesByDate\'. Use YYYY-MM-DD format.');
                }
            }
            $earthquakesAPI->getEarthquakes($parameters);
        } else {
            $earthquakesAPI->badRequest();
        }

    } else if ($_REQUEST['noun'] === 'latestEarthquakeNearMe') {
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            if (isset($_REQUEST['latitude']) && isset($_REQUEST['longitude'])) {
                $parameters['latest'] = true;
                $parameters['latitude'] = $_REQUEST['latitude'];
                $parameters['longitude'] = $_REQUEST['longitude'];
                $earthquakesAPI->getEarthquakes($parameters);
            } else {
                $earthquakesAPI->badRequest('Parameters \'latitude\' and \'longitude\' required when calling \'latestEarthquakeNearMe\'.');
            }
        } else {
            $earthquakesAPI->badRequest();
        }

    } else if (strpos(strtolower($_REQUEST['noun']), 'significant') !== false) {
        $parameters['count'] = -1;
        $parameters['significance'] = 600;
        if (strpos(strtolower($_REQUEST['noun']), 'hour') !== false) {
            $parameters['interval'] = 'PT1H';
        } else if (strpos(strtolower($_REQUEST['noun']), 'day') !== false) {
            $parameters['interval'] = 'PT24H';
        } else if (strpos(strtolower($_REQUEST['noun']), 'week') !== false) {
            $parameters['interval'] = 'P7D';
        } else if (strpos(strtolower($_REQUEST['noun']), 'month') !== false) {
            $parameters['interval'] = 'P30D';
        } else {
            $earthquakesAPI->resourceNotFound();
        }
        $earthquakesAPI->getEarthquakes($parameters);

    } else if (strpos(strtolower($_REQUEST['noun']), '4.5') !== false) {
        $parameters['count'] = -1;
        $parameters['magnitude'] = 4.5;
        if (strpos(strtolower($_REQUEST['noun']), 'hour') !== false) {
            $parameters['interval'] = 'PT1H';
        } else if (strpos(strtolower($_REQUEST['noun']), 'day') !== false) {
            $parameters['interval'] = 'PT24H';
        } else if (strpos(strtolower($_REQUEST['noun']), 'week') !== false) {
            $parameters['interval'] = 'P7D';
        } else if (strpos(strtolower($_REQUEST['noun']), 'month') !== false) {
            $parameters['interval'] = 'P30D';
        } else {
            $earthquakesAPI->resourceNotFound();
        }
        $earthquakesAPI->getEarthquakes($parameters);

    } else if (strpos(strtolower($_REQUEST['noun']), '2.5') !== false) {
        $parameters['count'] = -1;
        $parameters['magnitude'] = 2.5;
        if (strpos(strtolower($_REQUEST['noun']), 'hour') !== false) {
            $parameters['interval'] = 'PT1H';
        } else if (strpos(strtolower($_REQUEST['noun']), 'day') !== false) {
            $parameters['interval'] = 'PT24H';
        } else if (strpos(strtolower($_REQUEST['noun']), 'week') !== false) {
            $parameters['interval'] = 'P7D';
        } else if (strpos(strtolower($_REQUEST['noun']), 'month') !== false) {
            $parameters['interval'] = 'P30D';
        } else {
            $earthquakesAPI->resourceNotFound();
        }
        $earthquakesAPI->getEarthquakes($parameters);

    } else if (strpos(strtolower($_REQUEST['noun']), '1.0') !== false) {
        $parameters['count'] = -1;
        $parameters['magnitude'] = 1.0;
        if (strpos(strtolower($_REQUEST['noun']), 'hour') !== false) {
            $parameters['interval'] = 'PT1H';
        } else if (strpos(strtolower($_REQUEST['noun']), 'day') !== false) {
            $parameters['interval'] = 'PT24H';
        } else if (strpos(strtolower($_REQUEST['noun']), 'week') !== false) {
            $parameters['interval'] = 'P7D';
        } else if (strpos(strtolower($_REQUEST['noun']), 'month') !== false) {
            $parameters['interval'] = 'P30D';
        } else {
            $earthquakesAPI->resourceNotFound();
        }
        $earthquakesAPI->getEarthquakes($parameters);

    } else if (strpos(strtolower($_REQUEST['noun']), 'all') !== false) {
        $parameters['count'] = -1;
        if (strpos(strtolower($_REQUEST['noun']), 'hour') !== false) {
            $parameters['interval'] = 'PT1H';
        } else if (strpos(strtolower($_REQUEST['noun']), 'day') !== false) {
            $parameters['interval'] = 'PT24H';
        } else if (strpos(strtolower($_REQUEST['noun']), 'week') !== false) {
            $parameters['interval'] = 'P7D';
        } else if (strpos(strtolower($_REQUEST['noun']), 'month') !== false) {
            $parameters['interval'] = 'P30D';
        } else {
            $earthquakesAPI->resourceNotFound();
        }
        $earthquakesAPI->getEarthquakes($parameters);

    } else if ($_REQUEST['noun'] === 'types') {
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $earthquakesAPI->getTypes();
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

    public function badRequest($error = '')
	{
		http_response_code(400);
		$errorCode = 'badRequest';
		$errorString = 'Bad Request';
        $friendlyError = (!empty($error)) ? $error : $errorString;
		$errors = array($errorString);
		$this->echoResponse($errorCode, $errors, $friendlyError, 'fail', array());
		$this->completeRequest();
	}

	public function resourceNotFound()
	{
		http_response_code(404);
		$errorCode = 'resourceNotFound';
		$friendlyError = 'Resource Not Found';
		$errors = array($friendlyError);
		$this->echoResponse($errorCode, $errors, $friendlyError, 'fail', array());
		$this->completeRequest();
	}

	public function resourceNotDefined()
	{
		http_response_code(400);
		$errorCode = 'resourceNotDefined';
		$friendlyError = 'Resource Not Defined';
		$errors = array($friendlyError);
		$this->echoResponse($errorCode, $errors, $friendlyError, 'fail', array());
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
    ////////////////////////////// HELPER FUNCTIONS ///////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////

    public function string_contains (string $haystack, string $needle) {
        return empty($needle) || strpos($haystack, $needle) !== false;
    }

    ///////////////////////////////////////////////////////////////////////////////
    ////////////////////////////// EARTHQUAKE FUNCTIONS ///////////////////////////
    ///////////////////////////////////////////////////////////////////////////////

    public function getEarthquakes($parameters)
    {
        http_response_code(200);
        $response = Earthquakes::GetEarthquakes($this->_db, $this->_logger, $parameters);
        $this->echoResponse('none', array(), '', 'success', $response);
        $this->completeRequest();
    }

    ///////////////////////////////////////////////////////////////////////////////
    ////////////////////////////// Types FUNCTIONS ////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////

    public function getTypes()
    {
        http_response_code(200);
        $response = Types::GetTypes($this->_logger, $this->_db);
        $this->echoResponse('none', array(), '', 'success', $response);
        $this->completeRequest();
    }

    ///////////////////////////////////////////////////////////////////////////////
    ////////////////////////////// TOKEN FUNCTIONS ////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////

    public function createToken($token, $debug)
    {
        $sendPush = $_POST['sendPush'] ?? 0;
        $magnitude = $_POST['magnitude'] ?? 6;
        $location = $_POST['location'] ?? 0;
        $radius = $_POST['radius'] ?? 0;
        $units = $_POST['units'] ?? '';
        $latitude = $_POST['latitude'] ?? 0;
        $longitude = $_POST['longitude'] ?? 0;
        $tokenObject = new Token($this->_logger, $this->_db, $token, $debug, $sendPush, $magnitude, $location, $radius, $units, $latitude, $longitude);
        if ($tokenObject->getTokenExists() === 0) {
            if ($tokenObject->saveToken() === FALSE) {
                http_response_code(500);
                $errorCode = 'tokenNotInserted';
                $friendlyError = 'Token could not be inserted.';
                $errors = array($friendlyError);
                $this->echoResponse($errorCode, $errors, $friendlyError, 'fail', array());
            } else {
                http_response_code(201);
                $this->echoResponse('none', array(), '', 'success', array());
            }
        } else {
            if ($tokenObject->updateToken() === FALSE) {
                http_response_code(500);
                $errorCode = 'tokenNotUpdated';
                $friendlyError = 'Token could not be updated.';
                $errors = array($friendlyError);
                $this->echoResponse($errorCode, $errors, $friendlyError, 'fail', array());
            } else {
                http_response_code(200);
                $this->echoResponse('none', array(), '', 'success', array());
            }
        }
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
		$this->logIt('info', 'HTTP Response: ' . http_response_code());
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
