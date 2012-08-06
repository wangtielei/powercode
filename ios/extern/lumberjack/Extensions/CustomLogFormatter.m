#import "CustomLogFormatter.h"

static NSString *NSMethodCallStartIndicator = @"start";
static NSString *NSMethodCallEndIndicator   = @"end";

@implementation CustomLogFormatter

- (id)init
{
    if((self = [super init]))
    {
        _dateFormatter = [[NSDateFormatter alloc] init];
        [_dateFormatter setFormatterBehavior:NSDateFormatterBehavior10_4];
        [_dateFormatter setDateFormat:@"yyyy/MM/dd HH:mm:ss:SSS"];
        	
        _pid = [NSString stringWithFormat:@"%i", (int)getpid()];
    }
    return self;
}

- (NSString *)formatLogMessage:(DDLogMessage *)logMessage
{
    NSString *dateString = [_dateFormatter stringFromDate:(logMessage->timestamp)];
    	
    long instanceID = [logMessage->logMsg longLongValue];
    
    if (instanceID != 0) 
    {
        BOOL callStart = [logMessage->logMsg rangeOfString:NSMethodCallStartIndicator].length != 0;
        return [NSString stringWithFormat:@"%@ [%@:%@:%lu] %@:%@ %@", 
                dateString, _pid, [logMessage threadID], instanceID, [logMessage fileName], [logMessage methodName], callStart ? NSMethodCallStartIndicator : NSMethodCallEndIndicator];
    } 
    else if ([logMessage->logMsg isEqualToString:NSMethodCallStartIndicator] ||
               [logMessage->logMsg isEqualToString:NSMethodCallEndIndicator]) 
    {
        return [NSString stringWithFormat:@"%@ [%@:%@] %@:%@ %@", 
                dateString, _pid, [logMessage threadID], [logMessage fileName], [logMessage methodName], logMessage->logMsg];
    } 
    else 
    {
        return [NSString stringWithFormat:@"%@ [%@:%@] %@",
                dateString, _pid, [logMessage threadID], logMessage->logMsg];
    }
}
@end
