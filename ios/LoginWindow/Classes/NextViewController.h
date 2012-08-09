//
//  NextViewController.h
//  LoginWindow
//
//  Created by admin on 9/3/10.
//  Copyright 2010 __MyCompanyName__. All rights reserved.
//

#import <UIKit/UIKit.h>


@interface NextViewController : UIViewController {
UIButton *backButton;
}
@property(nonatomic,retain)IBOutlet UIButton *backButton;
-(IBAction)backAction;
@end
