#!/usr/bin/python3
# ********************************************************
# *    GPIO Pin Mapping Support for Adafruit Blinka      *
# *    Build Date: 06/02/2021 Version 0.01               *
# *    Last Modified: 02/10/2024                         *
# *                                 Have Fun - PiHome.eu *
# ********************************************************

# use Adafruit PlatformDetect to determine board type
import board

# create a pin mapping based on board type
# check if a Raspberry Pi
if board.board_id.find('RASPBERRY_PI') != -1:
    pindict = {
        "3": "D2",
        "5": "D3",
        "7": "D4",
        "8": "D14",
        "10": "D15",
        "11": "D17",
        "12": "D18",
        "13": "D27",
        "15": "D22",
        "16": "D23",
        "18": "D24",
        "19": "D10",
        "21": "D9",
        "22": "D25",
        "23": "D11",
        "24": "D8",
        "26": "D7",
        "27": "D0",
        "28": "D1",
        "29": "D5",
        "31": "D6",
        "32": "D12",
        "33": "D13",
        "35": "D19",
        "36": "D16",
        "37": "D26",
        "38": "D20",
        "40": "D21",
    }
elif board.board_id.find('PINEH64') != -1:
    pindict = {
        "3": "D2",
        "5": "D3",
        "7": "D4",
        "8": "D14",
        "10": "D15",
        "11": "D17",
        "12": "D18",
        "13": "D27",
        "15": "D22",
        "16": "D23",
        "18": "D24",
        "19": "D10",
        "21": "D9",
        "22": "D25",
        "23": "D11",
        "24": "D8",
        "26": "D7",
        "27": "D0",
        "28": "D1",
        "29": "D5",
        "31": "D6",
        "32": "D12",
        "33": "D13",
        "35": "D19",
        "36": "D16",
        "37": "D26",
        "38": "D20",
        "40": "D21",
    }
elif board.board_id.find('ORANGE_PI_ONE') != -1:
    pindict = {
        "3": "PA12",
        "5": "PA11",
        "7": "PA6",
        "8": "PA13",
        "10": "PA14",
        "11": "PA1",
        "12": "PD14",
        "13": "PA0",
        "15": "PA3",
        "16": "PC4",
        "18": "PC7",
        "19": "PC0",
        "21": "PC1",
        "22": "PA2",
        "23": "PC2",
        "24": "PC3",
        "26": "PA21",
        "27": "PA19",
        "28": "PA18",
        "29": "PA7",
        "31": "PA8",
        "32": "PG8",
        "33": "PA9",
        "35": "PA10",
        "36": "PG9",
        "37": "PA20",
        "38": "PG6",
        "40": "PG7",
    }
elif board.board_id.find('ORANGE_PI_ZERO_PLUS_2H5') != -1:
    pindict = {
        "3": "PA12",
        "5": "PA11",
        "7": "PA6",
        "8": "PA0",
        "10": "PA1",
        "11": "PL0",
        "12": "PD11",
        "13": "PL1",
        "15": "PA3",
        "16": "PA19",
        "18": "PA18",
        "19": "PA15",
        "21": "PA16",
        "22": "PA2",
        "23": "PA14",
        "24": "PA13",
        "26": "PD14",
    }
elif board.board_id.find('ORANGE_PI_ZERO_PLUS') != -1:
    pindict = {
        "3": "PA12",
        "5": "PA11",
        "7": "PA6",
        "8": "PG6",
        "10": "PG7",
        "11": "PA1",
        "12": "PA7",
        "13": "PA0",
        "15": "PA3",
        "16": "PA19",
        "18": "PA18",
        "19": "PA15",
        "21": "PA16",
        "22": "PA2",
        "23": "PA14",
        "24": "PA13",
        "26": "PA10",
    }
