/*
  Basic Code for Bi Directional Serial Communication between Arduino and Linux PC using C language and termios api
  Linux PC sends a character 'A' to Arduino using serial port
  Arduino Receives character and echoes back a reply "Character A Received OK"
  
  Please Remember to change Serial Port Name Before  Running the Code.
  (c) wwww.xanthium.in (2025)
*/
#include <fcntl.h>    /* file open flags and open() */
#include <termios.h>
#include <unistd.h>
#include <stdio.h>
#include <string.h>
#include <stdlib.h>
#include <sys/ioctl.h>
#include <sys/time.h>

#include <time.h>

void delay(int milliseconds)
{
    long pause;
    clock_t now,then;

    pause = milliseconds*(CLOCKS_PER_SEC/1000);
    now = then = clock();
    while( (now-then) < pause )
        now = clock();
}

int sendshortbreak( int fd ) {
  struct timeval duration ;

  duration.tv_sec =    0 ;
  duration.tv_usec= 1042 ;
  if ( ioctl(fd,TIOCSBRK,0) == -1 )
    return (-1) ;
  (void)select( 0, 0, 0, 0, &duration ) ;
  if ( ioctl(fd,TIOCCBRK,0) == -1 )
    return (-1) ;
  return (0) ;
}

// Function to read data from the serial port
int readFromSerialPort(int fd, char* buffer, size_t size)
{
    return read(fd, buffer, size);
}

// Function to write data to the serial port
int writeToSerialPort(int fd, const char* buffer,
                      size_t size)
{
    return write(fd, buffer, size);
}

// Function to close the serial port
void closeSerialPort(int fd) { close(fd); }

int main(int argc, char *argv[])
{
  if((argc >= 3) && (strcmp(argv[2], "r") == 0 || strcmp(argv[2], "w") == 0)) {
    char message[100];
    *message = 0;
    int i = 1;
    for(i = 1; i < argc; i++){
      strcat(message,argv[i]);
      if(i < argc - 1)
      {
        strcat(message," ");
      }
    }

    struct termios serial_port_settings;
    int fd = open("/dev/ttyAMA0", O_RDWR | O_NOCTTY); //open a connection to serialport

    if (fd == -1)
    {
      perror("Failed to open serial port"); /*  to print system error messages */
      return 1;
    }
             // Opening the port resets the Arduino
    tcgetattr(fd, &serial_port_settings); // get the serial port settings from the termios structure
    /*****************     Configure the Baudrate       *******************/
    cfsetispeed(&serial_port_settings,B115200); //Set input Baudrate
    cfsetospeed(&serial_port_settings,B115200); //Set output Baudrate
    /*****************     Configure the termios structure   ************************/

    serial_port_settings.c_lflag &= ~(ECHO | ECHOE | ISIG);
    serial_port_settings.c_iflag &= ~(IXON | IXOFF | IXANY);         // Turn OFF software based flow control (XON/XOFF).

    // Enable CANONICAL mode (the key step)
    serial_port_settings.c_lflag |= ICANON;

    serial_port_settings.c_cflag |=  CREAD | CLOCAL;         // Turn ON  the receiver of the serial port (CREAD)
    serial_port_settings.c_cflag &= ~CRTSCTS;                // Turn OFF Hardware based flow control RTS/CTS
    // Set 8N1 (8 bits, no parity, 1 stop bit)
    serial_port_settings.c_cflag &= ~PARENB;      // No parity
    serial_port_settings.c_cflag &= ~CSTOPB;      // One stop bit
    serial_port_settings.c_cflag &= ~CSIZE;
    serial_port_settings.c_cflag |=  CS8;          // 8 bits
    serial_port_settings.c_oflag &= ~OPOST;/*No Output Processing*/

    serial_port_settings.c_cc[VMIN]  = 10; /* Read at least 10 characters */
    serial_port_settings.c_cc[VTIME] = 10; /* Wait for 10 *100ms = 1 second ,measured in increments of 100ms */
    tcsetattr(fd,TCSANOW,&serial_port_settings);  // update new settings to termios structure,
                                               // TCSANOW tells to make the changes now without waiting
    /**/
    /* Flush both input and output buffers to clear out garbage values */
    if (tcflush(fd, TCIOFLUSH) != 0)
    {
       perror("tcflush");
    }

    /*********  Write characters to the Serial Port using write()***************/
    writeToSerialPort(fd, message, strlen(message));
    sendshortbreak(fd); // send the Break message
    delay(50); // give transmit time to complete 50mS

    /********* Read characters from Serial Port send by Arduino using read() ************/
    char buffer[100]; // should be big enough for longest replay eg the psheatcurve5 etc
    memset(buffer, 0, sizeof buffer); // flush the buffer, just to make sure
    int n = readFromSerialPort(fd, buffer, sizeof(buffer));
    printf(buffer);

    closeSerialPort(fd);  /* Close the file descriptor*/
  }
  else
  {
    if(argc >= 3 && argc <= 4)
    {
      printf("Agument 2 must rither 'r' or 'w'.\n");
    }
    else
    {
      printf("2 or more arguments are required.\n");
    }
  }
}
