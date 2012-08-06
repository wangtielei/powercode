//
//  HelloWorldAppDelegate.m
//  HelloWorld
//
//  Created by guanjianjun on 5/29/12.
//  Copyright (c) 2012 __MyCompanyName__. All rights reserved.
//

#import "HelloWorldAppDelegate.h"

#import "Log4Cocoa.h"

//引入lumberjack头文件
#import "DDLog.h"
#import "DDTTYLogger.h"
#import "DDASLLogger.h"
#import "DDFileLogger.h"
#import "CustomLogFormatter.h"
#import "ContextFilterLogFormatter.h"
#import "DispatchQueueLogFormatter.h"


//引入全局变量g_l4Logger
extern L4Logger * g_l4Logger;

extern int ddLogLevel;

@interface HelloWorldAppDelegate()

//初始化logger
- (void)initLogger;

@end

@implementation HelloWorldAppDelegate

@synthesize window = _window;

/*
 *一定要实现名称为l4Logger的方法，因为打印log的宏里直接使用了[self l4Logger]
 */
- (L4Logger *)l4Logger
{
    return g_l4Logger;
}

- (void)initLogger
{
    id<DDLogFormatter> logFormatter = [[CustomLogFormatter alloc]init];
    //DDTTYLogger将log打印到iphone系统的console
    [[DDTTYLogger sharedInstance] setLogFormatter:logFormatter];
    //DDTTYLogger将log打印到Xcode的console
    [[DDASLLogger sharedInstance] setLogFormatter:logFormatter];
    
    [DDLog addLogger:[DDTTYLogger sharedInstance]];
    [DDLog addLogger:[DDASLLogger sharedInstance]];
    
#ifdef DEBUG
    //DDFileLogger将log打印到系统文件
    //* On Mac, this is in ~/Library/Logs/<Application Name>.
    //* On iPhone, this is in ~/Library/Caches/Logs.
    DDFileLogger *fileLogger = [[DDFileLogger alloc] init];
    [fileLogger setLogFormatter:logFormatter];
    
    [DDLog addLogger:fileLogger];
#endif
}

- (BOOL)application:(UIApplication *)application didFinishLaunchingWithOptions:(NSDictionary *)launchOptions
{
    [self initLogger];
    
    log4Debug(@"======begin run======");
    log4Trace(@"trace test");
    log4Debug(@"debug test");
    log4Info(@"info test");
    log4Warn(@"warn test");
    log4Error(@"error test");
    log4Fatal(@"fatal test");
        
    DDLogVerbose(@"this is ddlog verbose");
    DDLogInfo(@"this is ddlog info");
    DDLogWarn(@"this is ddlog warn");
    DDLogError(@"this is ddlog error");
    
    // Override point for customization after application launch.
    return YES;
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
}

@end
