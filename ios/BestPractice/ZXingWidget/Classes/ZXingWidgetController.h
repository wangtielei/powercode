/**
 * Copyright 2009 Jeff Verkoeyen
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

#import <UIKit/UIKit.h>
#import <AudioToolbox/AudioToolbox.h>
#import <AVFoundation/AVFoundation.h>
#import "Decoder.h"
#import "parsedResults/ParsedResult.h"
#import "OverlayView.h"

@protocol ZXingDelegate;

#if !TARGET_IPHONE_SIMULATOR
#define HAS_AVFF 1
#endif

@interface ZXingWidgetController : UIViewController<DecoderDelegate,UIAlertViewDelegate,
                                                    UserActionDelegate,
                                                    UINavigationControllerDelegate
#if HAS_AVFF
                                                    , AVCaptureVideoDataOutputSampleBufferDelegate
#endif
                                                    > {
  NSSet *readers;
  ParsedResult *result;
  OverlayView *overlayView;
  SystemSoundID beepSound;
  BOOL showCancel;
  NSURL *soundToPlay;
  id<ZXingDelegate> delegate;
  BOOL wasCancelled;
  BOOL oneDMode;
#if HAS_AVFF
  AVCaptureSession *captureSession;
  AVCaptureVideoPreviewLayer *prevLayer;
#endif
  BOOL decoding;
  BOOL isStatusBarHidden;
  UIAlertView *baseAlert; //用于展示扫描到的内容
}

#if HAS_AVFF
@property (nonatomic, retain) AVCaptureSession *captureSession;
@property (nonatomic, retain) AVCaptureVideoPreviewLayer *prevLayer;
#endif
@property (nonatomic, retain ) NSSet *readers;
@property (nonatomic, assign) id<ZXingDelegate> delegate;
@property (nonatomic, retain) NSURL *soundToPlay;
@property (nonatomic, retain) ParsedResult *result;
@property (nonatomic, retain) OverlayView *overlayView;
@property (nonatomic, retain) NSString *capturedData;

- (id)initWithDelegate:(id<ZXingDelegate>)scanDelegate guideTips:(NSString*)tips showCancel:(BOOL)shouldShowCancel showTorch:(BOOL)shouldTorch showScanAnimation:(BOOL)shouldShowScanAnimation;

- (BOOL)fixedFocus;
- (void)setTorch:(BOOL)status;
- (BOOL)torchIsOn;

@end

@protocol ZXingDelegate<NSObject>
- (void)zxingController:(ZXingWidgetController*)controller didScanResult:(NSString *)result;
- (void)zxingControllerDidCancel:(ZXingWidgetController*)controller;
- (BOOL)previewCapturedResult:(ZXingWidgetController*)controller capturedData:(NSString *)result;
@end
