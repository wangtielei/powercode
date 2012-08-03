//
//  ZCodeViewController.h
//  BestPractice
//
//  Created by guanjianjun on 12-7-21.
//  Copyright (c) 2012年 __MyCompanyName__. All rights reserved.
//

#import <UIKit/UIKit.h>
#import "ZXingWidgetController.h"


@interface ZCodeViewController : UIViewController<ZXingDelegate>
- (IBAction)onScan:(id)sender;
@property (strong, nonatomic) IBOutlet UITextView *resultsView;

@property (nonatomic, copy) NSString *resultsToDisplay;
@end
