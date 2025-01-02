<?php
    namespace con4gis\ReservationBundle\Classes\Notifications;
    
    use Terminal42\NotificationCenterBundle\Token\Definition\Factory\TokenDefinitionFactoryInterface;
    use Terminal42\NotificationCenterBundle\NotificationType\NotificationTypeInterface;
    use Terminal42\NotificationCenterBundle\Token\Definition\AnythingTokenDefinition;

    class C4gReservationCancellationNotificationType implements NotificationTypeInterface
    {
        public const NAME = 'con4gis_cancellation';

        public function __construct(private TokenDefinitionFactoryInterface $factory)
        {
            $this->factory = $factory;
        }

        public function getName(): string
        {
            return self::NAME;
        }

        public function getTokenDefinitions(): array
        {
            // $email_text           = array('firstname','lastname','reservation_id','beginDate', 'beginTime', 'endDate', 'endTime');
            $return =  [
                $this->factory->create(AnythingTokenDefinition::class, 'admin_email', 'admin_email'),
                $this->factory->create(AnythingTokenDefinition::class, 'email', 'email'), 
                $this->factory->create(AnythingTokenDefinition::class, 'contact_email', 'contact_email'), 
                $this->factory->create(AnythingTokenDefinition::class, 'beginDate', 'beginDate'), 
                $this->factory->create(AnythingTokenDefinition::class, 'beginTime', 'beginTime'),   
                $this->factory->create(AnythingTokenDefinition::class, 'endDate', 'endDate'),
                $this->factory->create(AnythingTokenDefinition::class, 'endTime', 'endTime'),
                $this->factory->create(AnythingTokenDefinition::class, 'firstname', 'firstname'),
                $this->factory->create(AnythingTokenDefinition::class, 'lastname', 'lastname'),  
                $this->factory->create(AnythingTokenDefinition::class, 'reservation_id', 'reservation_id'),
                // $this->factory->create(AnythingTokenDefinition::class, 'email_text', $email_text),
            ];
            return $return;
        }
    }
?>
