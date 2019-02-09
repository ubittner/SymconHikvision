<?php

// Declare
declare(strict_types=1);

trait alerting
{
    //#################### Alerting

    /**
     * Executes a variable and/or script in case of alerting.
     *
     * @param bool $State
     */
    protected function ExecuteAlerting(bool $State)
    {
        // Camera alarm output
        if ($this->ReadPropertyBoolean('UseCameraAlarmOutput')) {
            $this->TriggerCameraAlarmOutput(1, $State);
        }
        // Variables
        $variables = json_decode($this->ReadPropertyString('TargetVariableList'));
        if (!empty($variables)) {
            foreach ($variables as $variable) {
                if ($variable->UseVariable) {
                    RequestAction($variable->ID, $State);
                }
            }
        }
        // Scripts
        $scripts = json_decode($this->ReadPropertyString('TargetScriptList'));
        if (!empty($scripts)) {
            foreach ($scripts as $script) {
                if ($script->UseScript) {
                    IPS_RunScriptEx($script->ID, ['Status' => $State]);
                }
            }
        }
    }
}
