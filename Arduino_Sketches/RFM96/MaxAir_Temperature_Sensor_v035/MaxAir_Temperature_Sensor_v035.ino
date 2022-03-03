//             __  __                             _
//            |  \/  |                    /\     (_)
//            | \  / |   __ _  __  __    /  \     _   _ __
//            | |\/| |  / _` | \ \/ /   / /\ \   | | |  __|
//            | |  | | | (_| |  >  <   / ____ \  | | | |
//            |_|  |_|  \__,_| /_/\_\ /_/    \_\ |_| |_|
//           
//                   S M A R T   T H E R M O S T A T
// *****************************************************************
// *       Battery Powered OneWire DS18B20 Temperature Sensor      *
// *           Version 0.35 Build Date 06/11/2017                  *
// *            Last Modification Date 03/03/2022                  *
// *                                          Have Fun - PiHome.eu *
// *****************************************************************

// Enable debug prints to serial monitor
//#define MY_DEBUG
//Set MY_SPLASH_SCREEN_DISABLED to disable MySensors splash screen. (This saves 120 bytes of flash)
#define MY_SPLASH_SCREEN_DISABLED
//Define Sketch Name
#define SKETCH_NAME "Temperature Sensor"
//Define Sketch Version
#define SKETCH_VERSION "0.35"

#define MY_RADIO_RFM95
//#define   MY_DEBUG_VERBOSE_RFM95
#define   MY_RFM95_MAX_POWER_LEVEL_DBM (20) //Set max TX power in dBm if local legislation requires this - 1mW = 0dBm, 10mW = 10dBm, 25mW = 14dBm, 100mW = 20dBm
#define   MY_RFM95_FREQUENCY (RFM95_434MHZ) //The frequency to use - RFM95_169MHZ, RFM95_315MHZ, RFM95_434MHZ, RFM95_868MHZ, RFM95_915MHZ
#define MY_RFM95_MODEM_CONFIGRUATION RFM95_BW125CR45SF128 
/* 
#define MY_RFM95_MODEM_CONFIGRUATION RFM95_BW125CR45SF128 
RFM95 modem configuration.
BW = Bandwidth in kHz CR = Error correction code SF = Spreading factor, chips / symbol
CONFIG 					BW 		CR 		SF		Comment
RFM95_BW125CR45SF128 	125 	4/5 	128 	Default, medium range
RFM95_BW500CR45SF128 	500 	4/5 	128 	Fast, short range
RFM95_BW31_25CR48SF512 	31.25 	4/8 	512 	Slow, long range
RFM95_BW125CR48SF4096 	125 	4/8 	4096 	Slow, long range 
*/
//#define MY_RFM95_TCXO // Enable to force your radio to use an external frequency source (e.g. TCXO, if present). This allows for better stability using SF 9 to 12. 
//#define MY_RFM95_TX_POWER_DBM   (13u) //Set TX power level, default 13dBm (overridden if ATC mode enabled) 
//#define MY_RFM95_ATC_TARGET_RSSI (-70)  //target RSSI -70dBm Target RSSI level (in dBm) for RFM95 ATC mode. 
#define MY_TRANSPORT_STATE_TIMEOUT_MS  (3*1000ul)
#define RFM95_RETRY_TIMEOUT_MS  (3000ul)
#define MY_RFM95_IRQ_PIN 2
#define MY_RFM95_IRQ_NUM digitalPinToInterrupt(MY_RFM95_IRQ_PIN)
#define MY_RFM95_CS_PIN 8

//MaxAir - Make Sure you change Node ID, for each temperature sensor. 21 for Ground Floor, 20 for First Floor, 30 for Domastic Hot Water.
#define MY_NODE_ID 20

//RF24_250KBPS for 250kbs, RF24_1MBPS for 1Mbps, or RF24_2MBPS for 2Mbps
//#define MY_RF24_DATARATE RF24_250KBPS

//Enable Signing
//#define MY_SIGNING_SIMPLE_PASSWD "maxair2021"

//Enable Encryption This uses less memory, and hides the actual data.
//#define MY_ENCRYPTION_SIMPLE_PASSWD "maxair2021"

// Set baud rate to same as optibot
//#define MY_BAUD_RATE 9600

//set how long to wait for transport ready in milliseconds
//#define MY_TRANSPORT_WAIT_READY_MS 3000

