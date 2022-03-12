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

/**
 * This version has been modified for use with the WT32-ETH01 module.
 * 
 * define the SPi pins for use with the WT32-ETH01.
 */
#define SS 15
#define MOSI 2
#define MISO 12
#define SCK 14

// Enable debug prints to serial monitor
#define MY_DEBUG
//Define Sketch Version
#define SKETCH_VERSION "0.1"

// Enables and select radio type (if attached)
//#define MY_RADIO_NRF24
//#define MY_RADIO_RFM69
#define MY_RADIO_RFM95

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
  #define MY_RFM95_IRQ_PIN 39
  #define MY_RFM95_IRQ_NUM digitalPinToInterrupt(MY_RFM95_IRQ_PIN)
  #define MY_RFM95_CS_PIN 15
#endif

#ifdef MY_RADIO_RFM69
  #define MY_TRANSPORT_STATE_TIMEOUT_MS  (3*1000ul)
  #define MY_RFM69_FREQUENCY RFM69_868MHZ // Set your frequency here
  #define MY_IS_RFM69HW // Omit if your RFM is not "H"
  #define MY_RFM69_IRQ_PIN 39
  #define MY_RFM69_IRQ_NUM digitalPinToInterrupt(MY_RFM69_IRQ_PIN)
  #define MY_RFM69_CS_PIN 15 // NSS. Use MY_RFM69_SPI_CS for older versions (before 2.2.0)
#endif

#ifdef MY_RADIO_RFM24
  //#define MY_RF24_PA_LEVEL RF24_PA_MAX
  //#define MY_DEBUG_VERBOSE_RF24

  // RF channel for the sensor net, 0-127
  #define MY_RF24_CHANNEL 91

  //RF24_250KBPS for 250kbs, RF24_1MBPS for 1Mbps, or RF24_2MBPS for 2Mbps
  //#define MY_RF24_DATARATE RF24_250KBPS
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
IPAddress myIP(10, 0, 0, 232);
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
#define MY_DEFAULT_ERR_LED_PIN 17  // Error led pin
#define MY_DEFAULT_RX_LED_PIN  5  // Receive led pin
#define MY_DEFAULT_TX_LED_PIN  33  // the PCB, on board LED

#define ETH_ONLY 35 // set LOW using jumper to only work in WIFI mode
#define CLEAR_EEPROM 36 // set LOW using jumper to clear save WIFI credentials
#define DISABLE_ETH 32 // set LOW using jumper to disable the Ethernet Interface

#if defined(MY_USE_UDP)
  #include <WiFiUdp.h>
#endif

#include <WiFiClient.h>

#include <MySensors.h>
#include <DNSServer.h>
#include <WebServer_WT32_ETH01.h>
#include <WiFiManager.h>         //https://github.com/tzapu/WiFiManager

//for LED status
#include <Ticker.h>
Ticker ticker;

