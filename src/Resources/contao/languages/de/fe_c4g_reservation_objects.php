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
$GLOBALS['TL_LANG']['fe_c4g_reservation_objects']['brick_caption'] = 'Reservierungsobjekt';
$GLOBALS['TL_LANG']['fe_c4g_reservation_objects']['brick_caption_plural'] = 'Reservierungsobjekte';

/** FIELDS */
$GLOBALS['TL_LANG']['fe_c4g_reservation_objects']['viewableTypes'] = 'Reservierungsart';
$GLOBALS['TL_LANG']['fe_c4g_reservation_objects']['caption'] = 'Bezeichnung';
$GLOBALS['TL_LANG']['fe_c4g_reservation_objects']['quantity'] = 'Verfügbare Menge';
$GLOBALS['TL_LANG']['fe_c4g_reservation_objects']['description'] = 'Beschreibung';
$GLOBALS['TL_LANG']['fe_c4g_reservation_objects']['image'] = 'Bild';
$GLOBALS['TL_LANG']['fe_c4g_reservation_objects']['price'] = 'Sicherheitsbetrag (Pfand)';
$GLOBALS['TL_LANG']['fe_c4g_reservation_objects']['published'] = 'Veröffentlicht?';
$GLOBALS['TL_LANG']['fe_c4g_reservation_objects']['days_exclusion_text'] = ['Tage ausschließen',
    'Bitte Einträge kommagetrennt auflisten. Beispiel: 01.01.2042-15.01.2042,01.05.2042,24.12.2042-26.12.2042'];

/** Legends */
$GLOBALS['TL_LANG']['fe_c4g_reservation_objects']['reservation_objects_legend'] = 'Einstellungen zur Objektpflege';

/** BUTTONS */
$GLOBALS['TL_LANG']['fe_c4g_reservation_objects']['buttonSaveObject'] = 'Speichern';

/** MESSAGES */
$GLOBALS['TL_LANG']['fe_c4g_reservation_objects']['wrong_postal'] = 'Falsche Postleitzahl. Keine Datensätze möglich.';
$GLOBALS['TL_LANG']['fe_c4g_reservation_objects']['fill_contact_data'] = 'Bitte vor dem Anlegen Kontaktdaten füllen (persönliche Daten).';