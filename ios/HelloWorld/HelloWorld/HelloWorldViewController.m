//
//  HelloWorldViewController.m
//  HelloWorld
//
//  Created by guanjianjun on 5/29/12.
//  Copyright (c) 2012 __MyCompanyName__. All rights reserved.
//

#import "HelloWorldViewController.h"

#import "Log4Cocoa.h"

//引入全局变量g_l4Logger
extern L4Logger * g_l4Logger;


@interface HelloWorldViewController ()

@end

@implementation HelloWorldViewController
@synthesize textField;
@synthesize label;


/*
 在实现setter和getter时顺便定义成员变量_userName，这样就不用在头文件里定义成员变量
 When the compiler encounters the @synthesize directive, it automatically generates the following two accessor methods for you:
 
 - (NSString *)userName
 
 - (void)setUserName:(NSString *)newUserName
 
 By adding the underscore to userName in your @synthesize code line, you tell the compiler to use _userName as the name of the instance variable for the userName property. Because you didn't declare an instance variable called _userName in your class, this code line asks the compiler to synthesize that as well.
 */
@synthesize userName = _userName;

/*
 *一定要实现名称为l4Logger的方法，因为打印log的宏里直接使用了[self l4Logger]
 */
- (L4Logger *)l4Logger
{
    return g_l4Logger;
}

- (void)viewDidLoad
{
    [super viewDidLoad];
	// Do any additional setup after loading the view, typically from a nib.
}

- (void)viewDidUnload
{
    [self setTextField:nil];
    [self setLabel:nil];
    [super viewDidUnload];
    // Release any retained subviews of the main view.
}

- (BOOL)shouldAutorotateToInterfaceOrientation:(UIInterfaceOrientation)interfaceOrientation
{
    log4Trace(@"trace test");
    log4Debug(@"debug test");
    log4Info(@"info test");
    log4Warn(@"warn test");
    log4Error(@"error test");
    log4Fatal(@"fatal test");
    
    return (interfaceOrientation != UIInterfaceOrientationPortraitUpsideDown);
}

//implementation change greeting
- (IBAction)changeGreeting:(UIButton *)sender 
{
    self.userName = self.textField.text;
    
    NSString *nameString = self.userName;
    
    if ([nameString length] == 0)
    {
        nameString = @"World";
    }
            
    NSString *greeting = [[NSString alloc] initWithFormat:@"Hello, %@!", nameString];
    
    self.label.text = greeting;    
}

//实现text field控件的delegate方法，让
- (BOOL)textFieldShouldReturn:(UITextField *)theTextField 
{
    //resignFirstResponder函数用于放弃第一个反馈者的权利，即释放键盘所有权
    [theTextField resignFirstResponder];
    
    return YES;
    
}

@end
