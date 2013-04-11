
#include "MyTest.h"
#include "test.h"
#include <netdb.h>
#include <arpa/inet.h>

int main(int argc, char **args)
{
    cout << "enter main()" << endl;

    MyTest myTest;
    myTest.print();
    
    g_count = 10;
    cout << "g_count: " << g_count << endl;

    struct hostent *he;
    char hostname[20] = {0};
    gethostname(hostname, sizeof(hostname));
    he = gethostbyname(hostname);
    cout << "hostname: " << hostname << endl;
    cout << "ipaddress: " << inet_ntoa(*(struct in_addr*)(he->h_addr)) << endl;
    char **pAddrList = he->h_addr_list;
    int i=0;
    while(pAddrList[i] != NULL)
    {
        cout << "ip index: " << i << "ip addr: " << inet_ntoa(*(struct in_addr*)(pAddrList[i])) << endl;
        ++i;
    }

    return 0;
}

