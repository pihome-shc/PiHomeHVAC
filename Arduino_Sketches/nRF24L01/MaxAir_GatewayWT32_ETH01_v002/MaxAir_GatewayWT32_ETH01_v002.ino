/**
 * The MySensors Arduino library handles the wireless radio link and protocol
 * between your home built sensors/actuators and HA controller of choice.
 * The sensors forms a self healing radio network with optional repeaters. Each
 * repeater and gateway builds a routing tables in EEPROM which keeps track of the
 * network topology allowing messages to be routed to nodes.
 *
 * Created by Henrik Ekblad <henrik.ekblad@mysensors.org>
 * Copyright (C) 2013-2018 Sensnology AB
 * Full contributor list: https://github.com/mysensors/Arduino/graphs/contributors
 *
 * Documentation: http://www.mysensors.org
 * Support Forum: http://forum.mysensors.org
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * version 2 as published by the Free Software Foundation.
 *
 *******************************
 *
 * REVISION HISTORY
 * Version 1.0 - tekka
 *
 * DESCRIPTION
 * The ESP32 gateway sends data received from sensors to the WiFi link.
 * The gateway also accepts input on ethernet interface, which is then sent out to the radio network.
 * ----------- PINOUT --------------
 * | IO   | RF24 | RFM69 | RFM95 |
   |------|------|-------|-------|
   | MOSI | 2    | 2     | 2     |
   | MISO | 12   | 12    | 12    |
   | SCK  | 14   | 14    | 14    |
   | CSN  | 15   | 15    | 15    |
   | CE   | 4    | -     | -     |
   | RST  | -    | -     | -     |
   | IRQ  | 39*  | 39    | 39    |
    
 * Make sure to fill in your ssid and WiFi password below.
*/

// Enable debug prints to serial monitor
#define MY_DEBUG
//Define Sketch Version
#define SKETCH_VERSION "0.39"

// *** Comment out the following line if the sketch is being used with the original version of the PCB, or if the PCF8575 is not installd ***
#define PCF8575_ATTACHED

// *** Uncomment the following line if the sketch is used with original PCB ***
//#define PCB_VERSION_1

#ifdef PCB_VERSION_1 
  #define SKETCH_NAME "Gateway"
#else
  #define SKETCH_NAME "Gateway Controller Relay"
#endif

// Enables and select radio type (if attached)
#define MY_RADIO_RF24
//#define MY_RADIO_RFM69
//#define MY_RADIO_RFM95

#ifdef MY_RADIO_RFM95
  #define MY_TRANSPORT_STATE_TIMEOUT_MS  (3*1000ul)
  #define RFM95_RETRY_TIMEOUT_MS  (3000ul)
  #define   MY_DEBUG_VERBOSE_RFM95
  //#define   MY_DEBUG_VERBOSE_RFM95_REGISTERS
  //#define MY_RFM95_ATC_TARGET_RSSI (-70)  // target RSSI -70dBm
  //#define   MY_RFM95_MAX_POWER_LEVEL_DBM (20)   // max. TX power 10dBm = 10mW
  #define   MY_RFM95_FREQUENCY (RFM95_434MHZ)
  #define MY_RFM95_MODEM_CONFIGRUATION RFM95_BW125CR45SF128
  /* 
    #define MY_RFM95_MODEM_CONFIGRUATION RFM95_BW125CR45SF128 
    RFM95 modem configuration.
    BW = Bandwidth in kHz CR = Error correction code SF = Spreading factor, chips / symbol
    CONFIG           BW    CR    SF    Comment
    RFM95_BW125CR45SF128  125   4/5   128   Default, medium range
    RFM95_BW500CR45SF128  500   4/5   128   Fast, short range
    RFM95_BW31_25CR48SF512  31.25   4/8   512   Slow, long range
    RFM95_BW125CR48SF4096   125   4/8   4096  Slow, long range 
  */
  #define MY_RFM95_IRQ_PIN 36
  #define MY_RFM95_IRQ_NUM digitalPinToInterrupt(MY_RFM95_IRQ_PIN)
  #define MY_RFM95_CS_PIN 15
#endif

