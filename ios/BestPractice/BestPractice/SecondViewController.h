//
//  SecondViewController.h
//  BestPractice
//
//  Created by guanjianjun on 12-7-16.
//  Copyright (c) 2012年 __MyCompanyName__. All rights reserved.
//

#import <UIKit/UIKit.h>

@interface SecondViewController : UIViewController

//定义各个按钮的实例变量
@property (strong, nonatomic) IBOutlet UILabel *titleLable;
@property (strong, nonatomic) IBOutlet UIButton *shutdownButton;
@property (strong, nonatomic) IBOutlet UIButton *rebootButton;
@property (strong, nonatomic) IBOutlet UIButton *lockdeviceButton;
@property (strong, nonatomic) IBOutlet UIButton *logoutButton;
@property (strong, nonatomic) IBOutlet UIButton *suspendButton;

//定义各个按钮的点击事件
- (IBAction)onShutdownClicked:(id)sender;
- (IBAction)onRebootClicked:(id)sender;
- (IBAction)onLockDeviceClicked:(id)sender;
- (IBAction)onLogoutClicked:(id)sender;
- (IBAction)onSuspendClicked:(id)sender;


@end
