<?php

// Declare
declare(strict_types=1);

trait EndpointParameters
{
    //#################### Endpoint parameters

    public function CheckCameraUser()
    {
        $user = null;
        $method = 'GET';
        $endpoint = '/ISAPI/Security/userCheck';
        $user = $this->SendDataToCamera($method, $endpoint, '');
        return $user;
    }

    /**
     * Gets information about the device.
     *
     * @return null|SimpleXMLElement
     */
    public function GetCameraInformation()
    {
        $info = null;
        $method = 'GET';
        $endpoint = '/ISAPI/System/deviceinfo';
        $info = $this->SendDataToCamera($method, $endpoint, '');
        return $info;
    }

    /**
     * Resets the camera people counter.
     *
     * @return null|SimpleXMLElement
     */
    public function ResetCameraPeopleCounter()
    {
        $data = null;
        $channel = $this->ReadPropertyInteger('Channel');
        if (!empty($channel)) {
            $method = 'PUT';
            $endpoint = '/ISAPI/System/Video/inputs/channels/' . $channel . '/counting/resetCount';
            $data = $this->SendDataToCamera($method, $endpoint, '');
        }
        return $data;
    }

    /**
     * Triggers the alarm output of the camera.
     *
     * @param int  $Output
     * @param bool $State
     *
     * @return SimpleXMLElement|null
     */
    public function TriggerCameraAlarmOutput(int $Output, bool $State)
    {
        $data = null;
        switch ($State) {
            case 0:
                $trigger = "low";
                break;
            case 1:
                $trigger = "high";
                break;
            default:
                $trigger = "low";
        }
        $method = 'PUT';
        $endpoint = '/ISAPI/System/IO/outputs/' . $Output . '/trigger';
        $postfields = '<IOPortData version="1.0" xmlns="http://www.hikvision.com/ver10/XMLSchema">\n<outputState>' . $trigger . '</outputState>\n</IOPortData>';
        $data = $this->SendDataToCamera($method, $endpoint, $postfields);
        return $data;
    }

    /**
     * Sends data to the camera.
     *
     * @param string $Method
     * @param string $Endpoint
     * @param string $Postfields
     *
     * @return SimpleXMLElement|null
     */
    private function SendDataToCamera(string $Method, string $Endpoint, string $Postfields)
    {
        $xmlData = null;
        $host = $this->ReadPropertyString('Host');
        if (empty($host)) {
            return null;
        }
        $username = $this->ReadPropertyString('Username');
        if (empty($username)) {
            return null;
        }
        $password = rawurlencode($this->ReadPropertyString('Password'));
        if (empty($password)) {
            return null;
        }
        $timeout = $this->ReadPropertyInteger('Timeout');
        $device = Sys_Ping($host, $timeout);
        if ($device) {
            $url = 'http://' . $username . ':' . $password . '@' . $host . $Endpoint;
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_TIMEOUT => 5,
                CURLOPT_CUSTOMREQUEST => $Method,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_HEADER => 0,
                CURLOPT_POSTFIELDS => $Postfields,
                CURLOPT_HTTPHEADER => ['Content-type: text/xml']]);
            $result = curl_exec($curl);
            curl_close($curl);
            $xmlData = new SimpleXMLElement($result);
        }
        return $xmlData;
    }
}