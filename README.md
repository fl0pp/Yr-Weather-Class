Yr-Weather-Class
================

A class to retrieve weather forecasts from Yr.no, and present the data in different formats.
The main goal of the Yr class is to fetch weather forecast from The Norwegian Meteorological Institute via yr.no, and present the data in different forms. Only weather forecasts from Norwegian areas is obtained.
It has a built-in cache-system, to speed up requests.

License
================

Creative Commons (CC BY-NC-SA 3.0)

Support
================

Author: Marius S. Eriksrud, marius (at) konsept-it.no

Initiate
================

$yr = new Yr($url);
$url can be several formats. To get the weather forecast for Halden in Norway, you can use all of the following formats:
* http://www.yr.no/sted/Norway/Østfold/Halden/Halden/varsel.xml
* /place/Norway/Østfold/Halden/Halden/
* http://www.yr.no/sted/Norway/Østfold/Halden/Halden

$yr = new Yr($url);

To set language on output, use the setLanguage() method.
Allowed languages: nb = Norwegian Bokmål, nn = Norwegian Nynorsk, en = English
$yr->setLanguage('nb');

To activate the cache-system, use the $options parameter when you initiate the Yr class. cache_timeout is set in seconds and is by default 1 hour/3600 seconds.
$yr = new Yr($url, array('cache_directory' => '/your/cache/directory', 'cache_timeout' => 600));

Examples
================

Get location name
----------------

Examples

Call: $yr->getName();
Output: Halden

Get location type
----------------

Examples

Call: $yr->getType();
Output: By

Get location country
----------------

Examples

Call: $yr->getCountry();
Output: Norge

Get timezone information for location
----------------

Examples

Call: $yr->getTimezone();
Output:

Array
(
    [id] => Europe/Oslo
    [utcoffsetMinutes] => 120
)

Get location information for location
----------------

Examples

Call: $yr->getLocation();
Output:

Array
(
    [altitude] => 7
    [latitude] => 59.1245978642888
    [longitude] => 11.3873828303074
    [geobase] => ssr
    [geobaseid] => 34643
)

Get sunrise time for location
----------------

Examples

Call: $yr->getSunrise();
Output: 2012-09-20 06:55:03

Call: $yr->getSunrise('d.m.Y');
Output: 20.09.2012

Get sunset time for location
----------------

Examples

Call: $yr->getSunset();
Output: 2012-09-20 19:19:05

Call: $yr->getSunset('d.m.Y');
Output: 20.09.2012

Forecast table
----------------

Get forecast table.
If you want to remove the default CSS style on the table, set first parameter to "false".
Second parameter is to override table style options. array('thead_bg' => '#f0f0f0', 'thead_color' => '#000000')
Examples

Call: $yr->getForecastTable();
Output:
I morgen, 21.09.2012 Tid	Varsel	Temp.	Nedbør	Vind
00:00 - 06:00		7 ℃	0 mm	Svak vind, 1.8 m/s fra vest-sørvest
06:00 - 12:00		2 ℃	0 mm	Flau vind, 0.5 m/s fra sør-sørøst
12:00 - 18:00		11 ℃	0 mm	Svak vind, 3.1 m/s fra Øst-nordøst
18:00 - 00:00		9 ℃	0 mm	Lett bris, 4.7 m/s fra nordøst
Lørdag, 22.09.2012 Tid	Varsel	Temp.	Nedbør	Vind
00:00 - 06:00		7 ℃	14 mm	Laber bris, 7.9 m/s fra Øst-nordøst
06:00 - 12:00		10 ℃	6 mm	Laber bris, 5.7 m/s fra Øst
12:00 - 18:00		11 ℃	1 mm	Svak vind, 2.9 m/s fra Øst
18:00 - 00:00		12 ℃	0 mm	Lett bris, 3.4 m/s fra nordøst
Søndag, 23.09.2012 Tid	Varsel	Temp.	Nedbør	Vind
00:00 - 06:00		10 ℃	0 mm	Lett bris, 4.7 m/s fra nord-nordøst
08:00 - 14:00		8 ℃	0 mm	Svak vind, 3 m/s fra nord-nordøst
14:00 - 20:00		13 ℃	0 mm	Svak vind, 2.7 m/s fra nord-nordvest
20:00 - 02:00		9 ℃	0 mm	Svak vind, 2.1 m/s fra nordøst
Mandag, 24.09.2012 Tid	Varsel	Temp.	Nedbør	Vind
02:00 - 08:00		5 ℃	0 mm	Svak vind, 2.2 m/s fra sørvest
08:00 - 14:00		3 ℃	0 mm	Svak vind, 2 m/s fra nordøst
14:00 - 20:00		13 ℃	0 mm	Svak vind, 2.4 m/s fra nord
20:00 - 02:00		9 ℃	0 mm	Svak vind, 2.1 m/s fra vest
Tirsdag, 25.09.2012 Tid	Varsel	Temp.	Nedbør	Vind
02:00 - 08:00		6 ℃	0 mm	Svak vind, 2.3 m/s fra Øst-nordøst
08:00 - 14:00		5 ℃	0 mm	Svak vind, 2.3 m/s fra Øst-nordøst
14:00 - 20:00		10 ℃	1 mm	Laber bris, 5.9 m/s fra Øst
20:00 - 02:00		9 ℃	2 mm	Lett bris, 4.8 m/s fra Øst-sørøst
Onsdag, 26.09.2012 Tid	Varsel	Temp.	Nedbør	Vind
02:00 - 08:00		9 ℃	1 mm	Lett bris, 4.8 m/s fra Øst
08:00 - 14:00		8 ℃	2 mm	Lett bris, 4.8 m/s fra Øst
14:00 - 20:00		11 ℃	1 mm	Lett bris, 5.3 m/s fra Øst
20:00 - 02:00		10 ℃	1 mm	Lett bris, 4.4 m/s fra Øst
Torsdag, 27.09.2012 Tid	Varsel	Temp.	Nedbør	Vind
02:00 - 08:00		11 ℃	2 mm	Lett bris, 4.6 m/s fra sør-sørvest
08:00 - 14:00		9 ℃	2 mm	Lett bris, 3.6 m/s fra sørvest
14:00 - 20:00		12 ℃	2 mm	Lett bris, 4.5 m/s fra Øst-sørøst
20:00 - 02:00		12 ℃	1 mm	Lett bris, 3.9 m/s fra sør-sørvest
Fredag, 28.09.2012 Tid	Varsel	Temp.	Nedbør	Vind
02:00 - 08:00		8 ℃	0 mm	Svak vind, 2.5 m/s fra nord-nordøst
08:00 - 14:00		10 ℃	0 mm	Lett bris, 3.6 m/s fra sørvest
14:00 - 20:00		13 ℃	1 mm	Lett bris, 4.7 m/s fra Øst
20:00 - 02:00		11 ℃	0 mm	Svak vind, 2.5 m/s fra sørvest
Lørdag, 29.09.2012 Tid	Varsel	Temp.	Nedbør	Vind
02:00 - 08:00		8 ℃	0 mm	Svak vind, 2.4 m/s fra vest-sørvest
08:00 - 14:00		8 ℃	0 mm	Svak vind, 2.7 m/s fra sør-sørvest
14:00 - 20:00		13 ℃	0 mm	Lett bris, 3.8 m/s fra sørvest
