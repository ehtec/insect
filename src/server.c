#include <arpa/inet.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <sys/types.h>
#include <sys/socket.h>
#include <sys/un.h>
#include <unistd.h>
#include <signal.h>
#include <iostream>
#include <thread>
#include <list>
#include <iterator>
#include <bits/stdc++.h>
#include <boost/algorithm/string.hpp>
#include <sys/stat.h>
#include <gst/gst.h>
#include <math.h>
//#include <wiringPi.h>
//#include <mcp23017.h>
//#include "./MotorGroup.h"
#include <vector>
#include <numeric>
#include <algorithm>
#include <functional>
#include <tuple>
#include <map>
#include <random>
#include <glib.h>
#include <Python.h>

using namespace std;
using std::vector;

#define SOCKET_FILENAME "/tmp/server.sock"
#define AUDIO_PATH "/var/www/html/uploads/"
#define RPI_CAM_WEB_INTERFACE_PATH "/home/pi/RPi_Cam_Web_Interface/"
#define SHUTDOWN_CMD "sudo shutdown 1"
#define REBOOT_CMD "sudo shutdown -r 1"
#define MAXVOL 1.2

/*

compile with:

g++ -Wall server.c -o server.out -pthread -lpython3.5m -I/usr/include/python3.5m $(pkg-config --cflags --libs gstreamer-1.0)

*/

int server;

//stop threads when goexit is true
bool goexit = false;

//audiofilename
char thefilestr[200];

//feedback level
//=0 only when langctrl
//>0 always
//<0 never
int feedbacklevel = 0;

//original values for feedbacklevel and langctrl
int feedbacklevelorig = 0;
bool langctrlorig = false;

//check if anything playing
bool isplaying = false;
bool stopplaying = false;

//language control bool
bool langctrl = false;

//motorcontrol
bool revertnorm = FALSE;
bool revertrev = true;
bool isgoing = false;
//bool stopgoing = false;

//commandlist
std::list<string> cmdList;

//command parameter will be pasted here
std::string thevalue;

//define pocketsphinx result string
std::string pockres;

//define valid commands for speech recognition
//vector<string> validCommands = { "play", "sing", "speak"  };
vector<string> validCommands = { "play", "sing", "forward", "stop" };

//gstreamer elements for audio player
GstElement *pipeline;
GstBus *bus;
GstMessage *msg;

//gstreamer elements for microphone and pocketsphinx
GMainLoop *loopx;
GstElement *pipelinex, *sourcex, *decoderx, *sinkx;
GstBus *busx;
guint bus_watch_id;

inline bool file_exists(const std::string& name) {
  struct stat buffer;
  return (stat(name.c_str(), &buffer) == 0);
}

inline bool is_command(const std::string& y) {
  return(std::find(::validCommands.begin(), ::validCommands.end(), y) != ::validCommands.end());
}

void espeak(char speakstr[100]) {
  bool langctrlorig2;
  //turn off language control
  langctrlorig2 = ::langctrl;
  ::langctrl = false;
  char speakcmdstr[100];
  strcpy(speakcmdstr, "espeak \"");
  strcat(speakcmdstr, speakstr);
  strcat(speakcmdstr, "\"");
  std::cout<<"Speaking: "<<speakstr<<"\n";
  system(speakcmdstr);
  puts("Spoken.");
  ::langctrl = langctrlorig2;
}

void speakfeedback(const char addstr[100]) {
  vector<std::string> phrases { "Aye aye sir.", "Of course sir." , "You are the boss.", "Certainly, sir." , "Sure."};
  std::random_device seed;
  std::mt19937 engine(seed());
  std::uniform_int_distribution<int> choose(0, phrases.size() - 1);
  std::string elem = phrases[choose(engine)];
  std::cout<<"Random element picked: "<<elem<<"\n";
  char thespeakstr[300];
  strcpy(thespeakstr, elem.c_str());
  strcat(thespeakstr, " ");
  strcat(thespeakstr, addstr);
  espeak(thespeakstr);
}

