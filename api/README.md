# APIs

## Boost State API
This API is used to set or check the BOOST on or off state for a given zone by name.
To check the current boost state call using http://pihome ip address/api/boostSet?zonename=abc where abc is the name of the zone.
To set the boost state call using http://pihome ip address/api/boostSet?zonename=abc&state=x where abc is the name of the zone and x is either true or false (alternatively 1 or 0 can be used).
The returned JSON format is:
```
    {
        "success": true,
        "state": zoneState
    }
```

## Get Sensor Temperature API
This API is used to return the temperture for a given temperature sensor by name.
To return the temperature for a temperature sensor call using http://pihome ip address/api/getTemperature?sensorname=abc where abc is the name of the temperature sensor.
The returned JSON format is:
```
    {
        "success": true,
        "state": sensorTemperature
    }
```
## Enable Rewrite
The shell script enable_rewrite.sh can be used to enable calling of the APIs without the need for the .php file extension