#ifdef MY_RADIO_RFM69
  #define MY_TRANSPORT_STATE_TIMEOUT_MS  (3*1000ul)
  #define MY_RFM69_FREQUENCY RFM69_868MHZ // Set your frequency here
  #define MY_IS_RFM69HW // Omit if your RFM is not "H"
  #define MY_RFM69_IRQ_PIN 36
  #define MY_RFM69_IRQ_NUM digitalPinToInterrupt(MY_RFM69_IRQ_PIN)
  #define MY_RFM69_CS_PIN 15 // NSS. Use MY_RFM69_SPI_CS for older versions (before 2.2.0)
#endif

#ifdef MY_RADIO_RF24
  #define MY_RF24_PA_LEVEL RF24_PA_HIGH
  //#define MY_DEBUG_VERBOSE_RF24
  //#define MY_RF24_IRQ_PIN 36
  //#define MY_RF24_IRQ_NUM digitalPinToInterrupt(MY_RF24_IRQ_PIN)
  //#define MY_RX_MESSAGE_BUFFER_FEATURE
  //#define MY_RX_MESSAGE_BUFFER_SIZE 5

  // We have to move CE/CSN pins for NRF radio
  #ifndef MY_RF24_CE_PIN
    #define MY_RF24_CE_PIN 4
  #endif
  #ifndef MY_RF24_CS_PIN
    #define MY_RF24_CS_PIN 15
  #endif

  // RF channel for the sensor net, 0-127
  #define MY_RF24_CHANNEL 74

  //RF24_250KBPS for 250kbs, RF24_1MBPS for 1Mbps, or RF24_2MBPS for 2Mbps
  #define MY_RF24_DATARATE RF24_250KBPS
#endif

//#define MY_ENCRYPTION_SIMPLE_PASSWD "maxair2022"

//#define MY_SIGNING_SIMPLE_PASSWD "maxair2022"

#define MY_GATEWAY_ESP32

//#define MY_WIFI_SSID "MySSID"
//#define MY_WIFI_PASSWORD "MyVerySecretPassword"

// Set the hostname for the WiFi Client. This is the hostname
// it will pass to the DHCP server if not static.
#define MY_HOSTNAME "ESP32_GW"

// Enable MY_IP_ADDRESS here if you want a static ip address (no DHCP)
//#define MY_IP_ADDRESS 10,0,0,233

// If using static ip you can define Gateway and Subnet address as well
//#define MY_IP_GATEWAY_ADDRESS 10,0,0,1
//#define MY_IP_SUBNET_ADDRESS 255,255,255,0

// The port to keep open on node server mode
#define MY_PORT 5003

// How many clients should be able to connect to this gateway (default 1)
#define MY_GATEWAY_MAX_CLIENTS 2

// Select the IP address according to your local network
IPAddress myIP(10, 0, 0, 254);
IPAddress myGW(10, 0, 0, 1);
IPAddress mySN(255, 255, 255, 0);

// Google DNS Server IP
IPAddress myDNS(8, 8, 8, 8);

#define MY_INDICATION_HANDLER

// Set blinking period
#define MY_DEFAULT_LED_BLINK_PERIOD 300

// Inverses the behavior of leds
#define MY_WITH_LEDS_BLINKING_INVERSE

// Flash leds on rx/tx/err
// Uncomment to override default HW configurations
#define MY_DEFAULT_ERR_LED_PIN 5  // Error led pin
#define MY_DEFAULT_RX_LED_PIN  17  // Receive led pin
#define MY_DEFAULT_TX_LED_PIN  33  // the PCB, on board LED

#if defined(PCB_VERSION_1)
  #define ETH_ONLY 35 // set LOW using jumper to only work in WIFI mode
  #define CLEAR_EEPROM 39 // set LOW using jumper to clear save WIFI credentials
  #define DISABLE_ETH 32 // set LOW using jumper to disable the Ethernet Interface
#else
  #define ADC_39 39 // used to determine if WiFi or Ethernet interfaces are disabled
  #define TRIGGER 35 // set LOW to indicate the relays are posotive level triggered
  #define CLEAR_EEPROM 32 // set LOW using jumper to clear save WIFI credentials (pin later used as i2c data)
#endif

#if defined(MY_USE_UDP)
  #include <WiFiUdp.h>
