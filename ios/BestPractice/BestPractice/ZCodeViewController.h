//
//  ZCodeViewController.h
//  BestPractice
//
//  Created by guanjianjun on 12-7-21.
//  Copyright (c) 2012年 __MyCompanyName__. All rights reserved.
//

#import <UIKit/UIKit.h>
#import "QRWidgetViewController.h"
#import "QRImageDecoder.h"


@interface ZCodeViewController : UIViewController<ZXingDelegate, 
                                                  UINavigationControllerDelegate,
                                                  UIImagePickerControllerDelegate,
                                                  QRDecodeDelegate>
- (IBAction)onScan:(id)sender;
@property (strong, nonatomic) IBOutlet UITextView *resultsView;
@property (nonatomic, copy) NSString *resultsToDisplay;
@property (nonatomic, nonatomic) QRImageDecoder *qrImageDecoder;

- (IBAction)onScan:(id)sender;
- (IBAction)onSelectQR:(id)sender;

@end
