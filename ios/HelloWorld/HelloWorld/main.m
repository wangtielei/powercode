//
//  main.m
//  HelloWorld
//
//  Created by guanjianjun on 5/29/12.
//  Copyright (c) 2012 __MyCompanyName__. All rights reserved.
//

#import <UIKit/UIKit.h>

#import "HelloWorldAppDelegate.h"

//引入logger头文件
#import "Log4Cocoa.h"
#import "DDLog.h"

//声明全局logger，注意别加上static，否则编译过不了
L4Logger * g_l4Logger = nil;

// Log levels: off, error, warn, info, verbose
#ifdef DEBUG
int ddLogLevel = LOG_LEVEL_VERBOSE;
#else
int ddLogLevel = LOG_LEVEL_WARN;
#endif



int main(int argc, char *argv[])
{
    @autoreleasepool {
        
        //初始化log4Cocoa
        //设置为输出任何log
        [[L4Logger rootLogger] setLevel:[L4Level all]];
        //将log输出到Console
        [[L4Logger rootLogger] addAppender:[[L4ConsoleAppender alloc] initTarget:YES withLayout:[L4Layout simpleLayout]]];
        //将log输出到文件
        [[L4Logger rootLogger] addAppender:[[L4FileAppender alloc] initWithLayout:[L4Layout simpleLayout] fileName:@"/tmp/helloworld.log" append:YES]];
        
        //初始化全局logger
        g_l4Logger = [L4Logger loggerForName:@"hellworldlogger"];
        
        return UIApplicationMain(argc, argv, nil, NSStringFromClass([HelloWorldAppDelegate class]));
    }
}