#endif

#include <WiFiClient.h>

#include <MySensors.h>
#include <DNSServer.h>
#include <WebServer_WT32_ETH01.h>
#include <WiFiManager.h>         //https://github.com/tzapu/WiFiManager

#if defined(PCF8575_ATTACHED)
  #define NOT_SEQUENTIAL_PINOUT
  #include "PCF8575.h"  // https://github.com/xreef/PCF8575_library
  #define SDA_0 32 //i2c data uses CFG (IO32) pin
  #define SCL_0 3  //i2c clock uses RX (IO3) pin
  TwoWire I2C_0 = TwoWire(0);

  // Set i2c address
  PCF8575 pcf8575(&I2C_0, 0x20, SDA_0, SCL_0);
  int r_mask = 0;
  char r_mask_binary[] = "0000000000000000";
#endif

//for LED status
#include <Ticker.h>
Ticker ticker;

void tick(){
  //toggle state
  int state = digitalRead(MY_DEFAULT_ERR_LED_PIN);  // get the current state of ERR LED pin
  digitalWrite(MY_DEFAULT_ERR_LED_PIN, !state);     // set pin to the opposite state
}

//gets called when WiFiManager enters configuration mode
void configModeCallback (WiFiManager *myWiFiManager) {
  Serial.println("Smart Home Gateway Entered WiFi Config Mode!!!");
  Serial.println(WiFi.softAPIP());
  //if you used auto generated SSID, print it
  Serial.println(myWiFiManager->getConfigPortalSSID());
  //entered config mode, make led toggle faster
  ticker.attach(0.2, tick);
}

WebServer server(80);
//Gateway Web Interface Stats
unsigned long startTime=millis();
unsigned long MsgTx = 0;
unsigned long MsgRx = 0;
unsigned long GWMsgTx = 0;
unsigned long GWMsgRx = 0;
unsigned long GWErTx = 0;
unsigned long GWErVer = 0;
unsigned long GWErTran = 0;
unsigned long Missed_Heartbeat = 0;

//String WebPage = "<h1>MaxAir Smart Home Gateway</h1>";
String WebPage = "<!DOCTYPE html><html lang=\"en\"><head><meta charset=\"UTF-8\" name=\"viewport\" content=\"width=device-width, initial-scale=1, user-scalable=no\"/><title>MaxAir Smart Home Gateway</title></head><body>";

void setupWebServer();
void showRootPage();
String readableTimestamp(unsigned long milliseconds);

const char* host = "maxairgw";

/**
 * This version has been modified for use with the WT32-ETH01 module
 * Define the SPi pins for use with the WT32-ETH01.
 */
#define MISO 12
#define MOSI 2
#define SCK 14
#define SS 15

#if defined(PCF8575_ATTACHED)
  #define NUMBER_OF_RELAYS 16 // Total number of attached relays
  #define RELAY_ON 1  // GPIO value to write to turn on attached relay
  #define RELAY_OFF 0 // GPIO value to write to turn off attached relay
#endif

// Enable repeater functionality for this node
//#define MY_REPEATER_FEATURE
//#define   MY_PARENT_NODE_ID 0

// Send/Recieve a heartbeat message once every 30 seconds.
long double send_heartbeat_time = millis();
long double recieve_heartbeat_time = millis();
long double HEARTBEAT_TIME = 30000;
int trigger;
char heartbeat_str[] = "Heartbeat,0,abcde";

#define CHILD_ID_TXT 255
MyMessage msgTxt(CHILD_ID_TXT, V_TEXT);

// Structure and array to hold Node IDs and last seen times
struct node{
  uint8_t n_id;
  long double n_time;
};

node nodes[256]; // maximum number of nodes
int nodes_pos = 0; // counter for actual number of nodes connected
// initialize varables, to be set depending on ADC reading
int enable_eth = 1;
int enable_wifi = 1;

// xnor function to be used to apply relay trigger setting
bool xnor(int a, int b)
{
  if (a == b) {
    return 0;
  } else {
    return 1;
  }
}

