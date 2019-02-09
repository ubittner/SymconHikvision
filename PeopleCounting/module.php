<?php

/*
 * @module      Hikvision PeopleMonitoring
 *
 * @prefix      HV
 *
 * @file        module.php
 *
 * @developer   Ulrich Bittner
 * @project     A joint project of Normen Thiel and Ulrich Bittner
 * @copyright   (c) 2019
 * @license    	CC BY-NC-SA 4.0
 *              https://creativecommons.org/licenses/by-nc-sa/4.0/
 *
 * @version     1.00-1
 * @date        2019-01-11, 18:00
 * @lastchange  2019-01-11, 18:00
 *
 * @see         https://github.com/ubittner/SymconHikvision
 *
 * @guids       Library
 *              {78F1F259-B1ED-2723-B27E-4F35A0EABF86}
 *
 *              People Monitoring
 *              {05986539-30D5-6B74-CD44-2573FD73BAE8}
 *
 * @changelog   2019-01-11, 18:00, initial version 1.00-1
 *
 */

// Declare
declare(strict_types=1);

// Include
include_once __DIR__ . '/helper/autoload.php';

class HikvisionPeopleCounting extends IPSModule
{
    // Helper
    use Control;
    use EndpointParameters;
    use Notifications;
    use Alerting;

