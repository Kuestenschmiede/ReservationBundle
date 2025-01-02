<?php 
     namespace con4gis\ReservationBundle\Classes\Notifications;

     use Terminal42\NotificationCenterBundle\NotificationCenter;

     class C4gNotificationCenterService {

        public function __construct(private NotificationCenter $notificationCenter){}

        public function getNotificationCenter() {
            return $this->notificationCenter;
        }
     }
?>