void tick(){
  //toggle state
  int state = digitalRead(5);  // get the current state of GPIO1 pin
  digitalWrite(5, !state);     // set pin to the opposite state
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

//String WebPage = "<h1>MaxAir Smart Home Gateway</h1>";
String WebPage = "<!DOCTYPE html><html lang=\"en\"><head><meta charset=\"UTF-8\" name=\"viewport\" content=\"width=device-width, initial-scale=1, user-scalable=no\"/><title>MaxAir Smart Home Gateway</title></head><body>";

void setupWebServer();
void showRootPage();
String readableTimestamp(unsigned long milliseconds);

const char* host = "maxairgw";

void setup()
{
    pinMode(ETH_ONLY, INPUT);
    pinMode(CLEAR_EEPROM, INPUT);
    pinMode(DISABLE_ETH, INPUT);
    if (digitalRead(ETH_ONLY)) {
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

    if (digitalRead(DISABLE_ETH)) {
      // Configure Ethernet
      // To be called before ETH.begin()
      WT32_ETH01_onEvent();

      //bool begin(uint8_t phy_addr=ETH_PHY_ADDR, int power=ETH_PHY_POWER, int mdc=ETH_PHY_MDC, int mdio=ETH_PHY_MDIO, 
      //           eth_phy_type_t type=ETH_PHY_TYPE, eth_clock_mode_t clk_mode=ETH_CLK_MODE);
      //ETH.begin(ETH_PHY_ADDR, ETH_PHY_POWER, ETH_PHY_MDC, ETH_PHY_MDIO, ETH_PHY_TYPE, ETH_CLK_MODE);
      ETH.begin(ETH_PHY_ADDR, ETH_PHY_POWER);

      // Static IP, leave without this line to get IP via DHCP
      //bool config(IPAddress local_ip, IPAddress gateway, IPAddress subnet, IPAddress dns1 = 0, IPAddress dns2 = 0);
      // ETH.config(myIP, myGW, mySN, myDNS);

      WT32_ETH01_waitForConnect();
    }
    setupWebServer();
}

void presentation()
{
  // Present locally attached sensors here
}

void loop()
{
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

  if (digitalRead(ETH_ONLY)) {
    page+="<tr>"; page+= "<td>Wi-Fi SSID</td>"; page+= "<td>"; page += WiFi.SSID(); page+= "</td>"; page+="</tr>";
    page+="<tr>"; page+= "<td>Wi-Fi Signal</td>"; page+= "<td>"; page += WiFi.RSSI(); page+= "</td>"; page+="</tr>";
    //page+="<tr>"; page+= "<td>Hostname</td>"; page+= "<td>"; page += WiFi.hostname(); page+= "</td>"; page+="</tr>";
    page+="<tr>"; page+= "<td>Wi-Fi MAC Address</td>"; page+= "<td>"; page += WiFi.macAddress(); page+= "</td>"; page+="</tr>";
    page+="<tr>"; page+= "<td>Wi-Fi IP Address</td>"; page+= "<td>"; page += WiFi.localIP().toString(); page+= "</td>"; page+="</tr>";
  }
  if (digitalRead(DISABLE_ETH)) {
    page+="<tr>"; page+= "<td>Ethernet MAC Address</td>"; page+= "<td>"; page += ETH.macAddress(); page+= "</td>"; page+="</tr>";
    page+="<tr>"; page+= "<td>Ethernet IP Address</td>"; page+= "<td>"; page += ETH.localIP().toString(); page+= "</td>"; page+="</tr>";
  }
  page+="<tr>"; page+= "<td>Free Memory</td>"; page+= "<td>"; page += ESP.getFreeHeap(); page+= "</td>"; page+="</tr>";

  page+="<tr>"; page+= "<td>Network Transmited Messages</td>"; page+= "<td>"; page += MsgTx; page+= "</td>"; page+="</tr>";
  page+="<tr>"; page+= "<td>Network Received Messages</td>"; page+= "<td>"; page += MsgRx; page+= "</td>"; page+="</tr>";
  page+="<tr>"; page+= "<td>Gateway Transmit Message</td>"; page+= "<td>"; page += GWMsgTx; page+= "</td>"; page+="</tr>";
  page+="<tr>"; page+= "<td>Gateway Received Message</td>"; page+= "<td>"; page += GWMsgRx; page+= "</td>"; page+="</tr>";
  page+="<tr>"; page+= "<td>Gateway Failed to Transmit Message</td>"; page+= "<td>"; page += GWErTx; page+= "</td>"; page+="</tr>";
  page+="<tr>"; page+= "<td>Gateway Protocol Version Mismatch</td>"; page+= "<td>"; page += GWErVer; page+= "</td>"; page+="</tr>";
  page+="<tr>"; page+= "<td>Gateway Transport Hardware Failure</td>"; page+= "<td>"; page += GWErTran; page+= "</td>"; page+="</tr>";
  page+="<tr>"; page+= "<td>Gateway Sketch Version</td>"; page+= "<td>"; page += SKETCH_VERSION; page+= "</td>"; page+="</tr>";

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