void gstfreerc() {
  if (::isplaying) {
    ::stopplaying=true;
    sleep(3);
  }
  sleep(1);
  puts("Freeing msg.");
  if (msg != NULL)
    gst_message_unref(msg);
  puts("Freeing bus.");
  if (bus != NULL)
    gst_object_unref(bus);
  puts("Setting pipeline null.");
  pipeline=NULL;
  if (pipeline != NULL) {
    gst_element_set_state(pipeline, GST_STATE_NULL);
    //puts("Setting pipline null.");
    //pipeline = NULL;
    puts("Freeing pipeline.");
   // pipeline = NULL;
    gst_object_unref(pipeline);
  }
  puts("Freed pipeline.");
  //clean up langctrl pipeline
  puts("Returned, stopping playback.");
  /*puts("Setting language control pipeling null.");
  if (pipelinex != NULL) {
    gst_element_set_state(pipelinex, GST_STATE_NULL);
  }
  puts("Deleting pipeline.");
  if (pipelinex != NULL) {
    gst_object_unref(GST_OBJECT(pipelinex));
  }
  if (bus_watch_id != NULL) {
    g_source_remove(bus_watch_id);
  }
  if (loopx != NULL) {
    g_main_loop_unref(loopx);
  }*/
}

void gstwait() {
  bus = gst_element_get_bus (pipeline);
  msg = gst_bus_timed_pop_filtered (bus, GST_CLOCK_TIME_NONE, (GstMessageType)(GST_MESSAGE_ERROR | GST_MESSAGE_EOS));
}

