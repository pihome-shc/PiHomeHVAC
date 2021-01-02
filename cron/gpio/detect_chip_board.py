import board
from adafruit_platformdetect import Detector

detector = Detector()
print("Chip id: ", detector.chip.id)
print("Board id: ", detector.board.id)
