# MaxAir - Smart Thermostat

### Note: Currently at Beta 4.5 stage.

The previous version, PiHome, was centered around the creation of zones, this version is more centered around devices.

## Setup:

* Add Nodes (as per PiHome)
* Create Temperature Measurement devices (with a node and child id)
* Create Relay devices (with a node and child id)
* Create Zones using the temperature measurement and relay devices created previously
* Temperature Measurement devices can be displayed without the need to be allocated to a 'zombie' zone

* The Temperature Measurement and Relay devices can be created from the One Touch menu
[![deploy1](https://user-images.githubusercontent.com/46624596/97433327-3a6a8880-1915-11eb-93b0-feac36159403.JPG)](https://user-images.githubusercontent.com/46624596/97433327-3a6a8880-1915-11eb-93b0-feac36159403.JPG)

* New configuration GUIs are available for the Relays and Sensors devices
[![deploy2](https://user-images.githubusercontent.com/46624596/97433533-946b4e00-1915-11eb-942b-75e2637affc8.JPG)](https://user-images.githubusercontent.com/46624596/97433533-946b4e00-1915-11eb-942b-75e2637affc8.JPG)

* Part of the reason for this version is to support HVAC systems and hence the system can be configured to work in either the existing Boiler or HVAC mode. The mode is selected from settings/system configuration menu
[![deploy3](https://user-images.githubusercontent.com/46624596/97433816-fe83f300-1915-11eb-9ae8-5b8b38f535ee.JPG)](https://user-images.githubusercontent.com/46624596/97433816-fe83f300-1915-11eb-9ae8-5b8b38f535ee.JPG)

* The home screen will show the mode of operation
[![deploy4](https://user-images.githubusercontent.com/46624596/97433953-33904580-1916-11eb-816c-0e33535a6831.JPG)](https://user-images.githubusercontent.com/46624596/97433953-33904580-1916-11eb-816c-0e33535a6831.JPG)
[![deploy5](https://user-images.githubusercontent.com/46624596/97434180-8964ed80-1916-11eb-9ccf-9962900bc8f4.JPG)](https://user-images.githubusercontent.com/46624596/97434180-8964ed80-1916-11eb-9ccf-9962900bc8f4.JPG)

* The 'MODE' button will cycle through the various modes of operation, for Boiler mode this is OFF, TIMER, CE, HW or BOTH, for HVAC the modes are OFF, TIMER, AUTO, HEAT, COOL or FAN.

### The trusted boiler.php engine has been replaced by controller.php

## Requirements
Basic knowledge of command line with following main components for MaxAir to function. 
* Apache Web Server
* PHP 7.x
* Python 3.x
* MySQL/MariaDB
* Adafruit Blinka

## How To Install
* sudo rm -R /var/www
* sudo apt -y install git
* sudo git clone https://github.com/pihome-shc/PiHomeHVAC.git "/var/www"
* sudo chown -R www-data:www-data /var/www
* sudo php /var/www/setup.php

### For more detailed instructiosn vist [PiHome](http://www.pihome.eu "PiHome - Smart Heating Control") website 


## Secial Thanks to

* [SB Admin 2 Template](http://startbootstrap.com/template-overviews/sb-admin-2 "SB Admin 2 Template ")
* [Pretty Checkbox](http://www.cssscript.com/pretty-checkbox-radio-inputs-bootstrap-awesome-bootstrap-checkbox-css "Pretty Checkbox ")
* [Font-Awesome](https://fortawesome.github.io/Font-Awesome "Font-Awesome")
* [Ionicons](http://ionicons.com "Ionicons ")
* [Box Shadow CSS](http://www.cssmatic.com/box-shadow "Box Shadow CSS")
* [AnimateCSS](https://daneden.github.io/animate.css "Animate.css ")
* [MySensors](https://www.mysensors.org "MySensors")
* [RaspberryPi Home Automation](http://pihome.harkemedia.de "RaspberryPi Home Automation")
* [All Others if forget them...](http://www.pihome.eu "All Others if forget them...")