/*std::vector <std::vector<MotorGroup>>
concat(vector <MotorGroup> mtg0, vector <MotorGroup> mtg1, vector <MotorGroup> mtg2, vector <MotorGroup> mtg3) {
    auto size1 = mtg0.size();
    auto size2 = mtg1.size();
    auto size3 = mtg2.size();
    auto size4 = mtg3.size();
    vector <MotorGroup> rever;
    vector <MotorGroup> normal;
    for (std::size_t i = 0; i < size1; i++) {
        MotorGroup &mot = mtg0[i];
        if (mot.reversed) {
            rever.push_back(mot);
        } else {
            normal.push_back(mot);
        }
    }
    for (std::size_t i = 0; i < size2; i++) {
        MotorGroup &mot = mtg1[i];
        if (mot.reversed) {
            rever.push_back(mot);
        } else {
            normal.push_back(mot);
        }
    }
    for (std::size_t i = 0; i < size3; i++) {
        MotorGroup &mot = mtg2[i];
        if (mot.reversed) {
            rever.push_back(mot);
        } else {
            normal.push_back(mot);
        }
    }
    for (std::size_t i = 0; i < size4; i++) {
        MotorGroup &mot = mtg3[i];
        if (mot.reversed) {
            rever.push_back(mot);
        } else {
            normal.push_back(mot);
        }
    }
    std::vector <std::vector<MotorGroup>> sorted;
    sorted.push_back(normal);
    sorted.push_back(rever);
    return sorted;
}


std::map<int, bool> listen(vector <MotorGroup> &mtg, map <int,bool> dictlist, bool walk = FALSE) {
    auto size = mtg.size();

    for (std::size_t i = 0; i < size; i++) {
        MotorGroup &mot = mtg[i];
        if (dictlist.find(i) == dictlist.end()){
            } else {
            continue;
            }
        if(mot.reversed){
            int last = mot.last;
            mot.pressed(mot.BTN1);
            mot.pressed(mot.BTN2);
            if (last != mot.last){
            dictlist[i] = TRUE;
            mot.chill();}
            }else{
        if (mot.last == mot.BTN2){
            if(mot.pressed(mot.BTN1)){
                dictlist[i] = TRUE;
                mot.chill();
            }
        } else if (mot.last == mot.BTN1){
            if(mot.pressed(mot.BTN2)){
                dictlist[i] = TRUE;
                mot.chill();
            }
        }}

        if (mot.last == mot.BTN1) {
            if (revertnorm) {
                if (!mot.reversed) {
                    mot.forward();
                    mot.revert();
                };
            };
            if (revertrev) {
                if (mot.reversed) {
                    mot.backward();
                    mot.revert();
                };
            };
        }
        if(mot.last == mot.BTN2) {

            if (revertnorm) {
                if (!mot.reversed) {
                    mot.backward();
                    mot.revert();
                };
            };
            if (revertrev) {
                if (mot.reversed) {
                    mot.forward();
                    mot.revert();
                };
            };
        }
        if(i<6){
        printf(" ");
        std::cout << mot.last;
        printf(" ");

        //~ //check all relay status
        printf("%d", digitalRead(mot.RELAY1));
        printf("%d", digitalRead(mot.RELAY2));
        printf("\t");}
        //~ printf("%d",mot.RELAY1);
        //~ printf("%d",mot.RELAY2);
        printf(" ");
        printf(" ");
    }
    printf("\t");
    for (map<int, bool>::const_iterator it = dictlist.begin();
             it != dictlist.end(); ++it) {
            std::cout << it->second; // string's value
    };
    std::cout << revertrev;
    return dictlist;
};

void setlast(vector <MotorGroup> &mtg){
    auto size = mtg.size();
    for (std::size_t t = 0; t < size; t++) {
        MotorGroup &mot = mtg[t];
        if (t%2==0){
            mot.last = mot.BTN1;
            }
        else {
            mot.last = mot.BTN2;
            }
        }
    };

std::vector <MotorGroup> setup(int pino, int addr) {
    wiringPiSetup();
    mcp23017Setup(pino, addr);
    vector <MotorGroup> motors;
    int lim = 15;
    motors.reserve((lim + 1) / 4);
    for (int i = 0; i <= lim; i++) {
        if (i < lim / 2 + 1) {
            digitalWrite(pino + i, 1);
            pinMode(pino + i, INPUT);
            pullUpDnControl(pino + i, PUD_UP);
        };
        if (i > lim / 2) {
            pinMode(pino + i, OUTPUT);
            digitalWrite(pino + i, 1);
        };
    };
    for (int i = 0; 4 * i < lim; i++) {
        bool klaus = FALSE;
        if (digitalRead(pino + 2 * i) == 0) {
            if (digitalRead(pino + 2 * i + 1) == 0) {
                klaus = TRUE;
            };
        };
        MotorGroup mot(pino + 2 * i, pino + 2 * i + 1, pino + lim - 2 * i - 1, pino + lim - 2 * i, 0, klaus);
        motors.push_back(mot);
    }

    return motors;
}

std::tuple <MotorGroup, MotorGroup, MotorGroup, MotorGroup> hardcoded(int pino, int addr) {
    wiringPiSetup();
    mcp23017Setup(pino, addr);
    int lim = 15;
    for (int i = 0; i <= lim; i++) {
        if (i < lim / 2) {
            pinMode(pino + i, INPUT);
            pullUpDnControl(pino + i, PUD_UP);
        };
        if (i > lim / 2) {
            pinMode(pino + i, OUTPUT);

        };
    };

    MotorGroup mot1(pino, pino + 1, pino + 8, pino + 9, 0, FALSE);
    MotorGroup mot2(pino + 2, pino + 3, pino + 10, pino + 11, 0, FALSE);
    MotorGroup mot3(pino + 4, pino + 5, pino + 12, pino + 13, 0, FALSE);
    MotorGroup mot4(pino + 6, pino + 7, pino + 14, pino + 15, 0, FALSE);
    return std::make_tuple(mot1, mot2, mot3, mot4);
}*/