void before(){
  // Startup up the SPi library, with defined pins.
  SPI.begin(SCK, MISO, MOSI, SS);

  #if defined(PCF8575_ATTACHED)
    Serial.println("Setup 1-Wire for PCF8575 I/O expander");
    // Set Initial state to OFF
    // Set pinMode to OUTPUT for P0-P7 and P10-P17
    for(int i=0;i<NUMBER_OF_RELAYS;i++) {
      pcf8575.pinMode(i, OUTPUT);
    }
  #endif
}

void setup()
{
    //set led pin as output
    pinMode(MY_DEFAULT_ERR_LED_PIN, OUTPUT);

    pinMode(CLEAR_EEPROM, INPUT);
    #if defined(PCB_VERSION_1)
      pinMode(ETH_ONLY, INPUT);
      pinMode(DISABLE_ETH, INPUT);
      if (!digitalRead(ETH_ONLY)) {
        enable_wifi = 0;
      }
      if (!digitalRead(DISABLE_ETH)) {
        enable_eth = 0;
      }
    #else
      pinMode(TRIGGER, INPUT);
      trigger = digitalRead(TRIGGER);
  
      // read ADC on pin 39
      int adc1 = analogRead(ADC_39);
      Serial.print("ADC Reading: ");
      Serial.println(adc1);

      if (adc1 == 0) {
        enable_wifi = 0;
      }
      if (adc1 > 1000 && adc1 < 4000) {
        enable_eth = 0;
      }
    #endif
    if (enable_wifi == 1) {
      Serial.println("START WiFiManager");
      WiFi.setHostname("maxairgw");
      //start ticker with 0.5 because we start in AP mode and try to connect
      ticker.attach(0.6, tick);

      //Local intialization. Once its business is done, there is no need to keep it around
      WiFiManager wifiManager;

      //WiFihostname("MaxAir_Gateway");
      //reset saved settings
      if (!digitalRead(CLEAR_EEPROM)) {
        wifiManager.resetSettings();
      }

      //set callback that gets called when connecting to previous WiFi fails, and enters Access Point mode
      wifiManager.setAPCallback(configModeCallback);

      //set custom ip for portal
      wifiManager.setAPStaticIPConfig(IPAddress(10,0,1,1), IPAddress(10,0,1,1), IPAddress(255,255,255,0));

      //sets timeout until configuration portal gets turned off useful to make it all retry or go to sleep in seconds
      wifiManager.setTimeout(500);

      //fetches ssid and pass and tries to connect if it does not connect it starts an access point with the specified name here  "AutoConnectAP" and goes into a blocking loop awaiting configuration
      if(!wifiManager.autoConnect("MaxAir_AP")) {
        Serial.println("Smart Home Gateway Failed to Connect and Hit Timeout");
        delay(3000);
        //reset and try again, or maybe put it to deep sleep
        ESP.restart();
        delay(5000);
      }
 
      //if you get here you have connected to the WiFi
      Serial.println("Smart Home Gateway Connected to your WiFi Successfully");
      Serial.println("IP address: ");
      Serial.println(WiFi.localIP());
      ticker.detach();
    }

    if (enable_eth == 1) {
      // Configure Ethernet
      // To be called before ETH.begin()
      WT32_ETH01_onEvent();

      //bool begin(uint8_t phy_addr=ETH_PHY_ADDR, int power=ETH_PHY_POWER, int mdc=ETH_PHY_MDC, int mdio=ETH_PHY_MDIO, 
      //           eth_phy_type_t type=ETH_PHY_TYPE, eth_clock_mode_t clk_mode=ETH_CLK_MODE);
      //ETH.begin(ETH_PHY_ADDR, ETH_PHY_POWER, ETH_PHY_MDC, ETH_PHY_MDIO, ETH_PHY_TYPE, ETH_CLK_MODE);
      //ETH.begin(ETH_PHY_ADDR, ETH_PHY_POWER);
      ETH.begin();

      // Static IP, leave without this line to get IP via DHCP
      //bool config(IPAddress local_ip, IPAddress gateway, IPAddress subnet, IPAddress dns1 = 0, IPAddress dns2 = 0);
      // ETH.config(myIP, myGW, mySN, myDNS);

      WT32_ETH01_waitForConnect();
    }

    #if defined(PCF8575_ATTACHED)
      Serial.print("TRIGGER: ");
      Serial.println(trigger);
      // Initialize the PCF8575 I/O Expander Connected Relays
      pcf8575.begin();
      for(int i=0;i<NUMBER_OF_RELAYS;i++) {
        pcf8575.digitalWrite(i, xnor(trigger, RELAY_OFF));
      }
   #endif
    
    setupWebServer();
}

