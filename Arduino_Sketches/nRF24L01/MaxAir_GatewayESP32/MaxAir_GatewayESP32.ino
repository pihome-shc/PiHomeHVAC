//           __  __                             _
//          |  \/  |                    /\     (_)
//          | \  / |   __ _  __  __    /  \     _   _ __
//          | |\/| |  / _` | \ \/ /   / /\ \   | | |  __|
//          | |  | | | (_| |  >  <   / ____ \  | | | |
//          |_|  |_|  \__,_| /_/\_\ /_/    \_\ |_| |_|
//
//                 S M A R T   T H E R M O S T A T
// *****************************************************************
// *      MaxAir MySensors WiFi Gateway Based on ESP32 Sketch      *
// *            Version 0.1 Build Date 19/04/2024                  *
// *                                          Have Fun - PiHome.eu *
// *****************************************************************

#include <vector>

//Set MY_SPLASH_SCREEN_DISABLED to disable MySensors splash screen. (This saves 120 bytes of flash)
//#define MY_SPLASH_SCREEN_DISABLED

// Enable debug prints to serial monitor
#define MY_DEBUG
//Define Sketch Version
#define SKETCH_VERSION "0.36"

//Define Sketch Name
#define SKETCH_NAME "Gateway Controller"

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
//  #define MY_RF24_IRQ_PIN 22
//  #define MY_RF24_IRQ_NUM digitalPinToInterrupt(MY_RF24_IRQ_PIN)
  //#define MY_RX_MESSAGE_BUFFER_FEATURE
  //#define MY_RX_MESSAGE_BUFFER_SIZE 5

  // We have to move CE/CSN pins for NRF radio
  #ifndef MY_RF24_CE_PIN
    #define MY_RF24_CE_PIN 22
  #endif
  #ifndef MY_RF24_CS_PIN
    #define MY_RF24_CS_PIN 5
  #endif

  // RF channel for the sensor net, 0-127
  // set by jumper on GPIO13 pin
  //#define MY_RF24_CHANNEL 91
  int8_t myChannel;
  #define MY_RF24_CHANNEL myChannel

  

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
//#define MY_DEFAULT_ERR_LED_PIN 2  // Error led uses on-board led connected to D2 pin
#define MY_DEFAULT_ERR_LED_PIN 16  // Error led pin
#define MY_DEFAULT_RX_LED_PIN  21  // Receive led pin
#define MY_DEFAULT_TX_LED_PIN  17  // the PCB, on board LED

#if defined(MY_USE_UDP)
  #include <WiFiUdp.h>
#endif

#include <WiFiClient.h>

#include <MySensors.h>
#include <DNSServer.h>
#include <WiFiManager.h>         //https://github.com/tzapu/WiFiManager
//#include <Arduino_Helpers.h>
//#include <AH/STL/algorithm>
//#include <AH/STL/iterator>

//for LED WiFi connection status
#include <Ticker.h>
Ticker ticker;

//watchdog timer
#include <esp_task_wdt.h>
//60 seconds WDT
#define WDT_TIMEOUT 60000