void motorcontrolloop() {
    /*std::vector <MotorGroup> mtg0 = setup(65, 0x20);
    std::vector <MotorGroup> mtg1 = setup(81, 0x21);
    std::vector <MotorGroup> mtg2 = setup(97, 0x22);
    std::vector <MotorGroup> mtg3 = setup(113, 0x23);
    std::vector <std::vector<MotorGroup>> sorted = concat(mtg0, mtg1, mtg2, mtg3);
    std::vector <MotorGroup> &norm = sorted[0];
    std::vector <MotorGroup> &rev = sorted[1];
    std::map<int, bool> normdone;
    std::map<int, bool> revdone;
    bool swiffle = TRUE;
    static bool initialized = false;
*/
    Py_Initialize();
  //PyImport_ImportModule("sys");
  //PyRun_SimpleString("sys.path.insert(0,'/home/pi/insect/python/')\n");
    PyRun_SimpleString("from robot import Robot\n""r = Robot(4)\n""r.motorassignment(6,3,10)\n");

    while (TRUE) {
    //sleep for better cpu
    sleep(0.05);
    while (::isgoing) {
        //run python functions
        PyRun_SimpleString("r.forwards()\n");
        //sleep for better cpu
        /*sleep(0.05);
        std::map<int, bool> crntnm = listen(norm, normdone, TRUE);
        normdone.insert(crntnm.begin(), crntnm.end());
        printf("\t");
        std::map<int, bool> crntrev = listen(rev, revdone, FALSE);
        revdone.insert(crntrev.begin(), crntrev.end());
        if (revertnorm || revertrev) {
            revertnorm = FALSE;
            revertrev = FALSE;
        };
        if (normdone.size() >= 6) {
            normdone.clear();
            revertrev = TRUE;
            swiffle = TRUE;
        };
        if (revdone.size() >= 3 && swiffle) {
            revdone.clear();
            revertnorm = TRUE;
            swiffle = FALSE;
            if(!initialized){
            initialized = true;
            setlast(norm);
            }
        };
        printf("\n");
    };

    while (::stopgoing) {
        //sleep for better cpu
        sleep(0.05);
        auto size1 = norm.size();
        auto size2 = rev.size();
        for (std::size_t i = 0; i < size1; i++) {
            MotorGroup &mot = norm[i];
            mot.chill();
            bool pressed1 = mot.pressed(mot.BTN1);
            bool pressed2 = mot.pressed(mot.BTN2);
            if (pressed1) {
                if (!mot.reversed) {
                    mot.forward();
                    sleep(0.3);
                    mot.revert();
                    mot.chill();
                };
                if (mot.reversed) {
                    mot.backward();
                    sleep(0.3);
                    mot.revert();
                    mot.chill();
                };
            };
            if(pressed2) {
                if (!mot.reversed) {
                    mot.backward();
                    sleep(0.3);
                    mot.revert();
                    mot.chill();
                };
                if (mot.reversed) {
                    mot.forward();
                    sleep(0.3);
                    mot.revert();
                    mot.chill();
                };
            }
        }
        for (std::size_t i = 0; i < size2; i++) {
            MotorGroup &mot = rev[i];
            mot.chill();
            bool pressed1 = mot.pressed(mot.BTN1);
            bool pressed2 = mot.pressed(mot.BTN2);
            if (pressed1) {
                if (!mot.reversed) {
                    mot.forward();
                    sleep(0.3);
                    mot.revert();
                    mot.chill();
                };
                if (mot.reversed) {
                    mot.backward();
                    sleep(0.3);
                    mot.revert();
                    mot.chill();
                };
            };
            if(pressed2) {
                if (!mot.reversed) {
                    mot.backward();
                    sleep(0.3);
                    mot.revert();
                    mot.chill();
                };
                if (mot.reversed) {
                    mot.forward();
                    sleep(0.3);
                    mot.revert();
                    mot.chill();
                };
            }
        }
        ::stopgoing = false;
            }*/
            }
        }


    }

void gstwaitloop() {
  while (1) {
    sleep(0.1);
    if (::isplaying) {
      puts("Gstwait thread detected audio playing");
      gstwait();
      ::isplaying = false;
      ::stopplaying = true;
      puts("Gstwait thread deteced audio has finished, sending free command.");
      puts("Switch language control and speakfeedback to original values.");
      ::langctrl=::langctrlorig;
      ::feedbacklevel=::feedbacklevelorig;
    }
  }
}

void gstfreeloop() {
  while (1) {
    sleep(0.1);
    if (::stopplaying) {
      puts("Gstfree thread got stop command.");
      if (::isplaying) {
        puts("Audio still playing, pausing");
        gst_element_set_state(pipeline, GST_STATE_PAUSED);
        puts("Paused audio.");
      }
      //puts("Freeing resources.");
      //gstfreerc();g
      ::stopplaying = false;
      ::isplaying = false;
      puts("Switch language control and speakfeedback to original values.");
      ::langctrl=langctrlorig;
      ::feedbacklevel=feedbacklevelorig;
      //puts("Released gstreamer resurces.");
    }
  }
}

