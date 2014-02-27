<?php
/**
 * CAssemblaLogRoute class for sending messages about errors, exceptions to Assembla.
 * Create a ticket with a description of the error.
 *
 */
class CAssemblaLogRoute extends CLogRoute {

    /**
     * @var string X-Api-Key
     */
    public $apiKey = '';

    /**
     * @var string X-Api-Secret
     */
    public $apiSecret = '';

    /**
     * @var string The domain for the exception. Locally will not send notice.
     */
    public $excludeHost = 'mysite.loc';

    /**
     * @var array excluding extensions
     */
    public $excludePositons = array(
        '.ico',
        '.png',
        '.jpg',
    );

    /**
     * @var string Prefix the name of tickets
     */
    public $deployPrefix = 'DEV-SITE';

    /**
     * Method of forming an error message and a reference to the method of curl Assembla
     *
     * @param array $logs
     * @return bool
     */
    protected function processLogs($logs)
    {
        $log = $logs[0];

        if(!is_array($log) || (strstr($_SERVER['HTTP_HOST'], $this->excludeHost))) {
            return false;
        }

        if(preg_match('/('.implode('|',$this->excludePositons).')/s', $log[0])) {
            return false;
        }

        $apiKey = $this->apiKey;
        $apiSecret = $this->apiSecret;

        $message = array(
            'ticket' => array(
                'summary' => '['. $this->deployPrefix . '] ' . $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].' --- '.$log[2],
                'description' => isset($_SERVER['HTTP_REFERER']) ? 'Referer: ' . $_SERVER['HTTP_REFERER'] . '<br/>'.$log[0] :  $log[0],
            )
        );

        $ch = curl_init();

        $headers = array(
            'X-Api-Key: '.$apiKey,
            'X-Api-Secret: '.$apiSecret,
            'Content-type: application/json',
        );

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, 'http://api.assembla.com/v1/spaces/virtual-health/tickets.xml');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, CJSON::encode($message));
        $answer = curl_exec($ch);


        if (curl_errno($ch)) {
            print "Error: " . curl_error($ch);
        } else {
            curl_close($ch);
        }
    }
}