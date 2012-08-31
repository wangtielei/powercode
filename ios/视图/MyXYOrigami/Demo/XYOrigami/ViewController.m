//
//  ViewController.m
//  XYOrigami
//
//  Created by XY Feng on 5/28/12.
//  Copyright (c) 2012 Xiaoyang Feng. All rights reserved.
//

#import "ViewController.h"
#import <QuartzCore/QuartzCore.h>
#import "Origami.h"

@interface ViewController ()
{
    Origami *_myOrigami;
    BOOL currDirection;
}
@end

@implementation ViewController
@synthesize centerView;
@synthesize sideView;
@synthesize foldsNum;
@synthesize durationNum;
@synthesize closeBtn;
@synthesize showBtn;
@synthesize webView;

- (void)viewDidLoad
{
    [super viewDidLoad];
    
    CLLocationCoordinate2D zoomLocation;
    zoomLocation.latitude = 40.7310;
    zoomLocation.longitude= -73.9977;
    MKCoordinateRegion viewRegion = MKCoordinateRegionMakeWithDistance(zoomLocation, 10000, 10000);         
    [self.sideView setRegion:viewRegion animated:NO];
    self.sideView.backgroundColor = [UIColor clearColor];
    [self.webView loadRequest:[NSURLRequest requestWithURL:[NSURL URLWithString:@"http://www.baidu.com"]]];    
    
    
    [self.view bringSubviewToFront:self.sideView];
    
    currDirection = XYOrigamiDirectionFromLeft;
    
    self->_myOrigami = [[Origami alloc] init];
    self.showBtn.hidden = NO;
    self.closeBtn.hidden = YES;
}

- (void)viewDidUnload
{
    [self setCenterView:nil];
    [self setSideView:nil];
    [self setFoldsNum:nil];
    [self setDurationNum:nil];
    [self setCloseBtn:nil];
    [self setShowBtn:nil];
    [self setWebView:nil];
    [super viewDidUnload];
}

- (BOOL)shouldAutorotateToInterfaceOrientation:(UIInterfaceOrientation)interfaceOrientation
{
    return UIInterfaceOrientationIsPortrait(interfaceOrientation);
}

- (IBAction)swipeLeft:(id)sender {
    if (currDirection == XYOrigamiDirectionFromLeft) {
        self.closeBtn.hidden = YES;
        [_myOrigami hideOrigamiTransitionWith:self.sideView
                                     NumberOfFolds:[self.foldsNum.text intValue]
                                          Duration:[self.durationNum.text floatValue]
                                         Direction:XYOrigamiDirectionFromLeft
                                        completion:^(BOOL finished) {
                                        }];
    }
    else {
   
        [_myOrigami showOrigamiTransitionWith:self.sideView
                                     NumberOfFolds:[self.foldsNum.text intValue]
                                          Duration:[self.durationNum.text floatValue]
                                         Direction:XYOrigamiDirectionFromRight
                                        completion:^(BOOL finished) {
                                            self.closeBtn.hidden = NO;
                                        }];
    }
}

- (IBAction)swipeRight:(id)sender {
    if (currDirection == XYOrigamiDirectionFromLeft) {
        
        [_myOrigami showOrigamiTransitionWith:self.sideView
                                     NumberOfFolds:[self.foldsNum.text intValue] 
                                          Duration:[self.durationNum.text floatValue]
                                         Direction:XYOrigamiDirectionFromLeft
                                        completion:^(BOOL finished) {
                                            self.closeBtn.hidden = NO;
                                        }];
    }
    else {
        self.closeBtn.hidden = YES;
        [_myOrigami hideOrigamiTransitionWith:self.sideView
                                     NumberOfFolds:[self.foldsNum.text intValue]
                                          Duration:[self.durationNum.text floatValue]
                                         Direction:XYOrigamiDirectionFromRight
                                        completion:^(BOOL finished) {
                                        }];
    }
}

- (IBAction)showMap:(id)sender {
    self.sideView.hidden = NO;
    _myOrigami.backgroundImage = [Origami imageFromView:self.webView Rect:self.sideView.frame];
    UIImageWriteToSavedPhotosAlbum(_myOrigami.backgroundImage, nil, nil, nil);
    [_myOrigami showOrigamiTransitionWith:self.sideView
                                 NumberOfFolds:[self.foldsNum.text intValue]
                                      Duration:[self.durationNum.text floatValue]
                                     Direction:currDirection
                                completion:^(BOOL finished) {
                                    self.closeBtn.hidden = NO;
                                    self.showBtn.hidden = YES;
                            }];
}

- (IBAction)foldNumberChanged:(UIStepper *)stepper {
    self.foldsNum.text = [NSString stringWithFormat:@"%d",(int)stepper.value];
}

- (IBAction)durationSliderChanged:(UISlider *)slider {
    self.durationNum.text = [NSString stringWithFormat:@"%.1f", slider.value];
}

- (IBAction)directionSelectorChanged:(UISegmentedControl*)seg 
{
    if (seg.selectedSegmentIndex == 0) {
        currDirection = XYOrigamiDirectionFromLeft;
        //self.closeBtn.frame = CGRectMake(20, 405, 40, 30);
    }
    else if (seg.selectedSegmentIndex == 1){
        currDirection = XYOrigamiDirectionFromRight;
        //self.closeBtn.frame = CGRectMake(260, 405, 40, 30);
    }
    else if (seg.selectedSegmentIndex == 2)
    {
        currDirection = XYOrigamiDirectionFromTop;
    }
    else if (seg.selectedSegmentIndex == 3)
    {
        currDirection = XYOrigamiDirectionFromBottom;
    }
}

- (IBAction)hideMap:(id)sender {
    [_myOrigami hideOrigamiTransitionWith:self.sideView
                                 NumberOfFolds:[self.foldsNum.text intValue]
                                      Duration:[self.durationNum.text floatValue]
                                     Direction:currDirection
                                    completion:^(BOOL finished) {
                                        self.closeBtn.hidden = YES;
                                        self.showBtn.hidden = NO;
                                        self.sideView.hidden = YES;
                            }];
}

@end