static gboolean
bus_callx(GstBus * busx, GstMessage * msgx, gpointer data)
{
    GMainLoop *loopx = (GMainLoop *) data;

    /*if (::goexit) {
      g_main_loop_quit(loopx);
      //break
    }*/

    switch (GST_MESSAGE_TYPE(msgx)) {

    case GST_MESSAGE_EOS:
        g_print("End of stream\n");
        g_main_loop_quit(loopx);
        break;

    case GST_MESSAGE_ERROR:{
            gchar *debug;
            GError *error;

            gst_message_parse_error(msgx, &error, &debug);
            g_free(debug);

            g_printerr("Error: %s\n", error->message);
            g_error_free(error);

            g_main_loop_quit(loopx);
            break;
        }
    default:
        break;
    }

    const GstStructure *st = gst_message_get_structure(msgx);
    if (st && strcmp(gst_structure_get_name(st), "pocketsphinx") == 0) {
	if (g_value_get_boolean(gst_structure_get_value(st, "final"))) {
            ::pockres = g_value_get_string(gst_structure_get_value(st, "hypothesis"));
            //std::cout<<"a"<<::pockres<<"a\n";
            }
            ::pockres.erase(::pockres.find_last_not_of(" \n\r\t")+1);
    	    g_print("Got result: %s\n", ::pockres.c_str() );
            if (is_command(::pockres.c_str()) && ::langctrl) {
              std::cout<<"This was a valid command: "<<::pockres<<"\n";
            //}
            //append commands to list
            if (::pockres == "play") {
              if (::feedbacklevel == 0) {
                speakfeedback("Playing Helter Skelter.");
                }
              //play helterskelter
              ::cmdList.push_back("play=helterskelter");
            } else if (::pockres == "sing") {
              if (::feedbacklevel == 0) {
                speakfeedback("Singing Austrias national hymn.");
              }
              //play bundeshymne
              ::cmdList.push_back("play=bundeshymne");
            } else if (::pockres == "forward") {
              if (::feedbacklevel == 0) {
                speakfeedback("Moving forward.");
              }
              //forward
              ::cmdList.push_back("forward");
            } else if (::pockres == "stop") {
              if (::feedbacklevel ==0) {
                speakfeedback("Stopping movement.");
                }
              //stop
              ::cmdList.push_back("stop");
            } else if (::pockres == "speak") {
              if (::feedbacklevel >= 0) {
                speakfeedback("");
              }
            } else {
              //error
              //::cmdList.push_back(::pockres);
              puts("An error occured.");
              }
            //sleep to ensure time between commands
            sleep(5);
            }
    }

    return TRUE;
}

void langctrlloop() {
  //Initialization
  loopx = g_main_loop_new(NULL, false);
  //create gstreamer elements
  pipelinex = gst_pipeline_new("pipelinex");
  sourcex = gst_element_factory_make("tcpclientsrc","tcp-source");
  decoderx = gst_element_factory_make("pocketsphinx","asr");
  sinkx = gst_element_factory_make("fakesink","output");
  if (!pipelinex || !sourcex || !decoderx || !sinkx) {
    g_printerr("One element could not be created. Exiting.\n");
    //return -1;
  }
  //set keyword file
  g_object_set(G_OBJECT(decoderx), "kws", "/home/pi/insect/keyword.list",NULL);
  //set tcpserver host and port
  g_object_set(G_OBJECT(sourcex), "host", "127.0.0.1", NULL);
  g_object_set(G_OBJECT(sourcex), "port", 3000, NULL);
  //add message handler
  busx = gst_pipeline_get_bus(GST_PIPELINE(pipelinex));
  bus_watch_id = gst_bus_add_watch(busx, bus_callx, loopx);
  gst_object_unref(busx);
  //add elements to the pipeline
  gst_bin_add_many(GST_BIN(pipelinex), sourcex, decoderx, sinkx, NULL);
  //link elements together
  gst_element_link_many(sourcex, decoderx, sinkx, NULL);
  gst_element_set_state(pipelinex, GST_STATE_PLAYING);
  //Iterate
  puts("Running language control...");
  g_main_loop_run(loopx);
  //clean up
  puts("Returned, stopping playback.");
  gst_element_set_state(pipelinex, GST_STATE_NULL);
  puts("Deleting pipeline.");
  gst_object_unref(GST_OBJECT(pipelinex));
  g_source_remove(bus_watch_id);
  g_main_loop_unref(loopx);

  //return 0;

}

