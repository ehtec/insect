import wiringpi
import time
import os
from Motor import Motor


class Robot:
    def __init__(self, boardcount=1, ppins=16, setoff=65):
        self.feet = []
        self.joints = []
        self.swivels = []
        self.inputs = []
        self.outputs = []
        self.inpairs = []
        self.outpairs = []
        self.motopins = []

        wiringpi.wiringPiSetup()
        for board in range(boardcount):
            gpin = setoff + ppins * board
            wiringpi.mcp23017Setup(gpin, 0x20 + board)
            for ppin in range(ppins):
                pin = ppin + gpin
                wiringpi.digitalWrite(pin, 0)
                if ppin < ppins / 2:
                    wiringpi.pinMode(pin, 0)
                    wiringpi.pullUpDnControl(pin, 2)
                    self.inputs.append(pin)
                if ppin >= ppins / 2:
                    wiringpi.digitalWrite(pin, 1)
                    wiringpi.pinMode(pin, 1)
                    self.outputs.append(pin)

        for x in zip(*[iter(self.inputs)]*8):
            rev = x[::-1]
            for tup in zip(*[iter(rev)] * 2):
##                print(tup)
                self.inpairs.append(tup)
            

##            pair0 = (f,e)
##            pair1 = (f,e)
##            pair2 = (f,e)
##            pair3 = (f,e)
##            self.inpairs.append(
##            print('{0} {1} {2} {3} {4} {5} {6} {7}'.format(a,b,c,d,e,f,g,h))

            
##        for x, y in zip(*[iter(self.inputs)] * 2):
##            tup = (x, y)
##            self.inpairs.append(tup)
        for x, y in zip(*[iter(self.outputs)] * 2):
            tup = (x, y)
            self.outpairs.append(tup)

        for inpair, outpair in zip(self.inpairs, self.outpairs):
            # print(inpair + outpair)
            self.motopins.append(inpair + outpair)
            
    def motorassignment(self, feet, swivels, joints):
        for motop, num in zip(self.motopins, range(len(self.motopins))):
            # print(num)
            if num < feet:
                # print('feet')
                # self.feet.append(motop)
                self.feet.append(Motor(motop[0], motop[1], motop[2], motop[3]))
            elif feet <= num < feet + swivels:
                # print('swivels')
                # self.swivels.append(motop)
                mot = Motor(motop[0], motop[1], motop[2], motop[3])
                mot.reversed = True
                self.swivels.append(mot)
            elif feet + swivels <= num < feet + swivels + joints:
                # print('joints')
                # self.joints.append(motop)
                self.joints.append(Motor(motop[0], motop[1], motop[2], motop[3]))

    def forwards(self):
        ripdone = []
        feet = len(self.feet)
        swivels = len(self.swivels)
        for swivel, num in zip(self.swivels, range(swivels)):
##            if num % 2 is 0:
              swivel.forwards()
##            elif num % 2 is 1:
##                swivel.backwards()

        while len(ripdone) < swivels:
##            self.status()
            for swivel in self.swivels:
                if swivel.heading:
                    swivel.pressed()
                    if swivel.heading is None:
                        ripdone.append(1)
            #print(len(ripdone))
        ripdone.clear()

        for even, odd in zip(self.feet[::2], self.feet[1::2]):
            even.forwards()
            odd.backwards()

        while len(ripdone) < feet:
##            self.status()
            for foot in self.feet:
                if foot.heading:
                    foot.pressed()
                    if foot.heading is None:
                        ripdone.append(1)
            #print(len(ripdone))
        ripdone.clear()

        for swivel, num in zip(self.swivels, range(swivels)):
##            if num % 2 is 0:
              swivel.backwards()
##            elif num % 2 is 1:
##                swivel.forwards()

        while len(ripdone) < swivels:
##            self.status()
            for swivel in self.swivels:
                if swivel.heading:
                    swivel.pressed()
                    if swivel.heading is None:
                        ripdone.append(1)
            #print(len(ripdone))
        ripdone.clear()

        for even, odd in zip(self.feet[::2], self.feet[1::2]):
            even.backwards()
            odd.forwards()

        while len(ripdone) < feet:
##            self.status()
            for foot in self.feet:
                if foot.heading:
                    foot.pressed()
                    if foot.heading is None:
                        ripdone.append(1)
            #print(len(ripdone))
        ripdone.clear()

    def walk(self):
        while os.environ['walk']:
            self.forward()

    def runtime(self):
        while os.environ['walk']:
            self.forward()
        while os.environ['stop']:
            self.stop()

    def stop(self):
        for foot in self.feet:
            if foot.heading:
                foot.chill()
                # print(foot.heading)
            else:
                if foot.last is foot.front:
                    foot.backwards()
                    # print(foot.heading)
                    time.sleep(1)
                    foot.chill()
                elif foot.last is foot.rear:
                    foot.forwards()
                    # print(foot.heading)
                    time.sleep(1)
                    foot.chill()
        for swivel in self.swivels:
            if swivel.heading:
                swivel.chill()
            else:
                if swivel.last is swivel.front:
                    swivel.backwards()
                    time.sleep(1)
                    swivel.chill()
                elif swivel.last is swivel.rear:
                    swivel.forwards()
                    time.sleep(1)
                    swivel.chill()

    def status(self):
        for foot in self.feet:
            read1 = wiringpi.digitalRead(foot.front)
            read2 = wiringpi.digitalRead(foot.rear)
            read3 = wiringpi.digitalRead(foot.forpin)
            read4 = wiringpi.digitalRead(foot.backpin)
            print(
                str(foot.front) + ' ' + str(read1) + ' ' + str(foot.rear) + ' ' + str(read2) + '  ' +
                str(foot.forpin) + ' ' + str(read3) + ' ' + str(foot.backpin) + ' ' + str(read4) + '\t', end=' ')

        for foot in self.feet:
            print(str(foot.heading), end='')

        print('\t', end='')

        for swivel in self.swivels:
            read1 = wiringpi.digitalRead(swivel.front)
            read2 = wiringpi.digitalRead(swivel.rear)
            read3 = wiringpi.digitalRead(swivel.forpin)
            read4 = wiringpi.digitalRead(swivel.backpin)
            print(
                str(swivel.front) + ' ' + str(read1) + ' ' + str(swivel.rear) + ' ' + str(read2) + '  ' +
                str(swivel.forpin) + ' ' + str(read3) + ' ' + str(swivel.backpin) + ' ' + str(read4) + '\t', end=' ')
        for swivel in self.swivels:
            print(str(swivel.heading), end='')

        print('')
