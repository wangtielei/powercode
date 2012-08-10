//
//  AppDelegate.m
//  BestPractice
//
//  Created by guanjianjun on 12-7-16.
//  Copyright (c) 2012年 __MyCompanyName__. All rights reserved.
//

#import "AppDelegate.h"
#import "MessageUI/MessageUI.h"

@interface AppDelegate()
{
    NSUncaughtExceptionHandler *defaultExceptionHandler;
}

@end

@implementation AppDelegate

@synthesize window = _window;

- (BOOL)application:(UIApplication *)application didFinishLaunchingWithOptions:(NSDictionary *)launchOptions
{
    [self initDDLog];
    
    // Override point for customization after application launch.
    DDLogVerbose(@"this is ddlog verbose");
    DDLogInfo(@"this is ddlog info");
    DDLogWarn(@"this is ddlog warn");
    DDLogError(@"this is ddlog error");
    return YES;
}

- (NSString*)loggingPath 
{
    NSArray *paths = NSSearchPathForDirectoriesInDomains(NSDocumentDirectory, NSUserDomainMask, YES);
    NSString *documentsDirectory = [paths objectAtIndex:0];
    NSString *logPath = [documentsDirectory stringByAppendingPathComponent:@"practice.log"];
    return logPath;    
}

- (BOOL)deleteLogFile 
{
    BOOL success = [[NSFileManager defaultManager] removeItemAtPath:[self loggingPath] error:nil];    
    return success;
}
      
void MyUncaughtExceptionHandler(NSException *exception)
{
    printf("uncaught %s\n", [[exception name] cStringUsingEncoding:NSASCIIStringEncoding]);
    
    // 显示当前堆栈内容
    NSArray *callStackArray = [exception callStackReturnAddresses];
    int frameCount = [callStackArray count];
    void *backtraceFrames[frameCount];
    
    for (int i=0; i<frameCount; i++) 
    {
        backtraceFrames[i] = (void *)[[callStackArray objectAtIndex:i] unsignedIntegerValue];
    }
}

- (void) initDDLog
{
    id<DDLogFormatter> logFormatter = [[CustomLogFormatter alloc]init];
    //DDTTYLogger将log打印到iphone系统的console
    [[DDTTYLogger sharedInstance] setLogFormatter:logFormatter];
    //DDTTYLogger将log打印到Xcode的console
    [[DDASLLogger sharedInstance] setLogFormatter:logFormatter];
    
    [DDLog addLogger:[DDTTYLogger sharedInstance]];
    [DDLog addLogger:[DDASLLogger sharedInstance]];
    
#ifdef DEBUG
    
    //delete old file
    [self deleteLogFile];
    
    //DDFileLogger将log打印到系统文件
    //* On Mac, this is in ~/Library/Logs/<Application Name>.
    //* On iPhone, this is in ~/Library/Caches/Logs.
    //DDFileLogger *fileLogger = [DDLogFileInfo logFileWithPath:[self loggingPath]];
    DDFileLogger *fileLogger = [[DDFileLogger alloc] init];
    [fileLogger setLogFormatter:logFormatter];
    
    [DDLog addLogger:fileLogger];
#endif

    defaultExceptionHandler = NSGetUncaughtExceptionHandler();
    NSSetUncaughtExceptionHandler(&MyUncaughtExceptionHandler);
    
    // 这里重载程序正常退出时UIApplicationWillTerminateNotification接口
    UIApplication *app = [UIApplication sharedApplication];
    [[NSNotificationCenter defaultCenter] addObserver:self
                                             selector:@selector(exit_processing:)
                                                 name:UIApplicationWillTerminateNotification
                                               object:app];
}

- (void)exit_processing:(NSNotification *)notification 
{
    DDLogEndMethodInfo();
    NSSetUncaughtExceptionHandler(defaultExceptionHandler);
}

void stacktrace(int sig, siginfo_t *info, void *context)
{
    NSString *mstr = [[NSString alloc] init];
    [mstr stringByAppendingString:@"Stack:\n"];
    void* callstack[128];
    int i, frames = backtrace(callstack, 128);
    char** strs = backtrace_symbols(callstack, frames);
    for (i = 0; i <frames; ++i) {
        [mstr stringByAppendingFormat:@"%s\n", strs[i]];
    }
}

- (void)redirectSignal
{
    struct sigaction mySigAction;
    mySigAction.sa_sigaction = stacktrace;
    mySigAction.sa_flags = SA_SIGINFO;
    
    sigemptyset(&mySigAction.sa_mask);
    sigaction(SIGQUIT, &mySigAction, NULL);
    sigaction(SIGILL , &mySigAction, NULL);
    sigaction(SIGTRAP, &mySigAction, NULL);
    sigaction(SIGABRT, &mySigAction, NULL);
    sigaction(SIGEMT , &mySigAction, NULL);
    sigaction(SIGFPE , &mySigAction, NULL);
    sigaction(SIGBUS , &mySigAction, NULL);
    sigaction(SIGSEGV, &mySigAction, NULL);
    sigaction(SIGSYS , &mySigAction, NULL);
    sigaction(SIGPIPE, &mySigAction, NULL);
    sigaction(SIGALRM, &mySigAction, NULL);
    sigaction(SIGXCPU, &mySigAction, NULL);
    sigaction(SIGXFSZ, &mySigAction, NULL);
}
/************************
 *发送mail
 ***********************/
- (void)sendLogByMail 
{    
    MFMailComposeViewController *picker = [[MFMailComposeViewController alloc] init];
    picker.mailComposeDelegate = self;
    [picker setSubject:[NSString stringWithFormat:@"%@ - Log", [self appName]]];
    NSString *message = [NSString stringWithContentsOfFile:[self loggingPath] encoding:NSUTF8StringEncoding error:nil];
    [picker setMessageBody:message isHTML:NO];
}

- (NSString*)appName
{
    return @"BestPractice";
}
- (void)applicationWillResignActive:(UIApplication *)application
{
    // Sent when the application is about to move from active to inactive state. This can occur for certain types of temporary interruptions (such as an incoming phone call or SMS message) or when the user quits the application and it begins the transition to the background state.
    // Use this method to pause ongoing tasks, disable timers, and throttle down OpenGL ES frame rates. Games should use this method to pause the game.
}

- (void)applicationDidEnterBackground:(UIApplication *)application
{
    // Use this method to release shared resources, save user data, invalidate timers, and store enough application state information to restore your application to its current state in case it is terminated later. 
    // If your application supports background execution, this method is called instead of applicationWillTerminate: when the user quits.
}

- (void)applicationWillEnterForeground:(UIApplication *)application
{
    // Called as part of the transition from the background to the inactive state; here you can undo many of the changes made on entering the background.
}

- (void)applicationDidBecomeActive:(UIApplication *)application
{
    // Restart any tasks that were paused (or not yet started) while the application was inactive. If the application was previously in the background, optionally refresh the user interface.
}

- (void)applicationWillTerminate:(UIApplication *)application
{
    // Called when the application is about to terminate. Save data if appropriate. See also applicationDidEnterBackground:.
    DDLogEndMethodInfo();
}

@end