void stopall() {
  //stop all threads
  //::goexit = true;
  //sleep(5);
  //Also reset all gpio pins here!
  ::isgoing = false;
  sleep(15);
  Py_Finalize();
  //::stopgoing = true;
  /*while (::stopgoing){
  sleep(0.1);
  }*/
  //stopping camera
  char stopcmd[100];
  strcpy(stopcmd, RPI_CAM_WEB_INTERFACE_PATH);
  strcat(stopcmd, "stop.sh");
  system(stopcmd);
  //free gst resources
  gstfreerc();
  //close server
  close(server);
  //remove socket file
  unlink(SOCKET_FILENAME);
  sleep(10);
}

void shutdown() {
  puts("\nShutting down.");
  //stopping camera, gst, server
  stopall();
  //shutdown system
  system(SHUTDOWN_CMD);
  //exit
  exit(0);
}

void reboot() {
  puts("\nRebooting.");
  //stop camera, gst, server
  stopall();
  //reboot system
  system(REBOOT_CMD);
  //exit
  exit(0);
}

void bind_listen_socket(int &server, sockaddr_un &server_addr)
{
  // create socket
  server = socket(PF_UNIX, SOCK_STREAM, 0);
  if (!server) {
    perror("socket");
    exit(-1);
  }

  // call bind to associate the socket with our local address and
  // port
  if (bind(server, (const struct sockaddr *)&server_addr, sizeof(server_addr)) < 0) {
    perror("bind");
    exit(-1);
  }

  // convert the socket to listen for incoming connections
  if (listen(server, 0) < 0) {
    perror("listen");
    exit(-1);
  }
  char str[80];
  strcpy(str,"chmod 777 ");
  strcat(str,SOCKET_FILENAME);
  system(str);

  puts("Listening to connection...");

}
void signal_callback_handler(int signum)
{
  puts("\nQuitting.");
  //stopping camera, gst, server
  stopall();
  // signal handled
  exit(0);
}

void listen_for_msg()
{
struct sockaddr_un server_addr, client_addr;
  socklen_t clientlen = sizeof(client_addr);
  int buflen, nread;
  char *buf;

  puts("Hello World");

  // listen to SIGINT, SIGTERM, and SIGKILL
  signal(SIGINT, signal_callback_handler);
  signal(SIGTERM, signal_callback_handler);
  signal(SIGKILL, signal_callback_handler);

  // setup socket address structure
  memset(&server_addr, 0, sizeof(server_addr));
  server_addr.sun_family = AF_UNIX;
  strcpy(server_addr.sun_path, SOCKET_FILENAME);

  // bind and listen on the socket file
  bind_listen_socket(server, server_addr);

  // allocate buffer
  buflen = 1024;
  buf = new char[buflen+1];
  // loop to handle all requests
  while (1) {
    unsigned int client = accept(server, (struct sockaddr *)&client_addr, &clientlen);

    // got a request, close the socket
    close(server);
    unlink(SOCKET_FILENAME);

    // read a request
    memset(buf, 0, buflen);
    nread = recv(client, buf, buflen, 0);

    printf("\nClient says: %s\n\n", buf);

    // echo back to the client
    send(client, buf, nread, 0);

    close(client);

    sleep(2);

    // re-bind and listen on the socket
    bind_listen_socket(server, server_addr);

    ::cmdList.push_back(buf);
  }
}

