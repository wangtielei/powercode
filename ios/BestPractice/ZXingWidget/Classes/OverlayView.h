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

@protocol UserActionDelegate;

@interface OverlayView : UIView 
{
	NSMutableArray *_points;
    UIButton *cancelButton;
	UIButton *flashButton;
    UILabel *instructionsLabel;
	id<UserActionDelegate> delegate;
	BOOL oneDMode;
    CGRect cropRect;
    NSString *displayedMessage;
}

@property (nonatomic, retain) NSMutableArray*  points;
@property (nonatomic, assign) id<UserActionDelegate> delegate;
@property (nonatomic, assign) BOOL oneDMode;
@property (nonatomic, assign) CGRect cropRect;
@property (nonatomic, copy) NSString *displayedMessage;
@property (nonatomic) float curYPos;
@property (nonatomic) BOOL isMovingDown;
@property (nonatomic, retain) UIView *scanLine;
@property (nonatomic, retain) NSTimer *scanTimer;

- (id) initWithFrame:(CGRect)theFrame cancelEnabled:(BOOL)isCancelEnabled torchEnabled:(BOOL)isTorchEnabled oneDMode:(BOOL)isOneDModeEnabled;

- (void)setPoint:(CGPoint)point;

- (void)setScan:(BOOL)begin;


@end

@protocol UserActionDelegate
- (void)cancelled;
- (BOOL)swtichTorch;
@end
