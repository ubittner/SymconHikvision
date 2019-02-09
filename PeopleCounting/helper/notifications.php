<?php

// Declare
declare(strict_types=1);

trait Notifications
{
    //#################### Notifications

    /**
     * Sends a WebFront, push or e-mail notification.
     *
     * @param int $State
     */
    protected function SendNotification(int $State)
    {
        // Get timestamp
        $timeStamp = date('d.m.Y, H:i:s');
        // Get title
        $title = substr($this->ReadPropertyString('TitleDescription'), 0, 32);
        // Get location designation
        $roomDesignation = $this->ReadPropertyString('RoomDesignation');
        // Get status text
        $statusText = '';
        $notificationVariants = json_decode($this->ReadPropertyString('NotificationVariants'));
        if (!empty($notificationVariants)) {
            foreach ($notificationVariants as $notificationVariant) {
                if ($notificationVariant->Status == $State) {
                    $statusText = $notificationVariant->MessageText;
                }
            }
        }
        // Create text
        $text = $roomDesignation . ', ' . $statusText . "\n\n" . $timeStamp . ', ' . $this->Translate('People') . ': ' . $this->GetValue('PeopleInRoom') . ', ' . $statusText;
        $this->SendDebug('SendNotification', $title . ' ' . $text, 0);
        // Icon and timeout
        switch ($State) {
            case 0:
                $icon = 'Information';
                $sound = 'bell';
                $timeout = 10;
                break;
            case 1:
                $icon = 'Warning';
                $sound = 'alarm';
                $timeout = 10;
                break;
            case 2:
                $icon = 'Warning';
                $sound = 'siren';
                $timeout = 0;
                break;
        }
        // WebFront notification
        $webFronts = json_decode($this->ReadPropertyString('WebFrontNotificationList'));
        if (!empty($webFronts)) {
            foreach ($webFronts as $webFront) {
                if ($webFront->UseNotification) {
                    $moduleID = IPS_GetInstance($webFront->ID)['ModuleInfo']['ModuleID'];
                    if ($webFront->ID != 0 && IPS_ObjectExists($webFront->ID) && $moduleID == WEBFRONT_GUID) {
                        WFC_SendNotification($webFront->ID, $title, $text, $icon, $timeout);
                    }
                }
            }
        }
        // Push notification
        $webFronts = json_decode($this->ReadPropertyString('PushNotificationList'));
        if (!empty($webFronts)) {
            foreach ($webFronts as $webFront) {
                if ($webFront->UseNotification) {
                    $moduleID = IPS_GetInstance($webFront->ID)['ModuleInfo']['ModuleID'];
                    if ($webFront->ID != 0 && IPS_ObjectExists($webFront->ID) && $moduleID == WEBFRONT_GUID) {
                        WFC_PushNotification($webFront->ID, $title, $text, $sound, 0);
                    }
                }
            }
        }
        // E-mail notification
        $recipients = json_decode($this->ReadPropertyString('EmailRecipientList'));
        if (!empty($recipients)) {
            foreach ($recipients as $recipient) {
                if ($recipient->UseNotification) {
                    $moduleID = IPS_GetInstance($recipient->ID)['ModuleInfo']['ModuleID'];
                    if ($recipient->ID != 0 && IPS_ObjectExists($recipient->ID) && $moduleID == MAIL_GUID) {
                        SMTP_SendMailEx($recipient->ID, $recipient->Address, $title, $text);
                    }
                }
            }
        }
    }
}