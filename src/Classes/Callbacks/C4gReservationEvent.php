<?php
    namespace con4gis\ReservationBundle\Classes\Callbacks;

    use Contao\Backend;
    use Contao\BackendUser;
    use Contao\Database;
    use Contao\DataContainer;
    use Contao\Input;

    class C4gReservationEvent extends Backend
    {
        /**
         * Import the back end user object
         */
        public function __construct()
        {
            parent::__construct();
            $this->import(BackendUser::class, 'User');
        }

        /**
         * @param \Contao\DataContainer $dc
         */
        public function setParent(DataContainer $dc)
        {
            \Contao\Message::addInfo($GLOBALS['TL_LANG']['tl_c4g_reservation_event']['infoEvent']);

            $pid = intval(Input::get('pid'));

            if (!$pid) {
                $pid = $dc->activeRecord->pid;
                if (!$pid) {
                return $dc;
                }
            }

            $dc->pid = $pid;
            $GLOBALS['TL_DCA']['tl_c4g_reservation_event']['fields']['pid']['default'] = $pid;
            $GLOBALS['TL_DCA']['tl_c4g_reservation_event']['fields']['pid']['sql'] = "int(10) unsigned NOT NULL default ".$pid;

            $calendarId = Database::getInstance()->prepare('SELECT pid FROM tl_calendar_events WHERE id=?')->execute($pid)->fetchAssoc();
            $calendarRow =  $calendarId ? Database::getInstance()->prepare('SELECT * FROM tl_calendar WHERE id=? AND activateEventReservation="1"')->execute($calendarId['pid'])->fetchAssoc() : false;
            if ($calendarRow) {
                $dc->location = $calendarRow['reservationLocation'];
                $dc->organizer = $calendarRow['reservationOrganizer'];
                $dc->reservationType = $calendarRow['reservationType'];
                $dc->minParticipants = $calendarRow['reservationMinParticipants'];
                $dc->maxParticipants = $calendarRow['reservationMaxParticipants'];
                $dc->speaker = $calendarRow['reservationSpeaker'];
                $dc->topic = $calendarRow['reservationTopic'];
                $dc->targetAudience = $calendarRow['reservationtargetAudience'];
                $dc->price = $calendarRow['reservationPrice'];
    //            $dc->taxOptions = $calendarRow['reservationTaxOptions'];
                $dc->priceoption = $calendarRow['reservationPriceOption'];

                $GLOBALS['TL_DCA']['tl_c4g_reservation_event']['fields']['location']['default'] = $calendarRow['reservationLocation'];
                $GLOBALS['TL_DCA']['tl_c4g_reservation_event']['fields']['organizer']['default'] = $calendarRow['reservationOrganizer'];
                $GLOBALS['TL_DCA']['tl_c4g_reservation_event']['fields']['reservationType']['default'] = $calendarRow['reservationType'];
                $GLOBALS['TL_DCA']['tl_c4g_reservation_event']['fields']['minParticipants']['default'] = $calendarRow['reservationMinParticipants'];
                $GLOBALS['TL_DCA']['tl_c4g_reservation_event']['fields']['maxParticipants']['default'] = $calendarRow['reservationMaxParticipants'];
                $GLOBALS['TL_DCA']['tl_c4g_reservation_event']['fields']['speaker']['default'] = $calendarRow['reservationSpeaker'];
                $GLOBALS['TL_DCA']['tl_c4g_reservation_event']['fields']['topic']['default'] = $calendarRow['reservationTopic'];
                $GLOBALS['TL_DCA']['tl_c4g_reservation_event']['fields']['targetAudience']['default'] = $calendarRow['reservationtargetAudience'];
                $GLOBALS['TL_DCA']['tl_c4g_reservation_event']['fields']['price']['default'] = $calendarRow['reservationPrice'];
                $GLOBALS['TL_DCA']['tl_c4g_reservation_event']['fields']['priceoption']['default'] = $calendarRow['reservationPriceOption'];
            }

            return $dc;
        }

        /**
         * Return all themes as array
         * @return array
         */
        public function getActEvent(DataContainer $dc)
        {
            $return = [];
            $events = $this->Database->prepare("SELECT id,title FROM tl_calendar_events ORDER BY title")->execute();

            while ($events->next()) {
                $return[intval($events->id)] = $events->title;
            }

            return $return;
        }

        /**
         * Return all event types as array
         * @return array
         */
        public function getReservationTypes(DataContainer $dc)
        {
            $return = [];

            $objects = $this->Database->prepare("SELECT id,caption FROM tl_c4g_reservation_type WHERE `reservationObjectType` = 2 ORDER BY caption")
                ->execute();

            while ($objects->next()) {
                $return[$objects->id] = $objects->caption;
            }

            return $return;
        }

        /**
         * Return all speaker as array
         * @return array
         */
        public function getSpeakerName(DataContainer $dc)
        {
            $return = [];

            $objects = $this->Database->prepare("SELECT id,title,firstname,lastname FROM tl_c4g_reservation_event_speaker ORDER BY lastname")
                ->execute();

            while ($objects->next()) {
                $name = '';
                if ($objects->title) {
                    $name = $objects->lastname.','.$objects->firstname.','.$objects->title;
                } else {
                    $name = $objects->lastname.','.$objects->firstname;
                }

                $return[$objects->id] = $name;
            }

            return $return;
        }
    }

?>