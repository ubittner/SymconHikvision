<?php

// Declare
declare(strict_types=1);

trait Control
{
    //################### Control

    /**
     * Toggles the monitoring switch.
     *
     * @param bool $State
     */
    public function ToggleMonitoring(bool $State)
    {
        // Get state
        $switchState = $this->GetValue('Monitoring');
        // Set state
        $this->SetValue('Monitoring', $State);
        // Toggle switch
        if ($switchState != $State) {
            // Monitoring is active
            if ($State) {
                $this->TriggerAlert(true);
            }
            // Monitoring is inactive
            if (!$State) {
                // Revert alarm variables and scripts
                $this->ExecuteAlerting(false);
                // Revert values to origin defined values
                $this->SetValue('LimitPeopleMaximum', $this->ReadPropertyInteger('LimitPeopleMaximum'));
                $this->SetValue('ThresholdCriticalState', $this->ReadPropertyInteger('ThresholdCriticalState'));
            }
            // Disable change threshold switch when monitoring is active an vice versa
            IPS_SetHidden($this->GetIDForIdent('ChangeThresholdCriticalState'), $State);
            IPS_SetHidden($this->GetIDForIdent('Counter'), $State);
        }
    }

    /**
     * Changes the threshold value for the critical state.
     *
     * @param int $Value
     */
    public function ChangeThresholdCriticalState(int $Value)
    {
        $thresholdCriticalState = $this->ReadPropertyInteger('ThresholdCriticalState');
        $actualThreshold = $this->GetValue('ThresholdCriticalState');
        $limitPeopleMaximum = $this->ReadPropertyInteger('LimitPeopleMaximum');
        switch ($Value) {
            // Decrease value by 10
            case 0:
                if ($actualThreshold > 10) {
                    $this->SetValue('ThresholdCriticalState', $actualThreshold - 10);
                }
                break;
            // Set default value
            case 1:
                $this->SetValue('ThresholdCriticalState', $thresholdCriticalState);
                break;
            // Increase value by 10
            case 2:
                if ($actualThreshold < $limitPeopleMaximum - 10) {
                    $this->SetValue('ThresholdCriticalState', $actualThreshold + 10);
                }
                break;
        }
    }

    /**
     * Displays the registered variables for messages.
     */
    public function DisplayMessageList()
    {
        print_r($this->GetMessageList());
    }

    /**
     * Resets the people counter.
     */
    public function ResetPeopleCounter()
    {
        // Revert values
        $this->SetValue('PeopleInRoom', 0);
        $this->SetValue('PeopleEnter', 0);
        $this->SetValue('PeopleExit', 0);
        $this->SetValue('PeoplePass', 0);
        // Reset camera people counter
        $this->ResetCameraPeopleCounter();
    }

    /**
     * Triggers an alert.
     *
     * @param bool $UseOverride
     */
    private function TriggerAlert(bool $UseOverride)
    {
        $peopleInRoom = $this->GetValue('PeopleInRoom');
        $thresholdCriticalState = $this->GetValue('ThresholdCriticalState');
        $limitPeopleMaximum = $this->GetValue('LimitPeopleMaximum');
        $state = 0;
        $alertingState = false;
        if ($peopleInRoom < $thresholdCriticalState) {
            $state = 0;
        }
        if (($peopleInRoom >= $thresholdCriticalState) && ($peopleInRoom < $limitPeopleMaximum)) {
            $state = 1;
            $alertingState = false;
        }
        if ($peopleInRoom >= $limitPeopleMaximum) {
            $state = 2;
            $alertingState = true;
        }
        // Get actual state
        $actualState = $this->GetValue('ActualState');
        // Set actual state
        $this->SetValue('ActualState', $state);
        // Only trigger alert if monitoring is started and the value has changed
        if ($this->ReadPropertyBoolean('UseMonitoring') && $this->GetValue('Monitoring')) {
            if ($actualState != $state || $UseOverride) {
                // Notification
                $this->SendNotification($state);
                $this->ExecuteAlerting($alertingState);
            }
        }
    }
}