void presentation(){
  // Send the sketch version information to the gateway and Controller
  sendSketchInfo(SKETCH_NAME, SKETCH_VERSION);

  #if defined(PCF8575_ATTACHED)
    for (int sensor=1; sensor<=NUMBER_OF_RELAYS; sensor++) {
      // Register all sensors to gw (they will be created as child devices)
      present(sensor, S_BINARY);
      delay(6);
    }
  #endif
}

void loop()
{
  long double temp = (millis() - send_heartbeat_time);
  if (temp > HEARTBEAT_TIME) {
    String myString = String(r_mask);
    if (r_mask < 10) {
      heartbeat_str[12] = '0';
      heartbeat_str[13] = '0';
      heartbeat_str[14] = '0';
      heartbeat_str[15] = '0';
      heartbeat_str[16] = myString[0];
    } else if (r_mask >= 10 and r_mask < 100) {
      heartbeat_str[12] = '0';
      heartbeat_str[13] = '0';
      heartbeat_str[14] = '0';
      heartbeat_str[15] = myString[0];
      heartbeat_str[16] = myString[1];
    } else if (r_mask >= 100 and r_mask < 1000){
      heartbeat_str[12] = '0';
      heartbeat_str[13] = '0';
      heartbeat_str[14] = myString[0];
      heartbeat_str[15] = myString[1];
      heartbeat_str[16] = myString[2];
    } else if (r_mask >= 1000 and r_mask < 10000){
      heartbeat_str[12] = '0';
      heartbeat_str[13] = myString[0];
      heartbeat_str[14] = myString[1];
      heartbeat_str[15] = myString[2];
      heartbeat_str[16] = myString[3];
    } else {
      heartbeat_str[12] = myString[0];
      heartbeat_str[13] = myString[1];
      heartbeat_str[14] = myString[2];
      heartbeat_str[15] = myString[3];
      heartbeat_str[16] = myString[4];
    }
   // If it exceeds the heartbeat time then send a heartbeat
    send(msgTxt.set(heartbeat_str));
    send_heartbeat_time = millis();
    Serial.print("Sent Heartbeat: " );
    Serial.println(heartbeat_str);
  }

  temp = (millis() - recieve_heartbeat_time);
  if (temp > (HEARTBEAT_TIME * 2)) {
    #if defined(PCF8575_ATTACHED)
      // If it exceeds the heartbeat time then set all relays OFF
      for(int i=0;i<NUMBER_OF_RELAYS;i++) {
        pcf8575.digitalWrite(i, xnor(trigger, RELAY_OFF));
      }
    #endif
    recieve_heartbeat_time = millis();
    Serial.println("No heartbeat recieved" );
    Missed_Heartbeat++;
  }

  server.handleClient();
}

void setupWebServer(){
  server.on("/", HTTP_GET, showRootPage);
  server.begin();
  Serial.println("WebServer started...");
}

void indication( const indication_t ind ){
  switch (ind) {
    case INDICATION_TX:
    MsgTx++;
    break;

    case INDICATION_RX:
    MsgRx++;
    break;

    case INDICATION_GW_TX:
    GWMsgTx++;
    break;

    case INDICATION_GW_RX:
    GWMsgRx++;
    break;


    case INDICATION_ERR_TX:
    GWErTx++;
    break;

    case INDICATION_ERR_VERSION:
    GWErVer++;
    break;

    case INDICATION_ERR_INIT_GWTRANSPORT:
    GWErTran++;
    break;
    default:
    break;
  };
}