#include <MySensors.h>
#include <DallasTemperature.h>
#include <OneWire.h>

#define ledpin 4      // LED for one Blink Power On, second blink for temperature sensors after successfull radio contact with gateway

// Define sensor node childs
#define CHILD_ID_BATT 1
#define CHILD_ID_TEMP 0

#define COMPARE_TEMP 1 // Send temperature only if changed? 1 = Yes 0 = No, > 1 - force send if it value not sent that number of times and value is valid (keep lower than notice interval)
#define COMPARE_BVOLT 0 // Send battery voltage only if changed? 1 = Yes 0 = No, > 1 - force send if it value not sent that number of times
#define ONE_WIRE_BUS 3 // Pin where dallase sensor is connected

#define MAX_ATTACHED_DS18B20 1
unsigned long SLEEP_TIME = 56000; // Sleep time between reads (in milliseconds)

int batteryNotSentCount=0;
int temperatureNotSentCount[MAX_ATTACHED_DS18B20];

// Battery related init
int BATTERY_SENSE_PIN = A0;  // select the input pin for the battery sense point
float oldBatteryV = 0;
MyMessage msgBatt(CHILD_ID_BATT, V_VOLTAGE);
#define MIN_VALUE 80 // threshold battery level to trigger notifications
MyMessage msgBattLevel(CHILD_ID_BATT, V_VAR1);

// Dallas Temperature related init
OneWire oneWire(ONE_WIRE_BUS); // Setup a oneWire instance to communicate with any OneWire devices (not just Maxim/Dallas temperature ICs)
DallasTemperature sensors(&oneWire); // Pass the oneWire reference to Dallas Temperature.
#define TEMPERATURE_PRECISION 12 // Temperature resolution
float lastTemperature[MAX_ATTACHED_DS18B20];
int numSensors=0;
bool receivedConfig = false;
bool metric = true;
// Initialize temperature message
MyMessage msg(CHILD_ID_TEMP, V_TEMP);

void before(){
  // Startup up the OneWire library
  sensors.begin();
}

void setup(){
  //This is LED pin set to output and turn it on for short while
  pinMode(ledpin, OUTPUT);
  digitalWrite(ledpin, HIGH);
  delay(60);
  digitalWrite(ledpin, LOW);
  // requestTemperatures() will not block current thread
  sensors.setWaitForConversion(false);
  sensors.setResolution(TEMPERATURE_PRECISION);
  // needed for battery soc
  // use the 1.1 V internal reference
  #if defined(__AVR_ATmega2560__)
    analogReference(INTERNAL1V1);
  #else
    analogReference(INTERNAL);
  #endif
  // set minimum battery level threshold
  send(msgBattLevel.set(MIN_VALUE, 0));
}

void presentation() {
  // Send the sketch version information to the gateway and Controller
  sendSketchInfo(SKETCH_NAME, SKETCH_VERSION);
  // Fetch the number of attached temperature sensors
  numSensors = sensors.getDeviceCount();
  //Blink LED as number of sensors attached
  blink_led(numSensors, ledpin);

  //check if attached sensors number is grater then 0 if no then put led on solid
  #if numSensors > 0
    digitalWrite(ledpin, HIGH);
  #else
    digitalWrite(ledpin, LOW);
  #endif
  // Present all sensors to controller
  for (int i=0; i<numSensors && i<MAX_ATTACHED_DS18B20; i++) {
    present(i, S_TEMP);
  }
}

