<?php
    namespace con4gis\ReservationBundle\Classes\Callbacks;

    use Contao\DataContainer;
    use Contao\Backend;
    use Contao\BackendUser;
    use Contao\Input;
    use Contao\Database;
    use Contao\StringUtil;
    use Contao\Image;
    use Contao\Versions;

    class C4gReservationEventParticipants extends Backend
    {
        /**
         * Import the back end user object
         */
        public function __construct()
        {
            parent::__construct();
            $this->import(BackendUser::class, 'User');
        }

        public function generateUuid($varValue, DataContainer $dc)
        {
            if ($varValue == '') {
                return \c4g\projects\C4GBrickCommon::getGUID();
            }
            else {
                return $varValue;
            }
        }

        public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
        {
            $this->import(BackendUser::class, 'User');
            
            if (strlen(Input::get('tid')))
            {
                $this->toggleVisibility(Input::get('tid'), (Input::get('state') == 1));
                $this->redirect($this->getReferer());
            }
            
            $href .= '&amp;id='.$this->Input->get('id').'&amp;tid='.$row['id'].'&amp;state='.($row['cancellation'] ? '' : 1);

            if ($row['cancellation'])
            {
                $icon = 'invisible.gif';
            }

            return '<a href="'.$this->addToUrl($href).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ';

        }

        public function toggleVisibility($intId, $blnCancellation)
        {
            $objVersions = new Versions('tl_c4g_reservation_type', $intId);
            $objVersions->initialize();
        /*  // Trigger the save_callback
            if (is_array($GLOBALS['TL_DCA']['tl_c4g_reservation_event_participants']['fields']['cancellation']['save_callback']))
            {
                foreach ($GLOBALS['TL_DCA']['tl_c4g_reservation_event_participants']['fields']['cancellation']['save_callback'] as $callback)
                {
                    $this->import($callback[0]);
                    $blnCancellation = $this->$callback[0]->$callback[1](!$blnCancellation, $this);
                }
            } */

            // Update the database
            $this->Database->prepare("UPDATE tl_c4g_reservation_event_participants SET tstamp=". time() .", cancellation='" . ($blnCancellation ? '0' : '1') . "' WHERE `id`=?")
                ->execute($intId);
            $objVersions = new Versions('tl_c4g_reservation_type', $intId);
            $objVersions->create();
        }

        public function setLabel(DataContainer $dc)
        {
            $id = intval(Input::get('id'));
            
            $formularId = Database::getInstance()->prepare("SELECT formular_id FROM tl_c4g_reservation WHERE reservation_object=?")->execute($id)->fetchAssoc();
            $formularId = intval($formularId['formular_id']);
            $fieldSelect = Database::getInstance()->prepare("SELECT fieldSelection FROM tl_c4g_reservation_settings WHERE id=?")->execute($formularId)->fetchAllAssoc(); 

            $additionaldatas = StringUtil::deserialize($fieldSelect[0]['fieldSelection']);

            //Default Labels
            $firstname = $GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['firstname'][0];
            $lastname = $GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['lastname'][0];
            $email = $GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['email'][0];

            foreach ($additionaldatas as $rowdata)
            {
                $rowField = $rowdata['additionaldatas'];
                
                switch($rowField) {
                    case "salutation": $salutation = $rowdata['individualLabel'] ? $rowdata['individualLabel'] : $GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['salutation'][0];
                        break;
                    case "firstname": $firstname = $rowdata['individualLabel'] ? $rowdata['individualLabel'] : $firstname;
                        break;
                    case "lastname": $lastname = $rowdata['individualLabel'] ? $rowdata['individualLabel'] : $lastname;
                        break;
                    case "email": $email = $rowdata['individualLabel'] ? $rowdata['individualLabel'] : $email;
                        break;
                    case "dateOfBirth": $dateOfBirth = $rowdata['individualLabel'] ? $rowdata['individualLabel'] : $GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['dateOfBirth'][0];
                        break;
                    case "phone": $phone = $rowdata['individualLabel'] ? $rowdata['individualLabel'] : $GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['phone'][0];
                        break;
                    case "address": $address = $rowdata['individualLabel'] ? $rowdata['individualLabel'] : $GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['address'][0];
                        break;
                    case "postal": $postal = $rowdata['individualLabel'] ? $rowdata['individualLabel'] : $GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['postal'][0];
                        break;
                    case "city": $city = $rowdata['individualLabel'] ? $rowdata['individualLabel'] : $GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['city'][0];
                        break;
                    case "comment": $comment = $rowdata['individualLabel'] ? $rowdata['individualLabel'] : $GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['comment'][0];
                        break;
                    case "additional1": $additional1 = $rowdata['individualLabel'] ? $rowdata['individualLabel'] : $GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['additional1'][0];
                        break;
                    case "additional2": $additional2 = $rowdata['individualLabel'] ? $rowdata['individualLabel'] : $GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['additional2'][0];
                        break;
                    case "additional3": $additional3 = $rowdata['individualLabel'] ? $rowdata['individualLabel'] : $GLOBALS['TL_LANG']['tl_c4g_reservation_event_participants']['additional3'][0];
                        break;
                }
            }

            $showParticipantInfoFields = Database::getInstance()->prepare("SELECT showParticipantInfoFields FROM tl_c4g_reservation_event WHERE pid=?")->execute($id)->fetchAssoc(); 
            $additionalFields = StringUtil::deserialize($showParticipantInfoFields['showParticipantInfoFields']);

            $fields = [$lastname,$firstname];
            if (isset($additionalFields)) {
                foreach ($additionalFields as $addFields) {
                    switch($addFields) {
                        case 'dateOfBirth': array_push($fields,$dateOfBirth);
                                            $GLOBALS['TL_DCA']['tl_c4g_reservation_event_participants']['fields']['city']['search'] = true; 
                                            $GLOBALS['TL_DCA']['tl_c4g_reservation_event_participants']['fields']['city']['sorting'] = true;
                                            break;
                        case 'email':   array_push($fields,$email); 
                                        break; 
                        case 'phone':   array_push($fields,$phone); 
                                        break;
                        case 'address': array_push($fields,$address); 
                                        break;
                        case 'postal':  array_push($fields,$postal); 
                                        break;
                        case 'city':    array_push($fields,$city);
                                        $GLOBALS['TL_DCA']['tl_c4g_reservation_event_participants']['fields']['city']['search'] = true; 
                                        $GLOBALS['TL_DCA']['tl_c4g_reservation_event_participants']['fields']['city']['sorting'] = true;
                                        break;
                        case 'additional1': array_push($fields,$additional1); 
                                            $GLOBALS['TL_DCA']['tl_c4g_reservation_event_participants']['fields']['additional1']['search'] = true; 
                                            $GLOBALS['TL_DCA']['tl_c4g_reservation_event_participants']['fields']['additional1']['sorting'] = true;
                                            break;
                        case 'additional2': array_push($fields,$additional2);
                                            $GLOBALS['TL_DCA']['tl_c4g_reservation_event_participants']['fields']['additional2']['search'] = true; 
                                            $GLOBALS['TL_DCA']['tl_c4g_reservation_event_participants']['fields']['additional2']['sorting'] = true;
                                            break;
                        case 'additional3': array_push($fields,$additional3); 
                                            $GLOBALS['TL_DCA']['tl_c4g_reservation_event_participants']['fields']['additional3']['search'] = true; 
                                            $GLOBALS['TL_DCA']['tl_c4g_reservation_event_participants']['fields']['additional3']['sorting'] = true;
                                            break;
                        case 'comment':     array_push($fields,$comment); 
                                            break;
                        case 'reservation_participant_option':  array_push($fields,'participant_params');
                                                                $GLOBALS['TL_DCA']['tl_c4g_reservation_event_participants']['fields']['participant_params']['sorting'] = true; 
                                                                break;
                        case 'booker':  array_push($fields,'booker'); 
                                        break;
                    }
                }
            }
            
            $GLOBALS['TL_DCA']['tl_c4g_reservation_event_participants']['list']['label']['fields'] = $fields;
        } 

        public function listFields($arrRow)
        {
            $id = $arrRow['pid'];
            $showParticipantInfoFields = Database::getInstance()->prepare("SELECT showParticipantInfoFields FROM tl_c4g_reservation_event WHERE pid=?")->execute($id)->fetchAssoc(); 
            $additionalFields = StringUtil::deserialize($showParticipantInfoFields['showParticipantInfoFields']);

            $participantParams = StringUtil::deserialize($arrRow['participant_params']);
            if ($participantParams) {
                $i = 0;
                foreach ($participantParams as $p) {
                    $params = Database::getInstance()->prepare("SELECT caption FROM `tl_c4g_reservation_params` WHERE id=?")->execute(intval($p))->fetchAssoc();
                    $participant_params = $participant_params ? $participant_params . ", " . $params['caption'] : $params['caption']; 
                }
            }

            $GLOBALS['TL_DCA']['tl_c4g_reservation_event_participants']['fields']['lastname']['sorting'];

            $result = [$arrRow['lastname'],$arrRow['firstname']];
            if (isset($additionalFields)) {
               foreach ($additionalFields as $addFields) {
                    switch($addFields) {
                        case 'dateOfBirth': $dateOfBirth = $arrRow['dateOfBirth'] ? date('d.m.Y',$arrRow['dateOfBirth']) : "";
                                            array_push($result,$dateOfBirth); break;
                        case 'email':       array_push($result,$arrRow['email']); break;
                        case 'phone':       array_push($result,$arrRow['phone']); break;
                        case 'address':     array_push($result,$arrRow['address']); break;
                        case 'postal':      array_push($result,$arrRow['postal']); break;
                        case 'city':        array_push($result,$arrRow['city']); break;
                        case 'comment':     array_push($result,$arrRow['comment']); break;
                        case 'additional1': array_push($result,$arrRow['additional1']); break;
                        case 'additional2': array_push($result,$arrRow['additional2']); break;
                        case 'additional3': array_push($result,$arrRow['additional3']); break;                                 
                        case 'reservation_participant_option': array_push($result,$participant_params); break;
                        case 'booker': array_push($result,$arrRow['booker']); break;
                    }
                }  
            }
           
            return $result;         
        }
    }

?>