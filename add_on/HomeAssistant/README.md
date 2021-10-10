# Home Assistant integration
For monitoring and controlling MaxAir from Home Assistant via MQTT. This integration requires Home Assistant with the Mosquito Broker add-on running on a separete device. Please see [Home Assistant Add-on: Mosquitto broker](https://github.com/home-assistant/addons/blob/master/mosquitto/DOCS.md) for details.

## Functionalities
MaxAir will automatically broadcast over MQTT all the entity definitions needed to setup and then update the following HA entities:
* MaxAir CPU Usage - sensor
* MaxAir CPU Load (1m, 5m and 15m) - sensors
* MaxAir CPU temperature - sensor
* MaxAir Memory Use - sensor
* MaxAir Swap Usage - sensor
* MaxAir Disk Use - sensor
* MaxAir Host Ip - sensor
* MaxAir Last Boot - sensor
* MaxAir Network throughput (up & down) - sensors
* MaxAir Wifi Strength - sensor
* MaxAir updates - sensor
* Boiler or HVAC Status - binary sensor
* Climate entity for each zone with the following attributes
  * Away Status (this is the same for all zones)
  * Zone Current Mode (this is the same for all zones)
  * Zone Current Temperature (for each zone)
  * Zone Target Temperature (for each zone)
  * Zone Current Status (for each zone)
  * Zone Boost (for each zone)
  * Zone Live Temperature (for each zone)
  * Zone sensor Last Seen time and date (for each zone)
  * Zone sensor battery percentage (for each zone using a MySensor sensor)
  * Zone sensor battery voltage (for each zone using a MySensor sensor)
* Temperature sensor for each stand-alone temperature sensor in MaxAir with the following attributes
  * Sensor Current Temperature (for each zone)
  * Sensor Last Seen time and date (for each zone)
  * Sensor battery percentage (for each zone using a MySensor sensor)
  * Sensor battery voltage (for each zone using a MySensor sensor)
* Humidity sensor for each stand-alone humidity sensor in MaxAir with the following attributes
  * Sensor Current Humidity (for each zone)
  * Sensor Last Seen time and date (for each zone)
  * Sensor battery percentage (for each zone using a MySensor sensor)
  * Sensor battery voltage (for each zone using a MySensor sensor)

## Quick Start

### Home Assistant
In Home Assistant follow the steps bellow to install the Mosquito MQTT add-on:
1. Navigate in your Home Assistant frontend to Supervisor -> Add-on Store.
2. Find the "Mosquitto broker" add-on and click it.
3. Click on the "INSTALL" button.
4. Navigate in your Home Assistant frontend to Supervisor -> Mosquitto broker.
5. Click on Configuration and edit the configuration file as needed. Below is an example of a basic configuration that supports both MQTT Nodes for MaxAir and the MaxAir Home Assistant integration.

```
logins:
  - username: airmax_HA
    password: password_1
  - username: airmax
    password: password_2
customize:
  active: false
  folder: mosquitto
certfile: fullchain.pem
keyfile: privkey.pem
require_certificate: false
anonymous: false
```
6. Start the add-on. Have some patience and wait a couple of minutes.
7. Check the add-on log output to see the result.
8. Navigate in your Home Assistant frontend to Configuration -> Integrations.
9. MQTT should appear as a discovered integration at the top of the page. Select it and check the box to enable MQTT discovery, and hit submit.

### MaxAir
1. From Settings > System Configuration > MQTT add a new MQTT connection
   * Enter a name for the connection (e.g. Home Assistant)
   * Enter the IP address and the port of the MQTT broker to be used (this should be the same broker to which Home Assistant is connected)
   * Enter the unsername and password to connect to the MQTT broker
   * Select "Home Assistant Integration" as connection type. Please note you should have only one MQTT connection of this type enabled in the system.
   
   ![MQTT](https://user-images.githubusercontent.com/62815008/133248709-a2dbf4a1-ee71-47bc-bf5d-61790ba98c2d.png)
2. Execute bash install.sh this will:
   * Install the Phyton moudles needed
   * Create and enable a service for autostart
   * Start service that was created
  
Please note that this integration will search for new zones and sensors only at start up. If new sensors or zones are added to the system reboot the system or restart the integration using 'systemctl restart HA_integration.service'.

## Usage
The Home Assistant enties will be automatically created via MQTT auto discovery.

![climate](https://user-images.githubusercontent.com/62815008/136705138-898d56b7-9d5f-46ca-863e-467638d47264.png)
![sensors](https://user-images.githubusercontent.com/62815008/136705153-44a72a38-0f71-45a8-adbf-115974ab9c9f.png)

The Climate entity allows to trigger the MaxAir Boost function (Aux Heat in Home Assistant) for each zone, adjust the Live Temperature for each zone (Temperature in Home Assistant), enable or disable the MaxAir Away status (Preset in Home Assistant) and change the MaxAir Mode (Operation in Home Assistant).

![Thermostat](https://user-images.githubusercontent.com/62815008/133150409-0ec36652-9058-42ae-ab4e-dbb2b5da659d.png)

![details](https://user-images.githubusercontent.com/62815008/133150504-b083284b-7aac-4dda-bc5f-feeecf6fd2f4.png)

Unfortunately the climate enetity in Home Assistant supports only the following operations: off, auto, heat, cool, fan_only and dry. When MaxAir is operating in boiler mode the Home Assistant operations are mapped as follow:
* 0 OFF   -> off
* 1 Timer -> auto
* 2 CH    -> heat
* 3 HW    -> fan_only
* 4 Both  -> dry

When MaxAir is operating in HVAC mode the Home Assistant operations are mapped as follow:
* 0 OFF   -> off
* 1 Timer -> dry
* 2 Auto  -> auto
* 3 Fan   -> fan_only
* 4 Heat  -> heat
* 5 Cool  -> cool 
