#!/bin/bash
#service_name:MQTT_nodes_dependecies
#app_name:Install Install Dependecies for MQTT Nodes
#app_description:Install the python dependencies needed for MQTT nodes

echo "Installing Phyton modules"
REQUIREMENTS=requirements.txt
pip3 install -r $REQUIREMENTS
