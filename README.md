# MaxAir - Smart Thermostat

### Note: Now at Version 1.61.

The previous version, PiHome, was centered around the creation of zones, this version is more centered around devices.

Version 1.1 introduces the 'Switch' zone category. These zones uses a binary switch sensor to control a zone relay and can be used to perform Home Automation tasks.

Version 1.2 Introduces the ability the control schedules based on local Sunset and Sunrise times. The existing 'Switch' zone category has been refined and the binary switch sensor is now classed the same as the Immersion and Humidity zone types.

The new 'Switch' zone category is used when no sensor or system controller are used, examples are:
* A lamp which switches on and off at set times
* A lamp which switches on a sunset and off at some set time
* A security light which switches on at sunset and off at sunrise on the following day

Version 1.3 Introduces support for MQTT Devices, together with Home Assistant Integration.

Version 1.4 Introduces support for multi-zone HVAC systems, together with 3 timer options - HEAT, COOL and AUTO (switch between HEAT and COOL as required).

Version 1.5 Introduced the use of Pump Relays to control the useage of external pumps.

Version 1.6 Introduces Email Notifications for Sensors when they return values either above of below pre-set limits.

Version 1.61 Introduces Away Scheduling, where a single overriding schedule can be activated by the Away Option.

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
* Either the Raspbian or Armbian Operating System
* Apache Web Server
* PHP 7.x
* Python 3.x
* MySQL/MariaDB
* Adafruit Blinka

## Supported Single Board Computers
The core requirements for the SBC are as stated above, MaxAir has been successfully installed on the following SBCs :
* Raspberry Pi Zero and Raspberry Zero W 
* Raspberry Pi 3/3B
* Raspberry Pi 4
* Orange Pi Zero
* Orange Pi Zero LTS
* Orange Pi Zero Plus
* Orange Pi Zero Plus2 H5 (with eMMC on-board storage)
* Orange Pi Zero2
* Orange Pi 3 (with optional eMMC on-board storage)
* Orange Pi 3 LTS (with eMMC on-board storage)
* Rock Pi E (with optional eMMC on-board storage)
* Pine H64 Model B (with optional eMMC on-board storage)
* Bannana Pi BPI-M2 Zero
* BeagleBone Black and BeagleBone Green (with eMMC on-board storage)

## How To Install
* sudo rm -R /var/www
* sudo apt -y install git
* sudo git clone https://github.com/pihome-shc/PiHomeHVAC.git "/var/www"
* sudo chown -R www-data:www-data /var/www
* cd /var/www
* sudo php ./setup.php

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
