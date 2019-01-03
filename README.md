# URI Cams Widget

Add the `[uri-cams]` shortcode to a page and a tides widget appears.

The widget pulls in live water temp and tide prediction data from NOAA and graphically displays the current position of the tide.  By default, data is pulled from the station at Quonset Point, RI, but a different station or buoy can be set if desired.

## Attributes

The cams shortcode is configurable by adding attributes to the shortcode:

**`ip`** (string)
The IP address from which to retrieve data. The default is the Bay Campus. (default: `131.128.104.45`)  

**`username`** (string)
Username to access the camera

**`password`** (string)
Password to access the camera

**`class`** (string)(optional)  
Set custom CSS class(s) (default: none)

**`alt`** (string)
The alternative text description of the image from the camera

## Plugin Details

Contributors: Brandon Fuller, John Pennypacker  
Tags: widgets  
Requires at least: 4.0  
Tested up to: 4.9  
Stable tag: 1.0