void check_for_msg()
{
  while (1) {
    if (! ::cmdList.empty()) {
      std::string firstcmd = ::cmdList.front();
      ::cmdList.pop_front();
      //std::cout<<firstcmd<<"\n";
      /*std::string segment;
      std::string thekey;*/

      /*if (std::getline(firstcmd, segment, '=')) {
        thekey = segment;
        std::getline(firstcmd, segment, '=');
        int thevalue = std::stoi(segment);
        std::cout<<thekey<<"   "<<thevalue<<"\n";
      } else {
        thekey = segment;
        std::cout<<thekey<<"   "<<"\n";
      }*/
      std::list<string> result;
      boost::split(result, firstcmd, boost::is_any_of("="));
      std::string thekey = result.front();
      result.pop_front();
      if (! result.empty()) {
        //int thevalue = std::stoi(result.front());
        ::thevalue = result.front();
        //std::cout<<"key: "<<thekey<<"value: "<<thevalue<<"\n";
      }
      //now checking commands
      if (thekey == "play") {
        puts("Detected play command.");
        puts("Stopping language control and speakfeedback.");
        ::langctrlorig = ::langctrl;
        ::feedbacklevelorig = ::feedbacklevel;
        ::langctrl=false;
        ::feedbacklevel=-1;
        if (::isplaying) {
          ::stopplaying = true;
          sleep(0.5);
        }
        //spoken feedback
        if (::feedbacklevelorig > 0) {
          if (::thevalue == "helterskelter") {
            speakfeedback("Playing Helter Skelter.");
          } else if (::thevalue == "bundeshymne") {
            speakfeedback("Singing Austrias national hymn");
          } else if (::thevalue == "faust") {
            speakfeedback("Reading Faust by Goethe.");
          } else if (::thevalue == "daedalus"){
            speakfeedback("Reading Daedalus and Icarus by Ovid.");
          } else {
            speakfeedback("Speaking recorded audio.");
          }
        }
        char filestr[200];
        char filenamestr[200];
        strcpy(::thefilestr, "");
        strcpy(filenamestr, AUDIO_PATH);
        strcat(filenamestr, ::thevalue.c_str());
        strcpy(filestr, filenamestr);
        strcat(filestr, ".ogg");
        if (file_exists(filestr)) {
          puts("Found ogg file.");
          strcpy(::thefilestr, filestr);
        } else {
          strcpy(filestr, filenamestr);
          strcat(filestr, ".wav");
          std::cout<<filestr<<"\n";
          if (file_exists(filestr)) {
            puts("Found wav file, converting.");
            strcpy(::thefilestr, filenamestr);
            strcat(::thefilestr, ".ogg");
            char cmdstr[80];
            strcpy(cmdstr, "oggenc -q 3 -o ");
            strcat(cmdstr, filenamestr);
            strcat(cmdstr, ".ogg ");
            strcat(cmdstr, filenamestr);
            strcat(cmdstr, ".wav");
            system(cmdstr);
          } else {
            puts("Invalid audio file.");
            }
          }
          if (strlen(::thefilestr) != 0) {
            puts("We are now playing the audio file.");
            //Initialize gstreamer
            /*GstElement *pipeline;
            GstBus *bus;
            GstMessage *msg;
            gst_init(&argc, &argv);*/
            //creating pipeline
            char pipestr[200];
            strcpy(pipestr, "playbin uri=file://");
            strcat(pipestr, ::thefilestr);
            pipeline = gst_parse_launch(pipestr, NULL);
            //start playing
            gst_element_set_state(pipeline, GST_STATE_PLAYING);
            //wait
            //gstwait();
            ::isplaying = true;
            /*bus = gst_element_get_bus (pipeline);
            msg = gst_bus_timed_pop_filtered (bus, GST_CLOCK_TIME_NONE, (GstMessageType)(GST_MESSAGE_ERROR | GST_MESSAGE_EOS));*/
            //free resources
            //gstfreerc();
            /*if (msg != NULL)
              gst_message_unref(msg);
            gst_object_unref(bus);
            gst_element_set_state(pipeline, GST_STATE_NULL);
            gst_object_unref(pipeline);*/
          } else {
            puts("An error occured. Cannot play audio file.");
            }
          puts("Switch language control and speakfeedback to original values.");
          ::langctrl=langctrlorig;
          ::feedbacklevel=feedbacklevelorig;
      } else if (thekey == "stopsound") {
        puts("Stopsound command detected.");
        if (::isplaying) {
          ::stopplaying = true;
          sleep(0.5);
        }
      } else if (thekey == "camera") {
        puts("Camera activation command detected.");
        //spoken feedback
        char camcmd[100];
        strcpy(camcmd, RPI_CAM_WEB_INTERFACE_PATH);
        int camval = std::stoi(thevalue);
        if (camval == 0) {
          puts("Starting camera.");
          strcat(camcmd, "start.sh");
          if (feedbacklevel > 0) {
            speakfeedback("Starting camera.");
          }
        } else {
          puts("Stopping camera.");
          if (feedbacklevel > 0) {
            speakfeedback("Starting camera.");
          }
          strcat(camcmd, "stop.sh");
        }
        system(camcmd);
        //sleeping to avoid too fast switch on/off
        sleep(5);
      } else if (thekey == "shutdown") {
          if (feedbacklevel > 0) {
            speakfeedback("Shutting down.");
          }
          shutdown();
      } else if (thekey == "reboot") {
          if (feedbacklevel > 0) {
            speakfeedback("Rebooting.");
          }
          reboot();
      } else if (thekey == "langctrl") {
          int langval = std::stoi(thevalue);
          if (langval == 0) {
            puts("Switching on language control.");
            if (feedbacklevel > 0) {
              speakfeedback("Switching on language control.");
            }
            ::langctrl = true;
            ::langctrlorig = true;
          } else {
            puts("Switching off language control.");
            if (feedbacklevel > 0) {
              speakfeedback("Switching off language control.");
            }
            ::langctrl = false;
            ::langctrlorig = false;
          }
      } else if (thekey == "changevol") {
          char volstr[100];
          int thevolume = std::stoi(thevalue);
          int therealvolume = round(thevolume * MAXVOL);
          std::string volpercent = std::to_string(therealvolume);
          std::cout<<"Changing volume to "<<thevalue<<"%\n";
          if (feedbacklevel >0 ){
            char chvolstr[100];
            strcat(chvolstr, "Changing volume to ");
            strcpy(chvolstr, thevalue.c_str());
            strcpy(chvolstr, " percent.");
            speakfeedback(chvolstr);
          }
          strcpy(volstr, "pactl set-sink-volume @DEFAULT_SINK@ ");
          strcat(volstr, volpercent.c_str());
          strcat(volstr, "%");
          system(volstr);
          puts("Changed volume.");
      } else if (thekey == "forward") {
          if (feedbacklevel > 0) {
            speakfeedback("Going forward.");
          }
          //forward here
          ::isgoing = true;
      } else if (thekey == "stop") {
          //stop here
          if (feedbacklevel > 0) {
            speakfeedback("Stopping.");
          }
          ::isgoing = false;
          //::stopgoing = true;
      } else if (thekey == "feedbacklevel") {
          int newlevel = std::stoi(thevalue);
          ::feedbacklevel=newlevel;
          ::feedbacklevelorig=newlevel;
      } else {
        puts("Invalid command.");
      }
    } else {
      sleep(0.1);
    }
  }
}