void showRootPage(){
  unsigned long runningTime = millis() - startTime;
  String page = WebPage;

  page+="<div style='text-align:center;display:inline-block;min-width:300px;'><h2>MaxAir Smart Home Gateway</h2><h4>General Information</h4>";
  page+="<style>body{text-align: center;font-family:verdana;font-size:1rem;} tr, td {border-bottom:1px solid #ff8839;padding:10px;text-align:left;} tr:hover {background-color: #ffede2;}</style>";
//  page+="<table style=\"width:400\">";
  page+="<table align=\"center\">";

//Message Related
  page+="<tr>"; page+= "<td>Gateway Up Time</td>"; page+= "<td>"; page += readableTimestamp(runningTime); page+= "</td>"; page+="</tr>";

  if (enable_wifi == 1) {
    page+="<tr>"; page+= "<td>Wi-Fi SSID</td>"; page+= "<td>"; page += WiFi.SSID(); page+= "</td>"; page+="</tr>";
    page+="<tr>"; page+= "<td>Wi-Fi Signal</td>"; page+= "<td>"; page += WiFi.RSSI(); page+= "</td>"; page+="</tr>";
    //page+="<tr>"; page+= "<td>Hostname</td>"; page+= "<td>"; page += WiFi.hostname(); page+= "</td>"; page+="</tr>";
    page+="<tr>"; page+= "<td>Wi-Fi MAC Address</td>"; page+= "<td>"; page += WiFi.macAddress(); page+= "</td>"; page+="</tr>";
    page+="<tr>"; page+= "<td>Wi-Fi IP Address</td>"; page+= "<td>"; page += WiFi.localIP().toString(); page+= "</td>"; page+="</tr>";
  }
  if (enable_eth == 1) {
    page+="<tr>"; page+= "<td>Ethernet MAC Address</td>"; page+= "<td>"; page += ETH.macAddress(); page+= "</td>"; page+="</tr>";
    page+="<tr>"; page+= "<td>Ethernet IP Address</td>"; page+= "<td>"; page += ETH.localIP().toString(); page+= "</td>"; page+="</tr>";
  }
  page+="<tr>"; page+= "<td>Free Memory</td>"; page+= "<td>"; page += ESP.getFreeHeap(); page+= "</td>"; page+="</tr>";

  #ifdef MY_RADIO_RF24
    page+="<tr>"; page+= "<td>Channel</td>"; page+= "<td>"; page += MY_RF24_CHANNEL; page+= "</td>"; page+="</tr>";
  #endif
  page+="<tr>"; page+= "<td>Nodes Connected in the Last 10 Minutes</td>"; page+= "<td>";
  for (byte i = 0; i < nodes_pos; i++) {
    if (millis() - nodes[i].n_time <= 600000) {
      page += nodes[i].n_id;
      page += " ";
    }
  }
  page+= "</td>"; page+="</tr>";
  page+="<tr>"; page+= "<td>Network Transmited Messages</td>"; page+= "<td>"; page += MsgTx; page+= "</td>"; page+="</tr>";
  page+="<tr>"; page+= "<td>Network Received Messages</td>"; page+= "<td>"; page += MsgRx; page+= "</td>"; page+="</tr>";
  page+="<tr>"; page+= "<td>Gateway Transmit Message</td>"; page+= "<td>"; page += GWMsgTx; page+= "</td>"; page+="</tr>";
  page+="<tr>"; page+= "<td>Gateway Received Message</td>"; page+= "<td>"; page += GWMsgRx; page+= "</td>"; page+="</tr>";
  page+="<tr>"; page+= "<td>Gateway Failed to Transmit Message</td>"; page+= "<td>"; page += GWErTx; page+= "</td>"; page+="</tr>";
  page+="<tr>"; page+= "<td>Gateway Protocol Version Mismatch</td>"; page+= "<td>"; page += GWErVer; page+= "</td>"; page+="</tr>";
  page+="<tr>"; page+= "<td>Gateway Transport Hardware Failure</td>"; page+= "<td>"; page += GWErTran; page+= "</td>"; page+="</tr>";
  page+="<tr>"; page+= "<td>Gateway Missed Heartbeat Count</td>"; page+= "<td>"; page += Missed_Heartbeat; page+= "</td>"; page+="</tr>";
  page+="<tr>"; page+= "<td>Gateway Sketch Version</td>"; page+= "<td>"; page += SKETCH_VERSION; page+= "</td>"; page+="</tr>";
  #ifdef PCF8575_ATTACHED
    page+="<tr>"; page+= "<td>Relay Mask</td>"; page+= "<td>"; page += r_mask_binary; page+= "</td>"; page+="</tr>";
  #endif

  page+="</table></div></body></html>";

  Serial.println("Smart Home Gateway Served Web Interface");
  server.send(200, "text/html", page);
}

