# con4gis-Reservation

### IMPORTANT for UPDATEs from Reservation version 2 to version 3
- Please save your settings in the reservation module before updating. Because the module settings are now in the backend module reservation form. The reason for this is among other things a relief of the tl_module.
- The price setting module does not exist anymore. The settings are now directly on the object.
- The instructor list has been further elaborated in the customer order and has its own module. Forwarding pages can be configured.
- HTML and CSS classes was changed. Individual styling may need to be adjusted.

## Overview
Contao module for creating reservation forms. For example for events, tables, rooms or other objects.

__Features include:__
* Define reservation types (for example: seminar reservation, room reservation, table reservation, ...)
* Define reservation objects (for example: table 3, room 112, ...)
* Set additional options that can be reserved
* Determine reservation costs
* Define e-mail notifications

## Installation
Via composer:
```
composer require con4gis/reservation
```
Alternatively, you can use the Contao Manager to install the con4gis-Reservation (con4gis/reservation).

## Requirements
- [Contao](https://github.com/contao/core-bundle) (latest stable release)
- [con4gis/core](https://github.com/Kuestenschmiede/CoreBundle/releases) (*latest stable release*)

## Documentation
Visit [docs.con4gis.org](https://docs.con4gis.org) for a user documentation. You can also contact us via the support forum there.
