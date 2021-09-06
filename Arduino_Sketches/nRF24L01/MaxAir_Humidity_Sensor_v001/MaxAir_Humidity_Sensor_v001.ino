//           __  __                             _
//          |  \/  |                    /\     (_)
//          | \  / |   __ _  __  __    /  \     _   _ __
//          | |\/| |  / _` | \ \/ /   / /\ \   | | |  __|
//          | |  | | | (_| |  >  <   / ____ \  | | | |
//          |_|  |_|  \__,_| /_/\_\ /_/    \_\ |_| |_|
//
//                 S M A R T   T H E R M O S T A T
// *****************************************************************
// *        Battery Powered I2C Si7021 Humidity Sensor             *
// *           Version 0.01 Build Date 12/04/2021                  *
// *            Last Modification Date 12/04/2021                  *
// *                                          Have Fun - PiHome.eu *
// *****************************************************************

// Enable debug prints to serial monitor
#define MY_DEBUG

//Set MY_SPLASH_SCREEN_DISABLED to disable MySensors splash screen. (This saves 120 bytes of flash)
//#define MY_SPLASH_SCREEN_DISABLED

//Define Sketch Name 
#define SKETCH_NAME "Humdity Sensor"
//Define Sketch Version 
#define SKETCH_VERSION "0.01"

// Enable and select radio type attached
#define MY_RADIO_RF24
//#define MY_RADIO_NRF5_ESB
//#define MY_RADIO_RFM69
//#define MY_RADIO_RFM95

//IRQ Pin on Arduino
#define MY_RF24_IRQ_PIN 2

//Enable OTA 
//#define MY_OTA_FIRMWARE_FEATURE

// * - RF24_PA_MIN = -18dBm
// * - RF24_PA_LOW = -12dBm
// * - RF24_PA_HIGH = -6dBm
// * - RF24_PA_MAX = 0dBm
// Set LOW transmit power level as default, if you have an amplified NRF-module and
// power your radio separately with a good regulator you can turn up PA level.
// RF24_PA_MIN RF24_PA_LOW RF24_PA_HIGH RF24_PA_MAX RF24_PA_ERROR
#define MY_RF24_PA_LEVEL RF24_PA_MIN
//#define MY_DEBUG_VERBOSE_RF24

/**
 * @brief RF channel for the sensor net, 0-125.
 * Frequencies: 2400 Mhz - 2525 Mhz
 * @see https://www.nordicsemi.com/eng/nordic/download_resource/8765/2/42877161/2726
 * - 0 => 2400 Mhz (RF24 channel 1)
 * - 1 => 2401 Mhz (RF24 channel 2)
 * - 76 => 2476 Mhz (RF24 channel 77)
 * - 83 => 2483 Mhz (RF24 channel 84)
 * - 124 => 2524 Mhz (RF24 channel 125)
 * - 125 => 2525 Mhz (RF24 channel 126)
 * In some countries there might be limitations, in Germany for example only the range
 * 2400,0 - 2483,5 Mhz is allowed.
 * @see http://www.bundesnetzagentur.de/SharedDocs/Downloads/DE/Sachgebiete/Telekommunikation/Unternehmen_Institutionen/Frequenzen/Allgemeinzuteilungen/2013_10_WLAN_2,4GHz_pdf.pdf
 */
 
//Default RF channel Default is 76
#define MY_RF24_CHANNEL  74

//PiHome - Make Sure you change Node ID, for each temperature sensor. 21 for Ground Floor, 20 for First Floor, 30 for Domastic Hot Water.
#define MY_NODE_ID 70

//RF24_250KBPS for 250kbs, RF24_1MBPS for 1Mbps, or RF24_2MBPS for 2Mbps
#define MY_RF24_DATARATE RF24_250KBPS

//Enable Signing 
//#define MY_SIGNING_SIMPLE_PASSWD "pihome"

//Enable Encryption This uses less memory, and hides the actual data.
//#define MY_ENCRYPTION_SIMPLE_PASSWD "pihome"

// Set baud rate to same as optibot
//#define MY_BAUD_RATE 9600

//set how long to wait for transport ready in milliseconds
//#define MY_TRANSPORT_WAIT_READY_MS 3000

#include <MySensors.h>  
#include <SI7021.h>
static SI7021 sensor;

#define ledpin 4      // LED for one Blink Power On, second blink for temperature sensors after successfull radio contact with gateway and three blinks for low battery 

