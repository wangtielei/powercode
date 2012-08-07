//
//  QRWidgetViewController.m
//  ZXingWidget
//
//  Created by guanjianjun on 12-8-7.
//  Copyright (c) 2012年 __MyCompanyName__. All rights reserved.
//

#import "QRWidgetViewController.h"
#import "QRCodeReader.h"

@interface QRWidgetViewController ()

@end

@implementation QRWidgetViewController

- (id)initWithDelegate:(id<ZXingDelegate>)scanDelegate guideTips:(NSString*)tips showCancel:(BOOL)shouldShowCancel showTorch:(BOOL)shouldTorch showScanAnimation:(BOOL)shouldShowScanAnimation
{
    self = [super initWithDelegate:scanDelegate guideTips:tips showCancel:shouldShowCancel showTorch:shouldTorch showScanAnimation:shouldShowScanAnimation];
    if (self)
    {
        //添加默认的code reader:二维码解码
        QRCodeReader* qrcodeReader = [[QRCodeReader alloc] init];
        self.readers = [[NSSet alloc ] initWithObjects:qrcodeReader,nil];
        [qrcodeReader release];
    }
    
    return self;
}
- (id)initWithNibName:(NSString *)nibNameOrNil bundle:(NSBundle *)nibBundleOrNil
{
    self = [super initWithNibName:nibNameOrNil bundle:nibBundleOrNil];
    if (self) {
        // Custom initialization
    }
    return self;
}

- (void)viewDidLoad
{
    [super viewDidLoad];
	// Do any additional setup after loading the view.
}

- (void)viewDidUnload
{
    [super viewDidUnload];
    // Release any retained subviews of the main view.
}

- (BOOL)shouldAutorotateToInterfaceOrientation:(UIInterfaceOrientation)interfaceOrientation
{
    return (interfaceOrientation == UIInterfaceOrientationPortrait);
}

@end
