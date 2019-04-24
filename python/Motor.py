import wiringpi
import time


class Motor:
    def __init__(self, rear, front, forpin, backpin):
        # when moving backwards the rear will be pressed
        self.rear = rear
        # when moving forward front will be pressed
        self.front = front
        
        self.forpin = forpin
        self.backpin = backpin
        self.heading = None
        self.reversed = False
        self.last = None

##        reading1 = wiringpi.digitalRead(front)
##        reading2 = wiringpi.digitalRead(rear)
##
##        if reading1 is 0 and reading2 is 0:
##            #print(str(reading1)+' '+str(reading2))
##            self.reversed = True
##        elif reading1 is not reading2:
##            self.forwards()
##            time.sleep(1)
##            self.chill()
##            reading1 = wiringpi.digitalRead(front)
##            reading2 = wiringpi.digitalRead(rear)
##            if reading1 is not reading2:
##                self.backwards()
##                time.sleep(2)
##                self.chill()
##            reading1 = wiringpi.digitalRead(front)
##            reading2 = wiringpi.digitalRead(rear)
##            if reading1 is 0 and reading2 is 0:
##                #print(str(reading1)+' '+str(reading2))
##                self.reversed = True

    def forwards(self):
        self.heading = self.front
        wiringpi.digitalWrite(self.forpin, 1)
        wiringpi.digitalWrite(self.backpin, 0)

    def backwards(self):
        self.heading = self.rear
        wiringpi.digitalWrite(self.forpin, 0)
        wiringpi.digitalWrite(self.backpin, 1)

    def chill(self):
        self.heading = None
        wiringpi.digitalWrite(self.forpin, 1)
        wiringpi.digitalWrite(self.backpin, 1)

    def pressed(self):
        if self.heading:
            reading = wiringpi.digitalRead(self.heading)
        else:
            reading = None
##        print(reading)
        if reading is 0 and self.reversed is False or reading is 1 and self.reversed:
            self.last = self.heading
            self.chill()
            return True
        else:
            return False