void tick(){
  //toggle state
  int state = digitalRead(LED_BUILTIN);  // get the current state of ERR LED pin
  digitalWrite(LED_BUILTIN, !state);     // set pin to the opposite state
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
 * This version has been modified for use with the WEMOS D1 MINI ESP32 module
 * Define the SPi pins for use with the WEMOS D1 MINI ESP32.
 */
#define MISO 19
#define MOSI 23
#define SCK 18
#define SS 5

// Define the 2 jumper pins
#define CLEAR_EEPROM 26
#define CHANNEL 13

// Enable repeater functionality for this node
//#define MY_REPEATER_FEATURE
//#define   MY_PARENT_NODE_ID 0

// Send/Recieve a heartbeat message once every 30 seconds.
long double send_heartbeat_time = millis();
long double recieve_heartbeat_time = millis();
long double watchdog_time = millis();
long double HEARTBEAT_TIME = 30000;
#define CHILD_ID_TXT 255
MyMessage msgTxt(CHILD_ID_TXT, V_TEXT);

// Structure and array to hold Node IDs and last seen times
struct node{
  uint8_t n_id;
  long double n_time;
};

node nodes[256]; // maximum number of nodes
int nodes_pos = 0; // counter for actual number of nodes connected

void before(){
  // Startup up the SPi library, with defined pins.
  #ifdef MY_DEBUG
    Serial.println("Configure SPI");
  #endif
  SPI.begin(SCK, MISO, MOSI, SS);
  #ifdef MY_DEBUG
    Serial.println("SPI Configured");
  #endif

  // Use lumper on GPIO13 to select the NRF24 Channel No (No Jumper 91, Jumper 74)
  pinMode(CHANNEL, INPUT_PULLUP);
  if (!digitalRead(CHANNEL)) {
    myChannel = 74;
  } else {
    myChannel = 91;
  }
  //myChannel = 91;
  #ifdef MY_DEBUG
    Serial.print("MY_RF24_CHANNEL: ");
    Serial.println(MY_RF24_CHANNEL);
  #endif
  //initialize the watchdog timer
  esp_task_wdt_init(WDT_TIMEOUT, true); //enable panic so ESP32 restarts
  esp_task_wdt_add(NULL); //add current thread to WDT watch
}

void setup()
{
  pinMode(LED_BUILTIN, OUTPUT);
  #ifdef MY_DEBUG
    Serial.println("START WiFiManager");
  #endif
  
  WiFi.setHostname("maxairgw");
  //start ticker with 0.5 because we start in AP mode and try to connect
  ticker.attach(0.6, tick);

  //Local intialization. Once its business is done, there is no need to keep it around
  WiFiManager wifiManager;

  //WiFihostname("MaxAir_Gateway");
  
  //reset saved settings
  pinMode(CLEAR_EEPROM, INPUT_PULLUP);
  if (!digitalRead(CLEAR_EEPROM)) {
    wifiManager.resetSettings();
  }

  //set callback that gets called when connecting to previous WiFi fails, and enters Access Point mode
  wifiManager.setAPCallback(configModeCallback);

  //set custom ip for portal
  wifiManager.setAPStaticIPConfig(IPAddress(10,0,1,1), IPAddress(10,0,1,1), IPAddress(255,255,255,0));

  //sets timeout until configuration portal gets turned off useful to make it all retry or go to sleep in seconds
  wifiManager.setTimeout(60);

  //fetches ssid and pass and tries to connect if it does not connect it starts an access point with the specified name here  "AutoConnectAP" and goes into a blocking loop awaiting configuration
  if(!wifiManager.autoConnect("MaxAir_AP")) {
    #ifdef MY_DEBUG
      Serial.println("Smart Home Gateway Failed to Connect and Hit Timeout");
    #endif
    delay(3000);
    //reset and try again, or maybe put it to deep sleep
    ESP.restart();
    delay(5000);
  }
 
  //if you get here you have connected to the WiFi
  #ifdef MY_DEBUG
    Serial.println("Smart Home Gateway Connected to your WiFi Successfully");
    Serial.println("IP address: ");
    Serial.println(WiFi.localIP());
  #endif
  
  //turn off blinking on-board led after WiFi connection and leave in the ON state
  ticker.detach();    
  digitalWrite(LED_BUILTIN, 1);     // set the LED ON

  setupWebServer();
}

void presentation(){
  // Send the sketch version information to the gateway and Controller
  sendSketchInfo(SKETCH_NAME, SKETCH_VERSION);
  #ifdef MY_DEBUG
    Serial.println("Presentation Complete");
  #endif
}

void loop()
{
  //reset the watchdog every 30 seconds if loop running, otherwise watchdof=g will reboot after 60 seconds
  long double temp = (millis() - watchdog_time);
  if (temp > WDT_TIMEOUT/2) {
    esp_task_wdt_reset();
    watchdog_time = millis();
    #ifdef MY_DEBUG
      Serial.println("WATCHDOG Reset" );
    #endif
  }
  
  temp = (millis() - send_heartbeat_time);
  if (temp > HEARTBEAT_TIME) {
    // If it exceeds the heartbeat time then send a heartbeat
    send(msgTxt.set("Heartbeat"));
    send_heartbeat_time = millis();
    #ifdef MY_DEBUG
      Serial.println("Sent heartbeat" );
    #endif
  }

  temp = (millis() - recieve_heartbeat_time);
  // Test if no hearbeat has been recieved from the gateway.py script in the last 120 seconds
  if (temp > (HEARTBEAT_TIME * 4)) {
    recieve_heartbeat_time = millis();
    Missed_Heartbeat++;
    #ifdef MY_DEBUG
      Serial.println("No heartbeat recieved" );
    #endif
  }

  server.handleClient();
}

void setupWebServer(){
  server.on("/", HTTP_GET, showRootPage);
  server.begin();
  #ifdef MY_DEBUG
    Serial.println("WebServer started...");
  #endif
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

  page+="<tr>"; page+= "<td>Wi-Fi SSID</td>"; page+= "<td>"; page += WiFi.SSID(); page+= "</td>"; page+="</tr>";
  page+="<tr>"; page+= "<td>Wi-Fi Signal</td>"; page+= "<td>"; page += WiFi.RSSI(); page+= "</td>"; page+="</tr>";
  //page+="<tr>"; page+= "<td>Hostname</td>"; page+= "<td>"; page += WiFi.hostname(); page+= "</td>"; page+="</tr>";
  page+="<tr>"; page+= "<td>Wi-Fi MAC Address</td>"; page+= "<td>"; page += WiFi.macAddress(); page+= "</td>"; page+="</tr>";
  page+="<tr>"; page+= "<td>Wi-Fi IP Address</td>"; page+= "<td>"; page += WiFi.localIP().toString(); page+= "</td>"; page+="</tr>";
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
      Serial.println("Heartbeat Recieved from Gateway Script");
      recieve_heartbeat_time = millis();
    }
  }
}
