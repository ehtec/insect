#ifndef MotorGroup_h
#define MotorGroup_h

#include <iostream>
#include <iomanip>
#include <wiringPi.h>
using namespace std;

class MotorGroup {
  private:
    int MSFT;
    bool _isdone = false;
    //bool BTN1.isdone = false;
    //bool BTN2.isdone = false;
  public:
    MotorGroup(int BTN1, int BTN2, int RLY1, int RLY2, int MSFT, bool reversed);
    int last;
    int BTN1;
    int BTN2;
    int RELAY1;
    int RELAY2;
    bool reversed;
    void forward();
    void backward();
    void chill();
    bool isdone();
    bool pressed(int BUTTON);
    void revert();
    //void processevent(int LISTENER, void func);
};
MotorGroup::MotorGroup(int BTN1, int BTN2, int RLY1, int RLY2, int MSFT, bool reversed)
{
  this->BTN1 = BTN1;
  this->BTN2 = BTN2;
  this->RELAY1 = RLY1;
  this->RELAY2 = RLY2;
  this->MSFT = MSFT;
  this->reversed = reversed;
  this->last = BTN1;
}
void MotorGroup::forward()
{
  //muss noch 1 & 0 andern
  digitalWrite(this->RELAY1, 0);
  digitalWrite(this->RELAY2, 1);
  //printf("Yaya");
}
void MotorGroup::backward() {
  digitalWrite(this->RELAY1, 1);
  digitalWrite(this->RELAY2, 0);
}
void MotorGroup::chill() {
  digitalWrite(this->RELAY1, 1);
  digitalWrite(this->RELAY2, 1);
}
bool MotorGroup::isdone() {
  return (bool)this->_isdone;
}
bool MotorGroup::pressed(int BUTTON) {
  //float btnVolts = digitalRead(BUTTON);
  bool btnread = digitalRead(BUTTON);
  //~ std::cout<<btnread;
  if (this->reversed){
    if (btnread) {
      //~ if (this->last == BUTTON){
        //~ printf("HELLNO");
        //~ }
      //~ printf(" R%d", this->last);
      //~ printf(" R%d",BUTTON);
      this->_isdone = true;
      this->last = BUTTON;
      return true;
    }  
  }else if (!this->reversed){
    if (!btnread) {
    //~ printf("yay");
      //~ printf(" R%d", this->last);
      //~ printf(" R%d",BUTTON);
      this->_isdone = true;
      this->last = BUTTON;
      return true;
    }
  }
  return false;
  
}
void MotorGroup::revert() {
  this->_isdone = false;
}
/*void MotorGroup::processevent(int LISTENER, void func) {
  this->pressed(LISTENER);
  if (this->isdone()) {
    func();
    this->revert();
  }
}*/
#endif //MotorGroup_h