void loop(){
  // get the battery Voltage
  //ref http://www.ohmslawcalculator.com/voltage-divider-calculator
  // Sense point is bypassed with 0.1 uF cap to reduce noise at that point

  // 1M, 100K divider across battery and using internal ADC ref of 1.1V
  // ((1e6+100e3)/100e3)*1.1 = Vmax = 12.1 Volts
  // 12.1/1023 = Volts per bit = 0.011828

  //R1 820k, R2 220k
  //((820e3+220e3)/220e3)*1.1 = Vmax = 5.2 Volts
  //5.2/1023 = Volts per bit = 0.005083089

  int battSensorValue = analogRead(BATTERY_SENSE_PIN);
  //float batteryV  = battSensorValue * 0.005083089; //R1 820k, R2 220k divider across battery and using internal ADC ref of 1.1v
  float batteryV  = battSensorValue * 0.011828;    //R1 1M, R2 100K divider across battery and using internal ADC ref of 1.1v

  //int batteryPcnt = ((batteryV - 2.9) / (4.2 - 2.9) * 100); // for 18650 Battery Powred
  //int batteryPcnt = ((batteryV - 2.1) / (3.0 - 2.1) * 100); // for 2 x AAA Battery Powered
  int batteryPcnt = ((batteryV - 2.5) / (4.5 - 2.5) * 100); // for 3 x AAA Battery Powered

  #ifdef MY_DEBUG
    Serial.print("Pin Reading: ");
    Serial.println(battSensorValue);
    Serial.print("Battery Voltage: ");
    Serial.print(batteryV);
    Serial.println(" v");
    //Print Battery Percentage
    Serial.print("Battery percent: ");
    Serial.print(batteryPcnt);
    Serial.println(" %");
  #endif

  #if COMPARE_BVOLT == 1
    if (oldBatteryV != batteryV) {
      send(msgBatt.set(batteryV, 2));
      sendBatteryLevel(batteryPcnt);
      oldBatteryV = batteryV;
    }
  #elif COMPARE_BVOLT == 0
    send(msgBatt.set(batteryV, 2));
    sendBatteryLevel(batteryPcnt);
  #else
    if (oldBatteryV != batteryV || batteryNotSentCount>=COMPARE_BVOLT) {
      send(msgBatt.set(batteryV, 2));
      sendBatteryLevel(batteryPcnt);
      oldBatteryV = batteryV;
      batteryNotSentCount=0;
    }else{
      batteryNotSentCount++;
    }
  #endif

  // Fetch temperatures from Dallas sensors
  sensors.requestTemperatures();
  // query conversion time and sleep until conversion completed
  int16_t conversionTime = sensors.millisToWaitForConversion(sensors.getResolution());
  //sleep() call can be replaced by wait() call if node need to process incoming messages (or if node is repeater)
  sleep(conversionTime);
  #ifdef MY_DEBUG
    Serial.print("Conversion Time: ");
    Serial.println(conversionTime);
  #endif
  // Read temperatures and send them to controller
  for (int i=0; i<numSensors && i<MAX_ATTACHED_DS18B20; i++) {
    // Fetch and round temperature to one decimal
    float temperature = static_cast<float>(static_cast<int>((getControllerConfig().isMetric?sensors.getTempCByIndex(i):sensors.getTempFByIndex(i)) * 10.)) / 10.;

    // Only send data if temperature has changed and no error
    #if COMPARE_TEMP == 1
      if (lastTemperature[i] != temperature && temperature != -127.00 && temperature != 85.00) {
        // Send in the new temperature
        send(msg.setSensor(i).set(temperature,1));
        // Save new temperatures for next compare
        lastTemperature[i]=temperature;
      }
    #elif COMPARE_TEMP == 0
      if (temperature != -127.00 && temperature != 85.00) {
        // Send in the new temperature
        send(msg.setSensor(i).set(temperature,1));
      }
    #else
      if ((lastTemperature[i] != temperature || temperatureNotSentCount[i]>=COMPARE_TEMP) && temperature != -127.00 && temperature != 85.00) {
        // Send in the new temperature
        send(msg.setSensor(i).set(temperature,1));
        // Save new temperatures for next compare
        lastTemperature[i]=temperature;
        //Reset values not sent count
        temperatureNotSentCount[i]=0;
      }else{
        lastTemperature[i]=temperature;
        temperatureNotSentCount[i]++;
      }
    #endif
  }

  //Condition to check battery levell is lower then minimum then blink led 3 times
  //if (batteryV < 2.9) { //for 18650 Battery Powered Sensor
  //if (batteryV < 2.0) { //for 2 x AAA Battery Powered Sensor

  //if (batteryV < 2.0) { //for 3 x AAA Battery Powered Sensor
    //blink_led(3, ledpin);
    //Serial.print("Low Voltage");
  //}
  //go to sleep for while
  //smartSleep(SLEEP_TIME);
  sleep(SLEEP_TIME);
}

//Blink LED function, pass ping number and number of blinks usage: blink_led(variable or number of time blink, ledpin);
void blink_led(int count, int pin){
  for(int i=0;i<count;i++){
    digitalWrite(pin, HIGH);
    delay(700);
    digitalWrite(pin, LOW);
    delay(700);
  }
}
