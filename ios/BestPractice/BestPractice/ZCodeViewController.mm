//
//  ZCodeViewController.m
//  BestPractice
//
//  Created by guanjianjun on 12-7-21.
//  Copyright (c) 2012年 __MyCompanyName__. All rights reserved.
//

#import "ZCodeViewController.h"

@interface ZCodeViewController ()

@end

@implementation ZCodeViewController
@synthesize resultsView;
@synthesize resultsToDisplay;
@synthesize qrImageDecoder = _qrImageDecoder;

- (id)initWithNibName:(NSString *)nibNameOrNil bundle:(NSBundle *)nibBundleOrNil
{
    self = [super initWithNibName:nibNameOrNil bundle:nibBundleOrNil];
    if (self) 
    {
        
    }
    return self;
}

- (void)viewDidLoad
{
    [super viewDidLoad];
	
    if (!_qrImageDecoder)
    {
        _qrImageDecoder = [[QRImageDecoder alloc] init];
    }
    
    _qrImageDecoder.notifyDelegate = self;
}

- (void)viewWillDisappear:(BOOL)animated
{
    DDLogEndMethodInfo();
}

- (void)viewWillAppear:(BOOL)animated 
{
    DDLogEndMethodInfo();
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

- (void)setResult:(NSString*)result
{
    self.resultsToDisplay = result;
    if (self.isViewLoaded) 
    {
        [resultsView setText:result];
        [resultsView setNeedsDisplay];
    }
}

- (IBAction)onScan:(id)sender 
{
    QRWidgetViewController *widController = [[QRWidgetViewController alloc] initWithDelegate:self guideTips:@"" showCancel:YES showTorch:YES showScanAnimation:YES];  
    NSBundle *mainBundle = [NSBundle mainBundle];
    widController.soundToPlay = [NSURL fileURLWithPath:[mainBundle pathForResource:@"beep-beep" ofType:@"aiff"] isDirectory:NO];
    [self presentModalViewController:widController animated:NO];
}

- (IBAction)onSelectQR:(id)sender 
{
    DDLogStartMethodInfo();
    UIImagePickerController *ipc = [[UIImagePickerController alloc] init];
	ipc.sourceType =  UIImagePickerControllerSourceTypePhotoLibrary;
	ipc.delegate = self;
	[self presentModalViewController:ipc animated:YES];	
    DDLogEndMethodInfo();
}

#pragma begin ZXingDelegate methond
- (void)zxingController:(ZXingWidgetController*)controller didScanResult:(NSString *)result {
    [self setResult:result];
    [self dismissModalViewControllerAnimated:YES];
}

- (void)zxingControllerDidCancel:(ZXingWidgetController*)controller {
    [self dismissModalViewControllerAnimated:NO];
}
#pragma begin ZXingDelegate methond


#pragma begin UIImagePickerControllerDelegate methond
// 3.0-3.1 compatibility
- (void) setAllowsEditing:(BOOL)doesAllow forPicker:(UIImagePickerController *) ipc
{
    DDLogStartMethodInfo();
	SEL allowsSelector;
	if ([ipc respondsToSelector:@selector(setAllowsEditing:)]) allowsSelector = @selector(setAllowsEditing:);
	
	NSMethodSignature *ms = [ipc methodSignatureForSelector:allowsSelector];
	NSInvocation *inv = [NSInvocation invocationWithMethodSignature:ms];
	
	[inv setTarget:ipc];
	[inv setSelector:allowsSelector];
	[inv setArgument:&doesAllow atIndex:2];
	[inv invoke];
    DDLogEndMethodInfo();
}

- (void)imagePickerController:(UIImagePickerController *)picker didFinishPickingMediaWithInfo:(NSDictionary *)info
{
    DDLogStartMethodInfo();
    //NSArray *imageKeys = [info allKeys];
    UIImage *selectedImage = [info objectForKey:@"UIImagePickerControllerOriginalImage"];
    //NSString *imagePath = [info objectForKey:@"UIImagePickerControllerReferenceURL"];
    //NSString *imageType = [info objectForKey:@"UIImagePickerControllerMediaType"];
    [self dismissModalViewControllerAnimated:YES];
    
    //[self.qrImageDecoder asyncDecode:selectedImage];
    NSString *result = [self.qrImageDecoder syncDecode:selectedImage];
    
    if (result)
    {
        [self setResult:result];
        
        /*
        float angle = 30.0f*(3.14/100);
        CGAffineTransform transform = CGAffineTransformMakeRotation(angle);
        resultsView.transform = transform;//绕着中心旋转30度
        resultsView.transform = CGAffineTransformIdentity;//还原坐标
         */
    }
    else 
    {
        [self setResult:@"decode failed"];
    }
    DDLogEndMethodInfo();
}

// Provide 2.x compliance
- (void)imagePickerController:(UIImagePickerController *)picker didFinishPickingImage:(UIImage *)image editingInfo:(NSDictionary *)editingInfo
{
    DDLogStartMethodInfo();
	NSDictionary *dict = [NSDictionary dictionaryWithObject:image forKey:@"UIImagePickerControllerOriginalImage"];
	[self imagePickerController:picker didFinishPickingMediaWithInfo:dict];
    DDLogEndMethodInfo();
}

- (void) imagePickerControllerDidCancel:(UIImagePickerController *)picker
{
    DDLogStartMethodInfo();
	[self dismissModalViewControllerAnimated:YES];
    DDLogEndMethodInfo();
}
#pragma end UIImagePickerControllerDelegate methond


#pragma begin QRDecodeDelegate methond
//解码成功时被调用，decodeResult为解码结果
- (void)successDecodeImage:(NSString*)decodeResult
{
    DDLogStartMethodInfo();
    DDLogInfo(@"decoded success with data:%@", decodeResult);
    [self setResult:decodeResult];
    DDLogEndMethodInfo();
}

//解码失败时被调用，decodeResult为解码结果
- (void)failDecodeImage:(NSString*)reason
{
    DDLogStartMethodInfo();
    DDLogInfo(@"decoded fail with reason:%@", reason);
    [self setResult:reason];
    DDLogEndMethodInfo();
}
#pragma end QRDecodeDelegate methond


- (void)navigationController:(UINavigationController *)navigationController willShowViewController:(UIViewController *)viewController animated:(BOOL)animated
{
    
}

- (void)navigationController:(UINavigationController *)navigationController didShowViewController:(UIViewController *)viewController animated:(BOOL)animated
{
    
}

@end
