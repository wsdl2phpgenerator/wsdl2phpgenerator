<?php

namespace Wsdl2PhpGenerator;

class SoapClientAsync extends \SoapClient {
    private static $registry = null;

    /** @var resource curl_multi resource for async calls  */
    private $curl_multi = null;
    /** @var array Extra options to pass to CURL  */
    private $curl_options = null;
    /** @var int Number of connections still active */
    private $active = null;

    /** @var array Used in the initial call to __soapRequestAsync(). Passes the callback to __doRequest() */
    private $private_data = null;
    /** @var string Used when a request is complete. Passes the finished result to __doRequest */
    private $finished_result = null;

    /**
     * SoapClientAsync constructor.
    * @param string $wsdl           URL of the WSDL
     * @param array|null $options   Options to the SOAP client. Also, if you need additional options to pass to CURL, pass
     *                              them as an array in the 'curl' parameter. Note that CURLOPT_RETURNTRANSFER and CURLOPT_PRIVATE
     *                              are reserved and not usable with this implementation.
     * @param null $curl_multi      If you wish to perform asynchronous calls to multiple SOAP services, create a manual
     *                              CURL multi handle with curl_multi_init() and pass it to all the Soap clients in this parameter.
     */
    public function __construct($wsdl, array $options = null, $curl_multi = null) {

        if ($options && isset($options['curl'])) {
            $this->curl_options = $options['curl'];
            unset($options['curl']);
        } else {
            $this->curl_options = array();
        }

        if (strncasecmp($wsdl, 'http://', 7) == 0 || strncasecmp($wsdl, 'https://', 8) == 0 || strncasecmp($wsdl, 'ftp://', 6) == 0) {
            $single = curl_init($wsdl);
            if ($this->curl_options) {
                curl_setopt_array($single, $this->curl_options);
            }

            curl_setopt($single, CURLOPT_RETURNTRANSFER, true);
            $contents = curl_exec($single);
            if ($contents === false) {
                $errno = curl_errno($single);
                $error = curl_strerror($errno);
            } else {
                $contentType = curl_getinfo($single, CURLINFO_CONTENT_TYPE);
            }
            curl_close($single);
            if ($contents === false) {
                throw new \Exception($error, $errno);
            }

            $wsdl = "data://$contentType;base64,".base64_encode($contents);
        }


        if ($options) {
            parent::__construct($wsdl, $options);
        } else {
            parent::__construct($wsdl);
        }

        $this->curl_multi = $curl_multi;
    }

    /**
     * Performs an asynchronous SOAP call. Syntax is exactly the same as regular __soapCall, except for the added $callback parameter.
     * @param string $methodName The name of the SOAP function to call
     * @param array $parameters Array of parameters to the SOAP function
     * @param callable $callback Callback that will be called when the operation is complete.
     * @param null $options Extra options (see __soapCall)
     * @param null $input_headers Array of input headers to be sent along
     * @param null $output_headers Array of to be filled with the output headers
     */
    public function __soapCallAsync($methodName, $parameters, callable $callback, $options = null, $input_headers = null, &$output_headers = null) {
        if (self::$registry == null) {
            self::$registry = array();
        }
        $regId = uniqid();

        self::$registry[$regId] = array(
            'client' => $this,
            'callback' => $callback,
            'method' => $methodName,
            'params' => $parameters
        );
        $this->private_data = $regId;
        // Regular __soapCall() takes care of all the SOAP XML magic and eventually calls __doRequest();
        $this->__soapCall($methodName, $parameters, $options, $input_headers, $output_headers);
        $this->private_data = null;
    }

    /**
     * Performs an actual request. Also starts/finishes an async request.
     * @param string $request
     * @param string $location
     * @param string $action
     * @param int $version
     * @param int $one_way
     * @return string
     * @throws \Exception
     */
    public function __doRequest($request, $location, $action, $version, $one_way = 0)
    {
        if ($this->finished_result) {
            return $this->finished_result;
        }

        $single = curl_init($location);
        if ($this->curl_options) {
            curl_setopt_array($single, $this->curl_options);
        }

        curl_setopt($single, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($single, CURLOPT_POST, true);
        curl_setopt($single, CURLOPT_POSTFIELDS, $request);

        if ($this->private_data) {
            // Async requests
            curl_setopt($single, CURLOPT_PRIVATE, $this->private_data);

            if ($this->curl_multi === null) {
                $this->curl_multi = curl_multi_init();
            }

            curl_multi_add_handle($this->curl_multi, $single);

            $this->__processMessages_LhKI2oZ6YD();
            return '';
        } else {
            // Sync requests
            $contents = curl_exec($single);
            if ( $contents === false ){
                $errno = curl_errno($single);
                $error = curl_strerror($errno);
            }
            curl_close($single);
            if ($contents === false) {
                throw new \Exception($error, $errno);
            } else {
                return $contents;
            }
        }
    }

    /**
     * Call this repeatedly to process the outstanding connections. This method blocks for up to $timeout seconds before
     * giving up if there is no activity. If there is any activity, processes that and returns.
     * @param float $timeout Timeout in seconds to wait for any activity.
     * @throws \Exception
     */
    public function __waitForAny($timeout = 0) {

        if ($this->active) {
            // A long standing bug in PHP - if curl_multi_select() returns -1, you need to sleep for teensy tiny bit and try again. Mostly even a single microsecond is enough.
            // See: https://bugs.php.net/bug.php?id=61141
            do {
                $ret = curl_multi_select($this->curl_multi, $timeout);
                if ($ret === -1) {
                    usleep(250);
                }
            } while ($ret === -1);
        }

        $this->__processMessages_LhKI2oZ6YD();
    }

    /** Waits until all the requests have finished processing. */
    public function __waitForAll() {
        while ($this->active) {
            $this->__waitForAny(1000);
        }
    }

    /** Processes whatever is done. */
    private function __processMessages_LhKI2oZ6YD() {
        do {
            // Process as far as possible at the moment.
            $ret = curl_multi_exec($this->curl_multi, $this->active);
        } while ($ret == CURLM_CALL_MULTI_PERFORM);

        if ($ret != CURLM_OK) {
            throw new \Exception(curl_multi_strerror($ret), $ret);
        }

        while ($message = curl_multi_info_read($this->curl_multi)) {
            $handle = $message['handle'];
            $tagId = curl_getinfo($handle, CURLINFO_PRIVATE);
            if (!is_string($tagId) || self::$registry == null || !isset(self::$registry[$tagId])) {
                continue;
            }
            $tag = self::$registry[$tagId];
            unset(self::$registry[$tagId]);

            $status = curl_getinfo($handle, CURLINFO_HTTP_CODE);
            $contents = curl_multi_getcontent($handle);
            curl_multi_remove_handle($this->curl_multi, $handle);
            curl_close($handle);

            if ($message['result'] != CURLE_OK || $status != 200) {
                call_user_func($tag['callback'], null, array(
                    'curl_errno' => $message['result'],
                    'curl_error' => curl_strerror($message['result']),
                    'http_code' => $status,
                    'http_contents' => $contents
                ));

                continue;
            }

            try {
                /** @var SoapClientAsync $client */
                $client = $tag['client'];
                $client->finished_result = $contents;
                $goodResult = $client->__soapCall($tag['method'], $tag['params']);
                $badResult = null;
            } catch (\Exception $ex) {
                $goodResult = null;
                $badResult = $ex;
            }

            if (is_soap_fault($goodResult)) {
                $badResult = $goodResult;
                $goodResult = null;
            }

            call_user_func($tag['callback'], $goodResult, $badResult);
        }
    }
}