//
//  SecondViewController.m
//  BestPractice
//
//  Created by guanjianjun on 12-7-16.
//  Copyright (c) 2012年 __MyCompanyName__. All rights reserved.
//

#import "SecondViewController.h"
#include <dlfcn.h>

#define SBSERVPATH "/System/Library/PrivateFrameworks/SpringBoardServices.framework/SpringBoardServices"
#define UIKITPATH "/System/Library/Framework/UIKit.framework/UIKit"

//定义函数指针
typedef mach_port_t* (*SBSSpringBoardServerPort)();

typedef int (*SBLockDevice)(mach_port_t* port, Boolean enable);
typedef int (*SBShutdown)(mach_port_t* port);
typedef int (*SBReboot)(mach_port_t *port);
typedef int (*SBSuspend)(mach_port_t *port, Boolean lastApp);

/**
 * 本类用于调研私有框架的函数.
 * 1.SpringBoard框架;
 *
 */
@interface SecondViewController ()
{
    mach_port_t *mServicePort;
    
    void *mSBHandler;
}
@end

@implementation SecondViewController

@synthesize titleLable = _titleLable;
@synthesize shutdownButton = _shutdownButton;
@synthesize rebootButton = _rebootButton;
@synthesize lockdeviceButton = _lockdeviceButton;
@synthesize logoutButton = _logoutButton;
@synthesize suspendButton = _suspendButton;

- (void)viewDidLoad
{
    [super viewDidLoad];
	// Do any additional setup after loading the view, typically from a nib.
    
    [_titleLable setText:NSLocalizedString(@"privatetitle", nil)];
    [_shutdownButton setTitle:NSLocalizedString(@"shutdown", nil) forState:UIControlStateNormal];
    [_rebootButton setTitle:NSLocalizedString(@"reboot", nil) forState:UIControlStateNormal];
    [_lockdeviceButton setTitle:NSLocalizedString(@"lockdevice", nil) forState:UIControlStateNormal];
    [_logoutButton setTitle:NSLocalizedString(@"logout", nil) forState:UIControlStateNormal];
    [_suspendButton setTitle:NSLocalizedString(@"suspend", nil) forState:UIControlStateNormal];
    
    [self getServicePort];
    [self loadSpringboard];
}

- (void)viewDidUnload
{    
    [self setTitleLable:nil];
    [self setShutdownButton:nil];
    [self setRebootButton:nil];
    [self setLockdeviceButton:nil];
    [self setLogoutButton:nil];
    [self setSuspendButton:nil];
    [self unloadSpringboard];
    
    [super viewDidUnload];
    // Release any retained subviews of the main view.
}

- (BOOL)shouldAutorotateToInterfaceOrientation:(UIInterfaceOrientation)interfaceOrientation
{
    return (interfaceOrientation != UIInterfaceOrientationPortraitUpsideDown);
}

- (void)getServicePort
{
    void *uikit = dlopen(UIKITPATH, RTLD_LAZY);
    SBSSpringBoardServerPort servicePort = dlsym(uikit, "SBSSpringBoardServerPort");
    self->mServicePort = servicePort();
    dlclose(uikit);
}

- (void)loadSpringboard
{
    self->mSBHandler = dlopen(SBSERVPATH, RTLD_LAZY);
}

- (void)unloadSpringboard
{
    if (self->mSBHandler)
    {
        dlclose(self->mSBHandler);
    }
}

- (IBAction)onShutdownClicked:(id)sender 
{
    SBShutdown shutdown = dlsym(self->mSBHandler, "SBShutdown");
    
    if (shutdown)
    {
        shutdown(self->mServicePort);
        [self addYes:sender];
    }
    else 
    {
        [self addNo:sender];
    }
}

- (IBAction)onRebootClicked:(id)sender 
{
    SBReboot reboot = dlsym(self->mSBHandler, "SBReboot");
    
    if (reboot)
    {
        reboot(self->mServicePort);
        [self addYes:sender];
    }
    else 
    {
        [self addNo:sender];
    }
}

- (IBAction)onLockDeviceClicked:(id)sender 
{
    SBLockDevice lockdevice = dlsym(self->mSBHandler, "SBLockDevice");
    
    if (lockdevice)
    {
        lockdevice(self->mServicePort, TRUE);
        [self addYes:sender];
    }
    else 
    {
        [self addNo:sender];
    }
}

- (IBAction)onLogoutClicked:(id)sender 
{
}

- (IBAction)onSuspendClicked:(id)sender 
{
    SBSuspend suspend = dlsym(self->mSBHandler, "SBSuspend");
    
    if (suspend)
    {
        suspend(self->mServicePort, TRUE);
        [self addYes:sender];
    }
    else 
    {
        [self addNo:sender];
    }
}

- (void)addYes:(id)sender
{
    UIButton *button = (UIButton*)sender;    
    NSString *title = button.titleLabel.text;    
    title = [title stringByAppendingString:@"[Y]"];    
    [button setTitle:title forState:UIControlStateNormal];
}

- (void)addNo:(id)sender
{
    UIButton *button = (UIButton*)sender;
    NSString *title = button.titleLabel.text;
    title = [title stringByAppendingString:@"[N]"];
    [button setTitle:title forState:UIControlStateNormal];
}

@end