int main(int argc, char **argv)
{
  //creating command list
//  std::list<string> cmdList;
  //initializing gstreamer
  /*GstElement *pipeline;
  GstBus *bus;
  GstMessage *msg;*/

  gst_init(&argc, &argv);

  //managing sock thread
  std::thread sock_thread (listen_for_msg);
  puts("Socket thread started.");

  //managing cmd thread
  std::thread cmd_thread (check_for_msg);
  puts("Cmd thread started.");

  //managing gstfree thread
  std::thread gstfree_thread (gstfreeloop);
  puts("Gstfree thread started.");

  //managing gstwait thread
  std::thread gstwait_thread (gstwaitloop);
  puts("Gstwait thread started.");

  //managing motorcontrol thread
  std::thread motorcontrol_thread (motorcontrolloop);
  puts("Motorcontrol thread started.");

  //managing language control thread
  std::thread langctrl_thread (langctrlloop);
  puts("Language control thread started.");

  //speaking test line
  if (::feedbacklevel > 0) {
    speakfeedback("Starting up system.");
  }

  //joining threads
  sock_thread.join();
  cmd_thread.join();
  gstfree_thread.join();
  gstwait_thread.join();
  motorcontrol_thread.join();
  langctrl_thread.join();

  puts("Quitting.");

  //stopping camera, gat, server
  stopall();

  return 0;
}