String readableTimestamp(unsigned long milliseconds){
  int days = milliseconds / 86400000;

  milliseconds=milliseconds % 86400000;
  int hours = milliseconds / 3600000;
  milliseconds = milliseconds %3600000;

   int minutes = milliseconds / 60000;
   milliseconds = milliseconds % 60000;

   int seconds = milliseconds / 1000;
   milliseconds = milliseconds % 1000;

    String timeStamp;
    timeStamp = days; timeStamp += " days, ";
    timeStamp += hours; timeStamp += ":";
    timeStamp += minutes ; timeStamp +=  ":";
    timeStamp +=seconds; timeStamp += ".";
    timeStamp +=milliseconds;
    Serial.println(timeStamp);
    return timeStamp;
}

void sendHeartbeat()
{
}

void receive(const MyMessage &message)
{
  // Maintain a structure array for connected nodes
  bool n_found = false;
  uint8_t node_id = message.sender;
  
  if (nodes_pos > 0) { // Only check for correct position in array, if array not empty
    // Check if the Node ID already exists in the array and if found then update the time
    for (byte i = 0; i < nodes_pos; i++) {
      if (node_id == nodes[i].n_id) {
        nodes[i].n_time = millis();
        n_found = true; 
      }
    }
    if (!n_found) { // Node ID not found so need to insert at correct position in the array
      bool n_insert = false;
      for (byte i = 0; i < nodes_pos; i++) {
        if (node_id < nodes[i].n_id) {
          for (byte j = nodes_pos; j > i; j--) {
            nodes[j] = {nodes[j - 1].n_id, millis()};
          }
          nodes[i] = {node_id, millis()};
          nodes_pos++;
          n_insert = true;
          break;
        }
      }
      if (!n_insert) { // New value is larger than all existing Node Ids, so add at end of array
        nodes[nodes_pos++] = {node_id, millis()};
      }
    }
  } else {
    // Add the first Node ID to the array
    nodes[nodes_pos++] = {node_id, millis()};
  }

  #if defined(PCF8575_ATTACHED)
    // We only expect one type of message from controller. But we better check anyway.
    if (message.getType()==V_VAR2) {
      // Change relay state
      pcf8575.digitalWrite(message.getSensor()-1, message.getBool()?xnor(trigger, RELAY_OFF):xnor(trigger, RELAY_ON));
      // Update the relay mask
      if (xnor(trigger, message.getBool())) {
        bitSet(r_mask, message.getSensor()-1);
        r_mask_binary[16-message.getSensor()] = '1';
      } else {
        bitClear(r_mask, message.getSensor()-1);
        r_mask_binary[16-message.getSensor()] = '0';
      }
      // Write some debug info
      Serial.print("Incoming change for sensor:");
      Serial.print(message.getSensor());
      Serial.print(", New status: ");
      Serial.print(message.getBool());
      Serial.print(", Relay Mask: ");
      Serial.println(r_mask);
    }
    if (message.type==V_VAR1) {
      Serial.print("Node ID:    ");
      Serial.println(message.destination);
      Serial.print("Type:    "); //Message type, the number assigned
      Serial.println(message.type); // V_VAR1 is 24 zero. etc.
      Serial.print("Child:   "); // Child ID of the Sensor/Device
      Serial.println(message.sensor);
      Serial.print("Payload: "); // This is where the wheels fall off
      Serial.println(message.getString()); // This works great!
      if (message.destination==0 && message.type==24){
         recieve_heartbeat_time = millis();
      }
    }
    // Clear any unallocated relays when the Gateway script heartbeat message is returned
    if (message.type==V_VAR3) {
      Serial.print("Relay Mask: ");
      Serial.println(message.getUInt());      
      for (int pin=0; pin<NUMBER_OF_RELAYS; pin++) {
        if (bitRead(message.getUInt(), pin) == 1) {
          pcf8575.digitalWrite(pin, xnor(trigger, RELAY_OFF));
        }
      }
    }
  #endif
}
