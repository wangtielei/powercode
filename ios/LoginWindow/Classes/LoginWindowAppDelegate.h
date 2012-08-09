//
//  LoginWindowAppDelegate.h
//  LoginWindow
//
//  Created by admin on 9/3/10.
//  Copyright __MyCompanyName__ 2010. All rights reserved.
//

#import <UIKit/UIKit.h>

@class LoginWindowViewController;

@interface LoginWindowAppDelegate : NSObject <UIApplicationDelegate> {
    UIWindow *window;
	UINavigationController *navController;
	LoginWindowViewController *viewController;
}

@property (nonatomic, retain) IBOutlet UIWindow *window;
@property(nonatomic,retain)UINavigationController *navController;
@property(nonatomic,retain)IBOutlet LoginWindowViewController *viewController;
@end

