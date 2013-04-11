
#ifndef __MYTEST_H_
#define __MYTEST_H_

#include <iostream>
#include <string>

using namespace std;

class MyTest
{
public:
    MyTest();
    void print();

private:
    const int m_pageSize;
};

#endif

