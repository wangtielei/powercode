
#import <Foundation/Foundation.h>
#import "DDLog.h"


@interface CustomLogFormatter : NSObject <DDLogFormatter>
{   
    NSDateFormatter *_dateFormatter;
    NSString *_pid;
}

@end