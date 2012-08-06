//
//  HelloWorldViewController.h
//  HelloWorld
//
//  Created by guanjianjun on 5/29/12.
//  Copyright (c) 2012 __MyCompanyName__. All rights reserved.
//

#import <UIKit/UIKit.h>

@interface HelloWorldViewController : UIViewController <UITextFieldDelegate>

// declare hello button click event
- (IBAction)changeGreeting:(UIButton *)sender;

//declare outlet between textbox and view controller
@property (weak, nonatomic) IBOutlet UITextField *textField;

//declare outlet between label and view controller
@property (weak, nonatomic) IBOutlet UILabel *label;

//declare userName property
@property (copy, nonatomic) NSString *userName;



@end
