# MaxAir - Smart Thermostat

### Note: Now at Version 3.07.

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

Version 1.7 Introduces a revised Settings Menu option, to improve performance.

Version 1.71 Introduces an option to display a graph when either a Zone or Standalone Sensor tile is clicked. In the case of a Zone tile, buttons on the popup dialogue allow the selection of eithera 24hour or 1hour period graph display, in the case of a standalone sensor, a 24hour period graph will be displayed. The graph popup has buttons to allow swapping between 1hour and 24hour displays.

Version 1.80 Introduces a revised Home screen, which no longer auto-refreshes. The data and indicator fields on the screen are updated individually once every second. The result is that now any pop-up windows that are opened, will remain displayed until closed by the user. 

Version 2.01 Major update that introduces 'Colour Themes' and a new look based around the use of Bootstrap Version 5.2. Six different colour themes are available, Amber, Blue, Orange, Red, Teal and Violet.

Version 2.02 Introduces a simplified 'theme' definition, adds theme coloring to dropdown lists and adds Dark and Burnt Orange themes.

![theme_colours](https://user-images.githubusercontent.com/46624596/173180055-25914223-90f9-40e9-9a09-6c9854efd4d6.png)

Version 2.03 Introduces 'Cooling' type zones that work witha temperture sensor, zone relay/s and operature againt a negative temperature profile.

Version 2.04 Introduces 'Message Sensors', to allow Home Screen display of custom messages (example boiler status passed from external interface).

Version 2.05 Introduces 'eBUS Communication', to allow MaxAir to capture data from eBUS compliant Boilers.

Version 2.06 Introduces the ability to attach the Live Temperature Control to any desired zone and the ability to configure any 'Immersion' or 'Cooling'zone (zone types with no System Controller attached) to maintain the set default temperature when there is no active schedule.

Version 2.07 Introduces 'Relay ON Lag Time', to allow relays to be triggered to the ON state after a preset delay is applied to the initial trigger ON request.

Version 3.00 Major Update - main processing engine 'controller.php' replace by Python3 module 'controller.py', runs continually every 1 second to improve system respose time

Version 3.01 Update Bootstrap from Version 5.2.3 to 5.3.2 and Bootstrap Icons from Version 1.10.3 to 1.11.0

Version 3.02 Change to the operation of cascading popup screens where they now return to the previous popup, rather than returning to the originating 'Settings' menu screen. This release also contains minor bug fixes.

Version 3.03 Introduces Graph Archiving. Optionally all sensors which generate a graph can be archived to a CSV file at midnight each day, the storage path can be set to any location prefered. Additionally a new Graph category, Min/Max, has been added, it used the data from the archive file to display separate graphs for the minimum and maximum sensor readings on a daily basis.

Version 3.04 Adds support for MySensor relay sketches which use 'Heartbeat' signals ie. the WT32-ETH01 adapter where the sketch version is > 0.37 and the Zone Controller or Multi Controller where the sketch version is > 0.33. For these adapters the relay state will only be updated on change rather than being continually rewrite, additionally any unallocated relays will be restored to the OFF state by the 'Heartbeat' process.

Version 3.05 Changes to ESP32-ETH01 Gateway Controller sketch to only write updates on state change. Rollup of various bug fixes.

Version 3.06 Remove dependancy on paho-mqtt Version 1.5. Changes to Multi-Controller sketch.

Version 3.07 Adds support zones with multiple sensors, using average sensor temperature reading. Update to Bootstrap Version 3.5.7.

## Setup:

* Add Nodes (as per PiHome)
* Create Temperature Measurement devices (with a node and child id)
* Create Relay devices (with a node and child id)
* Create Zones using the temperature measurement and relay devices created previously
* Temperature Measurement devices can be displayed without the need to be allocated to a 'zombie' zone

* The Temperature Measurement and Relay devices can be created from the One Touch menu
![doc1](https://user-images.githubusercontent.com/46624596/171923125-a4895306-a295-4c14-a2dc-f2c685e3aa1e.JPG)

* New configuration GUIs are available for the Relays and Sensors devices
![doc2](https://user-images.githubusercontent.com/46624596/171923178-8066063f-4e21-4e96-8649-a37da18db888.JPG)

* Part of the reason for this version is to support HVAC systems and hence the system can be configured to work in either the existing Boiler or HVAC mode. The mode is selected from settings/system configuration menu
![doc3](https://user-images.githubusercontent.com/46624596/171923242-5b36b742-b8bb-4090-9146-935aae59c03e.JPG)

* The home screen will show the mode of operation
![doc4](https://user-images.githubusercontent.com/46624596/171923332-72295169-8899-4d93-a675-d3fea62c4713.JPG)
![doc5](https://user-images.githubusercontent.com/46624596/171923351-65b4df03-78b9-4278-a503-47125d04507d.JPG)

* The 'MODE' button will cycle through the various modes of operation, for Boiler mode this is OFF, TIMER, CE, HW or BOTH, for HVAC the modes are OFF, TIMER, AUTO, HEAT, COOL or FAN.

### The trusted boiler.php engine has been replaced by controller.php

## Requirements
Basic knowledge of command line with following main components for MaxAir to function.
* Either the Debian or Ubuntu or ArchLinux Operating System
* Apache Web Server
* PHP 7.x or higher
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
* Orange Pi 4 (with optional eMMC on-board storage)
* Orange Pi 4 LTS (with eMMC on-board storage)
* Orange Pi 5
* Rock Pi E (with optional eMMC on-board storage)
* Pine H64 Model B (with optional eMMC on-board storage)
* Bannana Pi BPI-M2 Zero
* BeagleBone Black and BeagleBone Green (with eMMC on-board storage)

## How To Install
MaxAir can be installed on both Debian/Ubuntu or ArchLinux operating systems, the pre-requisite in both instances is a functioning LAMP stack (Linux, Apache, MySQL and PHP).

To install on Debian/Ubuntu:
* sudo rm -R /var/www
* sudo apt -y install git
* sudo git clone https://github.com/pihome-shc/PiHomeHVAC.git "/var/www"
* sudo chown -R www-data:www-data /var/www
* cd /var/www/prerequisites
* sudo bash ./install.sh (accept all defaults, when asked to enter a new password for the mariadb us passw0rd)
* cd ../
* sudo php ./setup.php

To install on ArchLinux:
* sudo rm -R /srv/http
* sudo pacman -S git
* sudo git clone https://github.com/pihome-shc/PiHomeHVAC.git "/srv/http"
* sudo chown -R http:http /srv/http
* cd /srv/http/prerequisites
* sudo bash ./install.sh (accept all defaults, when asked to enter a new password for the mariadb us passw0rd)
* cd ../
* sudo php ./setup.php (setup.php will create a symbolic link /var/www to /srv/http, for compatibility)


## Secial Thanks to

* [Bootstrap](https://getbootstrap.com/ "Bootstrap ")
* [Pretty Checkbox](http://www.cssscript.com/pretty-checkbox-radio-inputs-bootstrap-awesome-bootstrap-checkbox-css "Pretty Checkbox ")
* [Box Shadow CSS](http://www.cssmatic.com/box-shadow "Box Shadow CSS")
* [AnimateCSS](https://daneden.github.io/animate.css "Animate.css ")
* [MySensors](https://www.mysensors.org "MySensors")
* [RaspberryPi Home Automation](http://pihome.harkemedia.de "RaspberryPi Home Automation")
* [All Others if forget them...](http://www.pihome.eu "All Others if forget them...")
