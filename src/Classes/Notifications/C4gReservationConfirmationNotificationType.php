<?php
    namespace con4gis\ReservationBundle\Classes\Notifications;

    use Terminal42\NotificationCenterBundle\Token\Definition\Factory\TokenDefinitionFactoryInterface;
    use Terminal42\NotificationCenterBundle\NotificationType\NotificationTypeInterface;
    use Terminal42\NotificationCenterBundle\Token\Definition\AnythingTokenDefinition;
    use Terminal42\NotificationCenterBundle\Token\Definition\TextTokenDefinition;

    class C4gReservationConfirmationNotificationType implements NotificationTypeInterface
    {
        public const NAME = 'con4gis_reservation_confirmation';

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
            return [
                $this->factory->create(AnythingTokenDefinition::class, 'admin_email', 'admin_email'),
                $this->factory->create(AnythingTokenDefinition::class, 'email', 'email'), 
                $this->factory->create(AnythingTokenDefinition::class, 'email2', 'email2'), 
                $this->factory->create(AnythingTokenDefinition::class, 'contact_email', 'contact_email'),
                $this->factory->create(AnythingTokenDefinition::class, 'contact_website', 'contact_website'),
                $this->factory->create(AnythingTokenDefinition::class, 'member_email', 'member_email'),
                $this->factory->create(AnythingTokenDefinition::class, 'reservation_type', 'reservation_type'), 
                $this->factory->create(AnythingTokenDefinition::class, 'desiredCapacity', 'desiredCapacity'), 
                $this->factory->create(AnythingTokenDefinition::class, 'beginDate', 'beginDate'), 
                $this->factory->create(AnythingTokenDefinition::class, 'beginTime', 'beginTime'),   
                $this->factory->create(AnythingTokenDefinition::class, 'endDate', 'endDate'),
                $this->factory->create(AnythingTokenDefinition::class, 'endTime', 'endTime'),
                $this->factory->create(AnythingTokenDefinition::class, 'reservation_object', 'reservation_object'),
                $this->factory->create(AnythingTokenDefinition::class, 'reservation_title', 'reservation_title'),
                $this->factory->create(AnythingTokenDefinition::class, 'included_params', 'included_params'),
                $this->factory->create(AnythingTokenDefinition::class, 'additional_params', 'additional_params'),
                $this->factory->create(AnythingTokenDefinition::class, 'participantList', 'participantList'),
                $this->factory->create(AnythingTokenDefinition::class, 'salutation', 'salutation'),
                $this->factory->create(AnythingTokenDefinition::class, 'salutation2', 'salutation2'),
                $this->factory->create(AnythingTokenDefinition::class, 'title', 'title'),
                $this->factory->create(AnythingTokenDefinition::class, 'title2', 'title2'),
                $this->factory->create(AnythingTokenDefinition::class, 'organisation', 'organisation'),
                $this->factory->create(AnythingTokenDefinition::class, 'organisation2', 'organisation2'),
                $this->factory->create(AnythingTokenDefinition::class, 'firstname', 'firstname'),
                $this->factory->create(AnythingTokenDefinition::class, 'firstname2', 'firstname2'),
                $this->factory->create(AnythingTokenDefinition::class, 'lastname', 'lastname'), 
                $this->factory->create(AnythingTokenDefinition::class, 'lastname2', 'lastname2'), 
                $this->factory->create(AnythingTokenDefinition::class, 'phone', 'phone'), 
                $this->factory->create(AnythingTokenDefinition::class, 'phone2', 'phone2'), 
                $this->factory->create(AnythingTokenDefinition::class, 'address', 'address'), 
                $this->factory->create(AnythingTokenDefinition::class, 'address2', 'address2'), 
                $this->factory->create(AnythingTokenDefinition::class, 'postal', 'postal'), 
                $this->factory->create(AnythingTokenDefinition::class, 'postal2', 'postal2'), 
                $this->factory->create(AnythingTokenDefinition::class, 'city', 'city'), 
                $this->factory->create(AnythingTokenDefinition::class, 'city2', 'city2'), 
                $this->factory->create(AnythingTokenDefinition::class, 'dateOfBirth', 'dateOfBirth'), 
                $this->factory->create(AnythingTokenDefinition::class, 'comment', 'comment'), 
                $this->factory->create(AnythingTokenDefinition::class, 'internal_comment', 'internal_comment'), 
                $this->factory->create(AnythingTokenDefinition::class, 'contact_name', 'contact_name'), 
                $this->factory->create(AnythingTokenDefinition::class, 'contact_phone', 'contact_phone'), 
                $this->factory->create(AnythingTokenDefinition::class, 'contact_street', 'contact_street'), 
                $this->factory->create(AnythingTokenDefinition::class, 'contact_postal', 'contact_postal'), 
                $this->factory->create(AnythingTokenDefinition::class, 'contact_city', 'contact_city'), 
                $this->factory->create(AnythingTokenDefinition::class, 'reservation_id', 'reservation_id'),
                $this->factory->create(AnythingTokenDefinition::class, 'location', 'location'),
                $this->factory->create(AnythingTokenDefinition::class, 'speaker', 'speaker'),
                $this->factory->create(AnythingTokenDefinition::class, 'topic', 'topic'),
                $this->factory->create(AnythingTokenDefinition::class, 'audience', 'audience'),
                $this->factory->create(AnythingTokenDefinition::class, 'agreed', 'agreed'),
                $this->factory->create(AnythingTokenDefinition::class, 'description', 'description'),
                $this->factory->create(AnythingTokenDefinition::class, 'additional1', 'additional1'),
                $this->factory->create(AnythingTokenDefinition::class, 'additional2', 'additional2'),
                $this->factory->create(AnythingTokenDefinition::class, 'additional3', 'additional3'),
                $this->factory->create(AnythingTokenDefinition::class, 'price', 'price'),
                $this->factory->create(AnythingTokenDefinition::class, 'priceTax', 'priceTax'),
                $this->factory->create(AnythingTokenDefinition::class, 'priceSum', 'priceSum'),
                $this->factory->create(AnythingTokenDefinition::class, 'priceSumTax', 'priceSumTax'),
                $this->factory->create(AnythingTokenDefinition::class, 'priceNet', 'priceNet'),
                $this->factory->create(AnythingTokenDefinition::class, 'priceSumNet', 'priceSumNet'),
                $this->factory->create(AnythingTokenDefinition::class, 'priceOptionSum', 'priceOptionSum'),
                $this->factory->create(AnythingTokenDefinition::class, 'priceOptionSumNet', 'priceOptionSumNet'), 
                $this->factory->create(AnythingTokenDefinition::class, 'priceOptionSumTax', 'priceOptionSumTax'), 
                $this->factory->create(AnythingTokenDefinition::class, 'reservationTaxRate', 'reservationTaxRate'), 
                $this->factory->create(AnythingTokenDefinition::class, 'dbkey', 'dbkey'), 
                $this->factory->create(AnythingTokenDefinition::class, 'uploadFile', 'uploadFile'),  
            ];
        }
    }
?>