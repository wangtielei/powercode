//
//  LoginWindowViewController.m
//  LoginWindow
//
//  Created by admin on 9/3/10.
//  Copyright __MyCompanyName__ 2010. All rights reserved.
//

#import "LoginWindowViewController.h"
#import "NextViewController.h"

@implementation LoginWindowViewController

@synthesize userNameTextField,pwd,errorTextView,loginButton;


// The designated initializer. Override to perform setup that is required before the view is loaded.
- (id)initWithNibName:(NSString *)nibNameOrNil bundle:(NSBundle *)nibBundleOrNil {
    if ((self = [super initWithNibName:nibNameOrNil bundle:nibBundleOrNil])) {
        // Custom initialization
		
    }
    return self;
}

-(void)viewWillAppear:(BOOL)animated
{
	[errorTextView setHidden:YES];
	userNameTextField.text=@"";
	pwd.text=@"";
	[userNameTextField becomeFirstResponder];
}
// Implement viewDidLoad to do additional setup after loading the view, typically from a nib.
- (void)viewDidLoad {
	[super viewDidLoad];
	[[self navigationController] setNavigationBarHidden:YES];
	errorTextView.font=[UIFont fontWithName:@"verdana" size:12];
	errorTextView.backgroundColor=[UIColor clearColor];
	self.view.backgroundColor=[UIColor grayColor];
	userNameTextField.delegate=self;
	pwd.delegate=self;
	[errorTextView setEditable:NO];
	[errorTextView setHidden:YES];
	 [userNameTextField becomeFirstResponder];
}

-(IBAction) loginAction{
	[userNameTextField resignFirstResponder];
	[pwd resignFirstResponder];
	
	if([userNameTextField.text isEqualToString:@"username"] && [pwd.text isEqualToString:@"password"])
	{
	NextViewController *nextController=[[NextViewController alloc]initWithNibName:@"NextViewController" bundle:[NSBundle mainBundle]];
		[self.navigationController pushViewController:nextController  animated:YES];
	}
	else if ((userNameTextField.text == nil) || (pwd.text == nil) || ([userNameTextField.text length]==0) || ([pwd.text length]== 0)){
		[errorTextView setHidden:NO];
		errorTextView.text = @"Invalid User Name or Password";
		
	}
	else {
		[errorTextView setHidden:NO];
		errorTextView.text = @"Invalid User Name or Password";
	}

	//return;
}

- (BOOL)textFieldShouldReturn: (UITextField *)textField {
	[textField resignFirstResponder];
	return YES;
}
			
- (void)didReceiveMemoryWarning {
	// Releases the view if it doesn't have a superview.
    [super didReceiveMemoryWarning];
	
	// Release any cached data, images, etc that aren't in use.
}

- (void)viewDidUnload {
	// Release any retained subviews of the main view.
	// e.g. self.myOutlet = nil;
}


- (void)dealloc {
	[userNameTextField release];
	[pwd release];
	[errorTextView release];
	[loginButton release];
    [super dealloc];
}

@end
