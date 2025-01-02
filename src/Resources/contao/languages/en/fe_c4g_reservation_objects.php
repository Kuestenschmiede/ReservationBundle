<?php
/*
 * This file is part of con4gis, the gis-kit for Contao CMS.
 * @package con4gis
 * @version 10
 * @author con4gis contributors (see "authors.txt")
 * @license LGPL-3.0-or-later
 * @copyright (c) 2010-2025, by Küstenschmiede GmbH Software & Design
 * @link https://www.con4gis.org
 */

/** CONFIGURATION */
$GLOBALS['TL_LANG']['fe_c4g_reservation_objects']['brick_caption'] = 'Reservation object';
$GLOBALS['TL_LANG']['fe_c4g_reservation_objects']['brick_caption_plural'] = 'Reservation objects';

/** FIELDS */
$GLOBALS['TL_LANG']['fe_c4g_reservation_objects']['viewableTypes'] = 'Reservation type';
$GLOBALS['TL_LANG']['fe_c4g_reservation_objects']['caption'] = 'Caption';
$GLOBALS['TL_LANG']['fe_c4g_reservation_objects']['quantity'] = 'Available quantity';
$GLOBALS['TL_LANG']['fe_c4g_reservation_objects']['description'] = 'Description';
$GLOBALS['TL_LANG']['fe_c4g_reservation_objects']['image'] = 'Image';
$GLOBALS['TL_LANG']['fe_c4g_reservation_objects']['price'] = 'Contingency amount (deposit)';
$GLOBALS['TL_LANG']['fe_c4g_reservation_objects']['published'] = 'Published?';
$GLOBALS['TL_LANG']['fe_c4g_reservation_objects']['days_exclusion_text'] = ['Exclude days',
    'Please list entries comma separated. Beispiel: 01.01.2042-15.01.2042,01.05.2042,24.12.2050-26.12.2042'];

/** Legends */
$GLOBALS['TL_LANG']['fe_c4g_reservation_objects']['reservation_objects_legend'] = 'Object maintenance settings';

/** BUTTONS */
$GLOBALS['TL_LANG']['fe_c4g_reservation_objects']['buttonSaveObject'] = 'Save';

/** MESSAGES */
$GLOBALS['TL_LANG']['fe_c4g_reservation_objects']['wrong_postal'] = 'Wrong postal. No dataset possible.';
$GLOBALS['TL_LANG']['fe_c4g_reservation_objects']['fill_contact_data'] = 'Please fill in contact details (personal data) before creating.';