elif board.board_id.find('ORANGE_PI_ZERO_2') != -1:
    pindict = {
        "3": "PH5",	# start of pin mapping for 2x13 pin header
        "5": "PH4",
        "7": "PC9",
        "8": "PH2",
        "10": "PH3",
        "11": "PC6",
        "12": "PC11",
        "13": "PC5",
        "15": "PC8",
        "16": "PC15",
        "18": "PC14",
        "19": "PH7",
        "21": "PH8",
        "22": "PC7",
        "23": "PH6",
        "24": "PH9",
        "26": "PC10",
        "36": "PC1",	# start of pin mapping for 1x13 pin header
        "37": "PI16",
        "38": "PI6",
        "39": "PH10",
    }
elif board.board_id.find('ORANGE_PI_ZERO') != -1:
    pindict = {
        "3": "PA12",
        "5": "PA11",
        "7": "PA6",
        "8": "PG6",
        "10": "PG7",
        "11": "PA1",
        "12": "PA7",
        "13": "PA0",
        "15": "PA3",
        "16": "PA19",
        "18": "PA18",
        "19": "PA15",
        "21": "PA16",
        "22": "PA2",
        "23": "PA14",
        "24": "PA13",
        "26": "PA10",
    }
elif board.board_id.find('ORANGE_PI_3') != -1:
    pindict = {
        "3": "PD26",
        "5": "PD25",
        "7": "PD22",
        "8": "PL2",
        "10": "PL3",
        "11": "PD24",
        "12": "PD18",
        "13": "PD23",
        "15": "PL10",
        "16": "PD15",
        "18": "PD16",
        "19": "PH5",
        "21": "PH6",
        "22": "PD21",
        "23": "PH4",
        "24": "PH3",
        "26": "PL8",
    }
elif board.board_id.find('ORANGE_PI_4_LTS') != -1 or board.board_id.find('ORANGE_PI_5') != -1:
    pindict = {
        "3": "D3",
        "5": "D5",
        "7": "D7",
        "8": "D8",
        "10": "D10",
        "11": "D11",
        "12": "D12",
        "13": "D13",
        "15": "D15",
        "16": "D16",
        "18": "D18",
        "19": "D19",
        "21": "D21",
        "22": "D22",
        "23": "D23",
        "24": "D24",
        "26": "D26",
    }
elif board.board_id.find('BANANA_PI_M2_ZERO') != -1:
    pindict = {
        "3": "PA12",
        "5": "PA11",
        "7": "PA6",
        "8": "PA13",
        "10": "PA14",
        "11": "PA1",
        "12": "PA16",
        "13": "PA0",
        "15": "PA3",
        "16": "PA15",
        "18": "PCC",
        "19": "PC0",
        "21": "PC1",
        "22": "PA2",
        "23": "PC2",
        "24": "PC3",
        "26": "PC7",
        "27": "PA19",
        "28": "PA18",
        "29": "PA7",
        "31": "PA8",
        "32": "PL2",
        "33": "PA9",
        "35": "PA10",
        "36": "PL4",
        "37": "PA17",
        "38": "PA21",
        "40": "PA20",
    }
elif board.board_id.find('ROCK_PI_E') != -1:
    pindict = {
        "3": "D3",
        "5": "D5",
        "7": "D7",
        "8": "D8",
        "10": "D10",
        "11": "D11",
        "12": "D12",
        "13": "D13",
        "15": "D15",
        "19": "D19",
        "21": "D21",
        "23": "D23",
        "24": "D24",
        "26": "D26",
        "27": "D27",
        "28": "D28",
        "29": "D29",
        "31": "D31",
        "32": "D32",
        "33": "D33",
        "35": "D33",
        "36": "D36",
        "37": "D37",
        "38": "D38",
        "40": "D40",
    }
    # check if a BEAGLEBONE
elif board.board_id.find('BEAGLEBONE') != -1:
    # add 100 to P9 header pins so they can be referenced with an interger P9 pin number + 100
    pindict = dict()
    for x in dir(board):
       y = x.find('_')
       if x.find('P8') != -1:
           pin = {x[y + 1:]: x }
           pindict.update(pin)
       elif x.find('P9') != -1:
           pin = {str(100 + int(x[y + 1:])): x }
           pindict.update(pin)
