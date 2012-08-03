//
//  AddSightingViewController.h
//  BirdWatching
//
//  Created by guanjianjun on 7/15/12.
//  Copyright (c) 2012 __MyCompanyName__. All rights reserved.
//

#import <UIKit/UIKit.h>

@protocol AddSightingViewControllerDelegate;

@interface AddSightingViewController : UITableViewController <UITextFieldDelegate>

@property (weak, nonatomic) IBOutlet UITextField *birdNameInput;
@property (weak, nonatomic) IBOutlet UITextField *locationInput;
@property (weak, nonatomic) id <AddSightingViewControllerDelegate> delegate;

- (IBAction)onCancel:(id)sender;
- (IBAction)onDone:(id)sender;

@end

@protocol AddSightingViewControllerDelegate <NSObject>

- (void)addSightingViewControllerDidCancel:(AddSightingViewController *)controller;

- (void)addSightingViewControllerDidFinish:(AddSightingViewController *)controller name:(NSString *)name location:(NSString *)location;

@end