// Define sensor node childs
#define CHILD_ID_HUM  0
#define CHILD_ID_TEMP 1
#define CHILD_ID_BATT 2

#define COMPARE_TEMP 1 // Send temperature only if changed? 1 = Yes 0 = No, > 1 - force send if it value not sent that number of times and value is valid (keep lower than notice interval)
#define COMPARE_HUM 1 //
#define COMPARE_BVOLT 1 // Send battery voltage only if changed? 1 = Yes 0 = No, > 1 - force send if it value not sent that number of times

unsigned long SLEEP_TIME = 56000; // Sleep time between reads (in milliseconds)

int batteryNotSentCount=0;

// Battery related init
int BATTERY_SENSE_PIN = A0;  // select the input pin for the battery sense point
float oldBatteryV = 0;
MyMessage msgBatt(CHILD_ID_BATT, V_VOLTAGE);
#define MIN_VALUE 80 // threshold battery level to trigger notifications
MyMessage msgBattLevel(CHILD_ID_BATT, V_VAR1);

float lastTemperature;
float lastHumidity;
bool receivedConfig = false;
bool metric = true;

// Initialize temperature and humdity messages
MyMessage msgHum( CHILD_ID_HUM,  V_HUM );
MyMessage msgTemp(CHILD_ID_TEMP, V_TEMP);

int temperatureNotSentCount;
int humidityNotSentCount;

void setup(){
  //This is LED pin set to output and turn it on for short while 
  pinMode(ledpin, OUTPUT);
  digitalWrite(ledpin, HIGH);
  delay(60);
  while (not sensor.begin())
  {
    Serial.println(F("Sensor not detected!"));
    delay(5000);
  }
  digitalWrite(ledpin, LOW);
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
  // Present sensors as children to gateway
  present(CHILD_ID_HUM, S_HUM,   "Humidity");
  present(CHILD_ID_TEMP, S_TEMP, "Temperature");

  metric = getControllerConfig().isMetric;
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
  
  int batteryPcnt = ((batteryV - 2.9) / (4.2 - 2.9) * 100); // for 18650 Battery Powred 
  //int batteryPcnt = ((batteryV - 2.1) / (3.0 - 2.1) * 100); // for 2 x AAA Battery Powered
  //int batteryPcnt = ((batteryV - 2.5) / (4.5 - 2.5) * 100); // for 3 x AAA Battery Powered
    
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

    // Read temperature & humidity from sensor.
    const float temperature = float( metric ? sensor.getCelsiusHundredths() : sensor.getFahrenheitHundredths() ) / 100.0;
    const float humidity    = float( sensor.getHumidityBasisPoints() ) / 100.0;

    #ifdef MY_DEBUG
      Serial.print(F("Temp "));
      Serial.print(temperature);
      Serial.print(metric ? 'C' : 'F');
      Serial.print(F("\tHum "));
      Serial.println(humidity);
    #endif

   // Only send data if temperature has changed and no error
    #if COMPARE_TEMP == 1
      if (lastTemperature != temperature) {
        // Send in the new temperature
        send(msgTemp.set(temperature, 2));
        // Save new temperatures for next compare
        lastTemperature=temperature;       
      }
    #elif COMPARE_TEMP == 0
      // Send in the new temperature
      send(msgTemp.set(temperature, 2));
    #else
      if (lastTemperature != temperature || temperatureNotSentCount>=COMPARE_TEMP) {
        // Send in the new temperature
        send(msgTemp.set(temperature, 2));
        // Save new temperatures for next compare
        lastTemperature=temperature; 
        //Reset values not sent count
        temperatureNotSentCount=0;      
      }else{
        lastTemperature=temperature; 
        temperatureNotSentCount++;
      }
    #endif

    // Only send data if humidity has changed and no error
    #if COMPARE_HUM == 1
      if (lastHumidity != humidity) {
        // Send in the new humidity
        send(msgHum.set(humidity, 2));
        // Save new humidity for next compare
        lastHumidity=humidity;       
      }
    #elif COMPARE_HUM == 0
      // Send in the new humidity
      send(msgHum.set(humidity, 2));
    #else
      if (lastHumidity != humidity || humidityNotSentCount>=COMPARE_HUM) {
        // Send in the new humidity
        send(msgHum.set(humidity, 2));
        // Save new humidity for next compare
        lastHumidity=humidity; 
        //Reset values not sent count
        humidityNotSentCount=0;      
      }else{
        lastHumidity=humidity; 
        humidityNotSentCount++;
      }
    #endif
  
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
