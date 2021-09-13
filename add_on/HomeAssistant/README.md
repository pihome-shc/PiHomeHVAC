# Home Assistant integration
For monitoring and controlling MaxAir from Home Assistant via MQTT. MaxAir will automatically broadcast over MQTT all the entity definitions needed to setup and then update the following HA entities:
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
* Boiler or HVAC Status - binary sensor
* Climate entity for each zone defined in MaxAir
  * Away Status (this is the same for all zones)
  * Zone Current Mode (this is the same for all zones)
  * Zone Current Temperature (for each zone)
  * Zone Target Temperature (for each zone)
  * Zone Current Status (for each zone)
  * Zone Boost (for each zone)
  * Zone Live Temperature (for each zone)
  * Zone sensor battery percentage (for each zone using a MySensor sensor)
  * Zone sensor battery voltage (for each zone using a MySensor sensor)

The climate entites allow to trigger the MaxAir Boost function (Aux Heat in Home Assistant) for each zone, adjust the Live Temperature for each zone, enable or disable the MaxAir Away status and change the MaxAir Mode.

## Quick Start

1. Ensure your database is updated (i.e. the *mqtt* table includes the *topic* column)
2. Execute bash install.sh this will:
   * Install the Phyton moudles needed
   * Create and enable a service for autostart
   * Start service that was created
