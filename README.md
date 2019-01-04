# URI Cams Widget

Add the `[uri-cams]` shortcode to a page and a tides widget appears.

The widget pulls in still images from the quad cams and saves them in the uploads directory.  It does not add them to the Media Liberry.  It refreshes the images every 15 minutes or so. 

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

**`link`** (boolean)
If set to true, the image element will be a link to the image (for lightbox or what not)

## Plugin Details

Contributors: Brandon Fuller, John Pennypacker  
Tags: widgets  
Requires at least: 4.0  
Tested up to: 4.9  
Stable tag: 1.0