    public function Create()
    {
        // Never delete this line!
        parent::Create();

        // Register properties
        $this->RegisterPropertyBoolean('UseMonitoring', false);
        $this->RegisterPropertyString('RoomDesignation', '');

        $this->RegisterPropertyString('Host', '');
        $this->RegisterPropertyInteger('Timeout', 2000);
        $this->RegisterPropertyString('Username', '');
        $this->RegisterPropertyString('Password', '');
        $this->RegisterPropertyInteger('Channel', 1);

        $this->RegisterPropertyInteger('LimitPeopleMaximum', 200);
        $this->RegisterPropertyInteger('ThresholdCriticalState', 180);

        $this->RegisterPropertyString('TitleDescription', '');
        $this->RegisterPropertyString('NotificationVariants', '[{"Status":0,"MessageText":"' . $this->Translate('The number of people is uncritical.') . '"},{"Status":1,"MessageText":"' . $this->Translate('The number of people is critical!') . '"},{"Status":2,"MessageText":"' . $this->Translate('The number of people has exceeded the limit!') . '"}]');
        $this->RegisterPropertyString('WebFrontNotificationList', '');
        $this->RegisterPropertyString('PushNotificationList', '');
        $this->RegisterPropertyString('EmailRecipientList', '');

        $this->RegisterPropertyBoolean('UseCameraAlarmOutput', false);
        $this->RegisterPropertyString('TargetVariableList', '');
        $this->RegisterPropertyString('TargetScriptList', '');

        // Register profiles and variables

        // Monitoring
        $profile = 'HV.' . $this->InstanceID . '.Monitoring';
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 0);
        }
        IPS_SetVariableProfileAssociation($profile, 0, $this->Translate('Off'), '', 0xFF0000);
        IPS_SetVariableProfileAssociation($profile, 1, $this->Translate('On'), '', 0x00FF00);
        $this->RegisterVariableBoolean('Monitoring', $this->Translate('Monitoring'), $profile, 1);
        IPS_SetIcon($this->GetIDForIdent('Monitoring'), 'Camera');
        $this->EnableAction('Monitoring');

        // Actual state
        $profile = 'HV.' . $this->InstanceID . '.ActualState';
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 1);
        }
        IPS_SetVariableProfileAssociation($profile, 0, 'OK', 'Ok', 0x00FF00);
        IPS_SetVariableProfileAssociation($profile, 1, $this->Translate('Threshold critical'), 'People', 0xFFFF00);
        IPS_SetVariableProfileAssociation($profile, 2, $this->Translate('Limit exceeded'), 'Warning', 0xFF0000);
        $this->RegisterVariableInteger('ActualState', $this->Translate('Actual state'), $profile, 2);
        $this->SetValue('ActualState', 0);

        // People in the room
        $profile = 'HV.' . $this->InstanceID . '.PeopleInRoom';
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 1);
        }
        IPS_SetVariableProfileIcon($profile, 'People');
        $this->RegisterVariableInteger('PeopleInRoom', $this->Translate('People in the room'), $profile, 3);

        // Enter
        $profile = 'HV.' . $this->InstanceID . '.PeopleEnter';
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 1);
        }
        IPS_SetVariableProfileIcon($profile, 'HollowArrowRight');
        $this->RegisterVariableInteger('PeopleEnter', $this->Translate('Enter'), $profile, 4);

        // Leave
        $profile = 'HV.' . $this->InstanceID . '.PeopleExit';
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 1);
        }
        IPS_SetVariableProfileIcon($profile, 'HollowArrowLeft');
        $this->RegisterVariableInteger('PeopleExit', $this->Translate('Exit'), $profile, 4);

        // Pass
        $profile = 'HV.' . $this->InstanceID . '.PeoplePass';
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 1);
        }
        IPS_SetVariableProfileIcon($profile, 'HollowDoubleArrowRight');
        $this->RegisterVariableInteger('PeoplePass', $this->Translate('Pass'), $profile, 5);

        // Limit people maximum
        $profile = 'HV.' . $this->InstanceID . '.LimitPeopleMaximum';
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 1);
        }
        IPS_SetVariableProfileIcon($profile, 'Warning');
        $this->RegisterVariableInteger('LimitPeopleMaximum', $this->Translate('Maximum number of people in the room'), $profile, 6);
        $this->SetValue('LimitPeopleMaximum', $this->ReadPropertyInteger('LimitPeopleMaximum'));

        // Threshold critical state
        $profile = 'HV.' . $this->InstanceID . '.ThresholdCriticalState';
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 1);
        }
        IPS_SetVariableProfileIcon($profile, 'Information');
        $this->RegisterVariableInteger('ThresholdCriticalState', $this->Translate('Threshold critical state'), $profile, 7);
        $this->SetValue('ThresholdCriticalState', $this->ReadPropertyInteger('ThresholdCriticalState'));

        // Change threshold critical state
        $profile = 'HV.' . $this->InstanceID . '.ChangeThresholdCriticalState';
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 1);
        }
        IPS_SetVariableProfileAssociation($profile, 0, '- 10', '', 0xFF0000);
        IPS_SetVariableProfileAssociation($profile, 1, $this->Translate('Standard value'), '', 0x0000FF);
        IPS_SetVariableProfileAssociation($profile, 2, '+ 10', '', 0x00FF00);
        IPS_SetVariableProfileIcon($profile, 'Edit');
        $this->RegisterVariableInteger('ChangeThresholdCriticalState', $this->Translate('Change threshold critical state'), $profile, 8);
        $this->EnableAction('ChangeThresholdCriticalState');
        $this->SetValue('ChangeThresholdCriticalState', 1);

        // Reset counter
        $profile = 'HV.' . $this->InstanceID . '.ResetCounter';
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 1);
        }
        IPS_SetVariableProfileAssociation($profile, 0, $this->Translate('Reset'), 'Repeat', 0xFF0000);
        $this->RegisterVariableInteger('Counter', $this->Translate('Counter'), $profile, 9);
        $this->EnableAction('Counter');

        // Connect to server socket I/O
        $this->ConnectParent('{8062CF2B-600E-41D6-AD4B-1BA66C32D6ED}');
    }

    public function ApplyChanges()
    {
        // Wait until IP-Symcon is started
        $this->RegisterMessage(0, IPS_KERNELSTARTED);

        // Never delete this line!
        parent::ApplyChanges();

        // Check kernel runlevel
        if (IPS_GetKernelRunlevel() != KR_READY) {
            return;
        }

        // Validate configuration
        $this->ValidateConfiguration();

        // Rename instance
        $name = $this->ReadPropertyString('RoomDesignation');
        if (empty($name)) {
            $name = $this->Translate('People Counting');
        }
        IPS_SetName($this->InstanceID, $name);

        // Reset thresholds
        $this->SetValue('LimitPeopleMaximum', $this->ReadPropertyInteger('LimitPeopleMaximum'));
        $this->SetValue('ThresholdCriticalState', $this->ReadPropertyInteger('ThresholdCriticalState'));

        // Register variable for message update
        $variableID = $this->GetIDForIdent('PeopleInRoom');
        $this->RegisterMessage($variableID, VM_UPDATE);

        // Trigger alert
        $this->TriggerAlert(true);
    }

    public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
    {
        /*
        // Log message
        IPS_LogMessage('MessageSink', 'Message from SenderID ' . $SenderID . ' with Message ' . $Message . "\r\n Data: " . print_r($Data, true));
        */
        switch ($Message) {
            case IPS_KERNELSTARTED:
                $this->KernelReady();
                break;
            case VM_UPDATE:
                $this->TriggerAlert(false);
                break;
        }
    }

    /**
     * Apply changes when the kernel is ready.
     */
    private function KernelReady()
    {
        $this->ApplyChanges();
    }

    public function Destroy()
    {
        // Never delete this line!
        parent::Destroy();

        // Delete profiles
        $this->DeleteProfiles();
    }

    //#################### Server socket I/O

    /**
     * Receives data from the server socket I/O.
     *
     * @param $JSONString
     *
     * @return bool|void
     */
    public function ReceiveData($JSONString)
    {
        $data = utf8_decode(json_decode($JSONString)->Buffer);
        $this->SendDebug('Buffer', $data, 0);
        // Check statistic method
        preg_match_all('/\<statisticalMethods\>(.*)\<\/statisticalMethods\>/', $data, $statisticMethod);
        if ($statisticMethod[1][0] == 'realTime') {
            preg_match_all('/\<enter\>(.*)\<\/enter\>/', $data, $enter);
            preg_match_all('/\<exit\>(.*)\<\/exit\>/', $data, $exit);
            preg_match_all('/\<pass\>(.*)\<\/pass\>/', $data, $pass);
            $peopleEnter = null;
            if (!empty($enter[1])) {
                $peopleEnter = $enter[1][0];
                $this->SetValue('PeopleEnter', $peopleEnter);
            }
            $peopleExit = null;
            if (!empty($exit[1])) {
                $peopleExit = $exit[1][0];
                $this->SetValue('PeopleExit', $peopleExit);
            }
            if (!empty($pass[1])) {
                $peoplePass = $pass[1][0];
                $this->SetValue('PeoplePass', $peoplePass);
            }
            if (!is_null($peopleEnter) && !is_null($peopleExit)) {
                $this->SetValue('PeopleInRoom', $peopleEnter - $peopleExit);
            }
        }
    }

    //#################### Request action

    public function RequestAction($Ident, $Value)
    {
        try {
            switch ($Ident) {
                case 'Monitoring':
                    $this->ToggleMonitoring($Value);
                    break;
                case 'ChangeThresholdCriticalState':
                    $this->ChangeThresholdCriticalState($Value);
                    break;
                case 'Counter':
                    $this->ResetPeopleCounter();
                    break;
                default:
                    throw new Exception('Invalid Ident');
            }
        } catch (Exception $e) {
            $this->LogMessage('Hikvision ' . $this->InstanceID . ', ' . $e->getMessage(), 10205);
        }
    }

    //#################### Private function

    /**
     * Checks if the parent instance (server socket I/O) is active.
     *
     * @return bool
     */
    protected function HasActiveParent()
    {
        $state = false;
        $instanceID = @IPS_GetInstance($this->InstanceID);
        $parentID = $instanceID['ConnectionID'];
        if ($parentID > 0) {
            if (@IPS_GetInstance($parentID)['InstanceStatus'] == 102) {
                $state = true;
            }
        }
        if (!$state) {
            $this->LogMessage('Hikvision ' . $this->InstanceID . ', Server socket I/O is not active.', 10204);
        }
        return $state;
    }

    /**
     * Validates the configuration.
     */
    private function ValidateConfiguration()
    {
        $validate = true;
        $status = 102;
        $state = true;
        // Check host
        $host = $this->ReadPropertyString('Host');
        if (empty($host)) {
            $state = false;
        } else {
            $timeout = $this->ReadPropertyInteger('Timeout');
            $device = Sys_Ping($host, $timeout);
            if (!$device) {
                $this->LogMessage('Hikvision ' . $this->InstanceID . ', Device not reachable.', 10205);
                $state = false;
            } else {
                // Check user
                $username = $this->ReadPropertyString('Username');
                if (empty($username)) {
                    $this->LogMessage('Hikvision ' . $this->InstanceID . ', Username is missing.', 10205);
                    $state = false;
                } else {
                    // Check password
                    $password = $this->ReadPropertyString('Password');
                    if (empty($password)) {
                        $this->LogMessage('Hikvision ' . $this->InstanceID . ', Password is missing.', 10205);
                        $state = false;
                    } else {
                        // Check credentials
                        $user = $this->CheckCameraUser();
                        if (!is_null($user)) {
                            $credentials = $user->statusValue;
                            if ($credentials != 200) {
                                $this->LogMessage('Hikvision ' . $this->InstanceID . ', Wrong credentials.', 10205);
                                $state = false;
                            }
                        }
                    }
                }
            }
        }
        /*
        $parent = $this->HasActiveParent();
        if (!$parent) {
            $state = false;
        }
        */
        if (!$state) {
            $validate = false;
            $status = 201;
        }
        $this->SetStatus($status);
        return $validate;
    }

    /**
     * Deletes the profiles.
     */
    private function DeleteProfiles()
    {
        $profiles = ['Monitoring', 'ActualState', 'PeopleInRoom', 'PeopleEnter', 'PeopleExit', 'PeoplePass', 'LimitPeopleMaximum', 'ThresholdCriticalState', 'ChangeThresholdCriticalState', 'ResetCounter'];
        foreach ($profiles as $profile) {
            $profileName = 'HV.' . $this->InstanceID . '.' . $profile;
            if (IPS_VariableProfileExists($profileName)) {
                IPS_DeleteVariableProfile($profileName);
            }
        }
    }
}
