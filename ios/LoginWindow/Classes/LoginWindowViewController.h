//
//  LoginWindowViewController.h
//  LoginWindow
//
//  Created by admin on 9/3/10.
//  Copyright __MyCompanyName__ 2010. All rights reserved.
//

#import <UIKit/UIKit.h>

@interface LoginWindowViewController : UIViewController<UITextFieldDelegate> {
	UITextView *errorTextView;
	UITextField *userNameTextField;
	UITextField *pwd;
	UIButton *loginButton;
	
}
@property(nonatomic,retain)IBOutlet UITextView *errorTextView;
@property(nonatomic,retain)IBOutlet UITextField *userNameTextField;
@property(nonatomic,retain)IBOutlet UITextField *pwd;
@property(nonatomic,retain)IBOutlet UIButton *loginButton;
-(IBAction) loginAction;
- (BOOL)textFieldShouldReturn: (UITextField *)textField;
@end

