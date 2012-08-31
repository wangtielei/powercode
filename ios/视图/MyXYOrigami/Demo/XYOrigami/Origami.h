//
//  UIView+Origami.h
//  origami
//
//  Created by XY Feng on 4/6/12.
//  Copyright (c) 2012 Xiaoyang Feng. All rights reserved.
//
//  Permission is hereby granted, free of charge, to any person
//  obtaining a copy of this software and associated documentation
//  files (the "Software"), to deal in the Software without
//  restriction, including without limitation the rights to use,
//  copy, modify, merge, publish, distribute, sublicense, and/or sell
//  copies of the Software, and to permit persons to whom the
//  Software is furnished to do so, subject to the following
//  conditions:
//
//  The above copyright notice and this permission notice shall be
//  included in all copies or substantial portions of the Software.
//
//  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
//  EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
//  OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
//  NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
//  HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
//  WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
//  FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
//  OTHER DEALINGS IN THE SOFTWARE.

#import <UIKit/UIKit.h>
#import <QuartzCore/QuartzCore.h>

typedef double (^KeyframeParametricBlock)(double);

enum {
	XYOrigamiDirectionFromRight     = 0,
	XYOrigamiDirectionFromLeft      = 1,
    XYOrigamiDirectionFromTop       = 2,
    XYOrigamiDirectionFromBottom    = 3,
    XYOrigamiDirectionUnknown       = 10
};
typedef NSUInteger XYOrigamiDirection;

@interface Origami :NSObject

@property (nonatomic, retain) UIImage *backgroundImage;

-(id)init;

/********************************************************************
 *显示折叠效果，从收起到展开状态
 *view:需要折叠的视图;
 *folds:需要折叠的组数,总折块数为folds*2;
 *duration:动画显示时间;
 *direction:动画显示的方向，从左到右，从右到左，从上到下，从下到上
 *******************************************************************/
-(void)showOrigamiTransitionWith:(UIView *)view 
                    NumberOfFolds:(NSInteger)folds 
                         Duration:(CGFloat)duration
                        Direction:(XYOrigamiDirection)direction;

/********************************************************************
 *显示折叠效果，从收起到展开状态
 *view:需要折叠的视图;
 *folds:需要折叠的组数,总折块数为folds*2;
 *duration:动画显示时间;
 *direction:动画显示的方向，从左到右，从右到左，从上到下，从下到上
 *completion:动画结束后执行的回调函数，可以为nil，如果completion函数不为nil，
 *则整个动画结束后才会调用completion
 *******************************************************************/
-(void)showOrigamiTransitionWith:(UIView *)view 
                    NumberOfFolds:(NSInteger)folds 
                         Duration:(CGFloat)duration
                        Direction:(XYOrigamiDirection)direction
                       completion:(void (^)(BOOL finished))completion;

/********************************************************************
 *显示折叠效果，从收起到展开状态
 *view:需要折叠的视图;
 *foldFrames:指定的折痕矩形;
 *duration:动画显示时间;
 *completion:动画结束后执行的回调函数，可以为nil，如果completion函数不为nil，
 *则整个动画结束后才会调用completion
 *******************************************************************/
-(void)showOrigamiTransitionWith:(UIView *)view 
                   FoldFrames:(NSMutableArray*)foldFrames
                        Duration:(CGFloat)duration
                      completion:(void (^)(BOOL finished))completion;





/********************************************************************
 *显示折叠效果，从展开到收起状态
 *view:需要折叠的视图;
 *folds:需要折叠的组数,总折块数为folds*2;
 *duration:动画显示时间;
 *direction:动画显示的方向，从左到右，从右到左，从上到下，从下到上
 *******************************************************************/
-(void)hideOrigamiTransitionWith:(UIView *)view
                    NumberOfFolds:(NSInteger)folds
                         Duration:(CGFloat)duration
                        Direction:(XYOrigamiDirection)direction;

/********************************************************************
 *显示折叠效果，从收起到展开状态
 *view:需要折叠的视图;
 *folds:需要折叠的组数,总折块数为folds*2;
 *duration:动画显示时间;
 *direction:动画显示的方向，从左到右，从右到左，从上到下，从下到上
 *completion:动画结束后执行的回调函数，可以为nil，如果completion函数不为nil，
 *则整个动画结束后才会调用completion
 *******************************************************************/
-(void)hideOrigamiTransitionWith:(UIView *)view 
                   NumberOfFolds:(NSInteger)folds 
                        Duration:(CGFloat)duration
                       Direction:(XYOrigamiDirection)direction
                      completion:(void (^)(BOOL finished))completion;


/********************************************************************
 *显示折叠效果，从展开到收起状态
 *如果completion函数不为nil，则整个动画结束后才会调用completion
 *view:需要折叠的视图;
 *foldFrames:指定的折痕矩形;
 *duration:动画显示时间;
 *******************************************************************/
-(void)hideOrigamiTransitionWith:(UIView *)view
                        FoldFrames:(NSMutableArray*)foldFrames
                         Duration:(CGFloat)duration
                       completion:(void (^)(BOOL finished))completion;

/********************************************************************
 *设置动画效果过程中的背景试图
 *animationView:是需要动的view
 *******************************************************************/
-(void)backgroundImage:(UIView *)backView AnimaitonView:(UIView*)animationView;



/********************************************************************
 *当前屏幕的缩放比例
 *******************************************************************/
+(float)screenScale;

/********************************************************************
 *根据视图截图
 *******************************************************************/
+(UIImage *)imageFromView:(UIView *)view;

/********************************************************************
 *根据视图截取某个区域的图
 *******************************************************************/
+(UIImage *)imageFromView:(UIView *)view Rect:(CGRect)cropRC;

/********************************************************************
 *裁剪某个图形
 *******************************************************************/
+(UIImage *)cropImage:(UIImage *)srcImage Rect:(CGRect)cropRC;



@end
