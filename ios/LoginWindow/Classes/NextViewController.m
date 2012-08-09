//
//  NextViewController.m
//  LoginWindow
//
//  Created by admin on 9/3/10.
//  Copyright 2010 __MyCompanyName__. All rights reserved.
//

#import "NextViewController.h"


@implementation NextViewController
@synthesize backButton;

-(IBAction)backAction{
	[self.navigationController popViewControllerAnimated:YES];
}

// Implement viewDidLoad to do additional setup after loading the view, typically from a nib.
- (void)viewDidLoad {
	backButton=[[UIButton alloc] initWithFrame:CGRectMake(130,200,30,30)];
	backButton.backgroundColor=[UIColor clearColor];
    [super viewDidLoad];
}

- (void)didReceiveMemoryWarning {
    // Releases the view if it doesn't have a superview.
    [super didReceiveMemoryWarning];
    
    // Release any cached data, images, etc that aren't in use.
}

- (void)viewDidUnload {
    [super viewDidUnload];
    // Release any retained subviews of the main view.
    // e.g. self.myOutlet = nil;
}


- (void)dealloc {
    [super dealloc];
}


@end
