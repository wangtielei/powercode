//
//  main.m
//  BestPractice
//
//  Created by guanjianjun on 12-7-16.
//  Copyright (c) 2012年 __MyCompanyName__. All rights reserved.
//

#import <UIKit/UIKit.h>

#import "AppDelegate.h"

//引入lumberjack头文件
#import "DDLog.h"
#import "DDTTYLogger.h"
#import "DDASLLogger.h"
#import "DDFileLogger.h"
#import "CustomLogFormatter.h"
#import "ContextFilterLogFormatter.h"
#import "DispatchQueueLogFormatter.h"

// Log levels: off, error, warn, info, verbose
#ifdef DEBUG
int ddLogLevel = LOG_LEVEL_VERBOSE;
#else
int ddLogLevel = LOG_LEVEL_WARN;
#endif

int main(int argc, char *argv[])
{
    @autoreleasepool {
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

        return UIApplicationMain(argc, argv, nil, NSStringFromClass([AppDelegate class]));
    }
}
