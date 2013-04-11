
#include "MyTest.h"

MyTest::MyTest()
    :m_pageSize(100)
{
    cout << "enter MyTest()" <<endl;
}

void MyTest::print()
{
    cout << "entery print()" << endl;
    cout << "this is Makefile test project" << endl;
    cout << "page size: " << m_pageSize << endl;
}

